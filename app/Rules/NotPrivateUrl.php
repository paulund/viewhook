<?php

declare(strict_types=1);

namespace App\Rules;

use App\Services\IpSafetyService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final readonly class NotPrivateUrl implements ValidationRule
{
    public function __construct(
        private ?IpSafetyService $ipSafetyService = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || $value === '') {
            return;
        }

        $parsed = parse_url($value);

        if ($parsed === false || ! isset($parsed['host'])) {
            $fail('The :attribute must be a valid URL.');

            return;
        }

        // Only HTTPS is permitted to prevent cleartext forwarding
        if (($parsed['scheme'] ?? '') !== 'https') {
            $fail('The :attribute must use HTTPS.');

            return;
        }

        $service = $this->ipSafetyService ?? app(IpSafetyService::class);
        $error = $service->check($value);

        if ($error !== null) {
            $fail('The :attribute '.$error);
        }
    }
}
