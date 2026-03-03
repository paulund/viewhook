<?php

declare(strict_types=1);

namespace App\Services;

use App\DataTransferObjects\ForwardingResultDTO;
use App\Models\Request as RequestModel;
use App\Models\Url;
use Illuminate\Support\Facades\Http;
use Throwable;

final readonly class WebhookForwardingService
{
    private const int TIMEOUT_SECONDS = 30;

    private const int MAX_RESPONSE_BODY_SIZE = 64 * 1024; // 64 KB

    public function __construct(
        private IpSafetyService $ipSafetyService,
    ) {}

    /**
     * Forward a captured request to the target URL and return the result.
     */
    public function forward(RequestModel $request, Url $url): ForwardingResultDTO
    {
        $startTime = microtime(true);

        try {
            /** @var string $targetUrl */
            $targetUrl = $url->forward_to_url;

            // Re-validate at execution time to prevent SSRF via DNS rebinding (TOCTOU)
            $this->ipSafetyService->assertSafe($targetUrl);

            $response = $this->sendRequest($request, $url);

            $responseTimeMs = (int) round((microtime(true) - $startTime) * 1000);

            return ForwardingResultDTO::success(
                statusCode: $response->status(),
                responseBody: $this->truncateResponseBody($response->body()),
                responseTimeMs: $responseTimeMs,
            );
        } catch (Throwable $e) {
            $responseTimeMs = (int) round((microtime(true) - $startTime) * 1000);

            return ForwardingResultDTO::failure(
                error: $this->formatError($e),
                responseTimeMs: $responseTimeMs,
            );
        }
    }

    /**
     * Send the HTTP request to the target URL.
     */
    private function sendRequest(RequestModel $request, Url $url): \Illuminate\Http\Client\Response
    {
        /** @var string $targetUrl */
        $targetUrl = $url->forward_to_url;

        $headers = $this->buildHeaders($request, $url);

        $httpRequest = Http::timeout(self::TIMEOUT_SECONDS)
            ->withHeaders($headers);

        // Add body for methods that support it
        if (in_array($url->forward_method, ['POST', 'PUT', 'PATCH'], true)) {
            if ($request->isJson()) {
                $httpRequest = $httpRequest->withBody(
                    $request->body ?? '',
                    'application/json'
                );
            } else {
                $httpRequest = $httpRequest->withBody(
                    $request->body ?? '',
                    $request->content_type ?? 'text/plain'
                );
            }
        }

        return match ($url->forward_method) {
            'GET' => $httpRequest->get($targetUrl),
            'POST' => $httpRequest->post($targetUrl),
            'PUT' => $httpRequest->put($targetUrl),
            'PATCH' => $httpRequest->patch($targetUrl),
            'DELETE' => $httpRequest->delete($targetUrl),
            default => $httpRequest->post($targetUrl),
        };
    }

    /**
     * Build headers for the forwarded request.
     *
     * @return array<string, string>
     */
    private function buildHeaders(RequestModel $request, Url $url): array
    {
        $headers = [];

        // Add custom headers configured on the URL
        if ($url->forward_headers !== null) {
            $headers = array_merge($headers, $url->forward_headers);
        }

        // Forward original headers (excluding hop-by-hop headers)
        $excludeHeaders = [
            'host',
            'connection',
            'keep-alive',
            'transfer-encoding',
            'te',
            'trailer',
            'proxy-authorization',
            'proxy-authenticate',
            'upgrade',
            'content-length', // Will be set automatically
        ];

        foreach ($request->headers as $name => $value) {
            $normalizedName = strtolower($name);
            if (! in_array($normalizedName, $excludeHeaders, true)) {
                $headers[$name] = is_array($value) ? implode(', ', $value) : $value;
            }
        }

        // Add forwarding identifier headers
        $headers['X-Forwarded-By'] = 'Viewhook';
        $headers['X-Original-Request-Id'] = $request->resource_id;

        return $headers;
    }

    /**
     * Truncate response body to max size.
     */
    private function truncateResponseBody(string $body): string
    {
        if (strlen($body) > self::MAX_RESPONSE_BODY_SIZE) {
            return substr($body, 0, self::MAX_RESPONSE_BODY_SIZE).'... [truncated]';
        }

        return $body;
    }

    /**
     * Format exception error message.
     */
    private function formatError(Throwable $e): string
    {
        $message = $e->getMessage();

        if (strlen($message) > 1000) {
            return substr($message, 0, 1000).'...';
        }

        return $message;
    }
}
