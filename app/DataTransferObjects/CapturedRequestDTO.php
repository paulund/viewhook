<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

use Illuminate\Http\Request;

final readonly class CapturedRequestDTO
{
    /**
     * @param  array<string, mixed>  $headers
     * @param  array<string, mixed>|null  $queryParams
     */
    public function __construct(
        public string $method,
        public string $path,
        public ?string $contentType,
        public int $contentLength,
        public array $headers,
        public ?array $queryParams,
        public ?string $body,
        public ?string $ipAddress,
        public ?string $userAgent,
    ) {}

    public static function fromRequest(Request $request, string $path = '/'): self
    {
        $headers = collect($request->headers->all())
            ->map(fn (array $values): string => implode(', ', $values))
            ->all();

        return new self(
            method: $request->method(),
            path: $path,
            contentType: $request->header('Content-Type'),
            contentLength: (int) $request->header('Content-Length', '0'),
            headers: $headers,
            queryParams: $request->query() ?: null,
            body: $request->getContent() ?: null,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'method' => $this->method,
            'path' => $this->path,
            'content_type' => $this->contentType,
            'content_length' => $this->contentLength,
            'headers' => $this->headers,
            'query_params' => $this->queryParams,
            'body' => $this->body,
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
        ];
    }
}
