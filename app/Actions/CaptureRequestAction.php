<?php

declare(strict_types=1);

namespace App\Actions;

use App\DataTransferObjects\CapturedRequestDTO;
use App\Events\RequestCaptured;
use App\Models\Request as RequestModel;
use App\Models\Url;
use App\Notifications\NewRequestNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

final readonly class CaptureRequestAction
{
    private const int MAX_PAYLOAD_SIZE = 10 * 1024 * 1024; // 10 MB

    private const int MAX_TOTAL_HEADER_SIZE = 64 * 1024; // 64 KB

    private const int MAX_SINGLE_HEADER_SIZE = 16 * 1024; // 16 KB

    private const array SUPPORTED_CONTENT_TYPES = [
        'application/json',
        'application/x-www-form-urlencoded',
        'text/plain',
        'multipart/form-data',
    ];

    public function execute(Request $request, Url $url, ?string $path = null): void
    {
        Log::info('CaptureRequestAction::execute', [
            'url_id' => $url->resource_id,
            'method' => $request->method(),
            'path' => $path,
        ]);

        $this->validateRequest($request);

        $capturedPath = $path !== null ? '/'.$path : '/';
        $dto = CapturedRequestDTO::fromRequest($request, $capturedPath);

        $this->capture($url, $dto);
    }

    private function validateRequest(Request $request): void
    {
        $this->validatePayloadSize($request);
        $this->validateHeaderSize($request);
        $this->validateContentType($request);
    }

    private function capture(Url $url, CapturedRequestDTO $dto): RequestModel
    {
        $requestModel = $url->requests()->create($dto->toArray());

        $url->update(['last_request_at' => now()]);

        event(new RequestCaptured($requestModel));

        if ($url->hasForwarding()) {
            dispatch(new \App\Jobs\ForwardWebhookJob($requestModel, $url));
        }

        $this->dispatchNotifications($requestModel, $url);

        return $requestModel;
    }

    /**
     * Dispatch email and Slack notifications for a captured request.
     */
    private function dispatchNotifications(RequestModel $request, Url $url): void
    {
        $user = $url->user;

        // Email notification
        if ($url->hasEmailNotification()) {
            $user->notify(new NewRequestNotification($request));
        }

        // Slack notification
        if ($url->hasSlackNotification()) {
            dispatch(new \App\Jobs\SendSlackNotificationJob($request, $url));
        }
    }

    private function validatePayloadSize(Request $request): void
    {
        $contentLength = (int) $request->header('Content-Length', '0');

        if ($contentLength > self::MAX_PAYLOAD_SIZE) {
            throw new HttpException(413, 'Payload too large. Maximum size is 10 MB.');
        }

        $content = $request->getContent();
        $actualSize = strlen($content);
        if ($actualSize > self::MAX_PAYLOAD_SIZE) {
            throw new HttpException(413, 'Payload too large. Maximum size is 10 MB.');
        }
    }

    private function validateHeaderSize(Request $request): void
    {
        $totalSize = 0;

        foreach ($request->headers->all() as $name => $values) {
            $headerValue = implode(', ', $values);
            $headerSize = strlen($name) + strlen($headerValue);

            if ($headerSize > self::MAX_SINGLE_HEADER_SIZE) {
                throw new HttpException(431, 'Single header too large. Maximum size is 16 KB.');
            }

            $totalSize += $headerSize;
        }

        if ($totalSize > self::MAX_TOTAL_HEADER_SIZE) {
            throw new HttpException(431, 'Headers too large. Maximum total size is 64 KB.');
        }
    }

    private function validateContentType(Request $request): void
    {
        $contentType = $request->header('Content-Type');

        // Allow requests without content type (GET, HEAD, etc.)
        if ($contentType === null || $contentType === '') {
            return;
        }

        // Extract the base content type without parameters (e.g., charset)
        $baseContentType = explode(';', $contentType)[0];
        $baseContentType = trim(strtolower($baseContentType));
        $isSupported = array_any(self::SUPPORTED_CONTENT_TYPES, fn ($supported): bool => str_starts_with($baseContentType, (string) $supported));

        if (! $isSupported) {
            throw new HttpException(415, 'Unsupported media type.');
        }
    }
}
