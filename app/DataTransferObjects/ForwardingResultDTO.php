<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

final readonly class ForwardingResultDTO
{
    public function __construct(
        public ?int $statusCode,
        public ?string $responseBody,
        public int $responseTimeMs,
        public ?string $error,
    ) {}

    public static function success(int $statusCode, string $responseBody, int $responseTimeMs): self
    {
        return new self(
            statusCode: $statusCode,
            responseBody: $responseBody,
            responseTimeMs: $responseTimeMs,
            error: null,
        );
    }

    public static function failure(string $error, int $responseTimeMs): self
    {
        return new self(
            statusCode: null,
            responseBody: null,
            responseTimeMs: $responseTimeMs,
            error: $error,
        );
    }
}
