<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Url;
use Illuminate\Support\Facades\RateLimiter;

final readonly class RateLimitService
{
    private const int GLOBAL_LIMIT_PER_MINUTE = 100;

    /**
     * Get rate limit information for a URL.
     *
     * @return array{limit: int, remaining: int, reset_at: int, used: int, percentage: float}
     */
    public function getRateLimitInfo(Url $url): array
    {
        $limit = $this->getHourlyLimit();
        $key = $this->getHourlyKey($url);
        $used = RateLimiter::attempts($key);
        $remaining = (int) max(0, $limit - $used);
        $resetAt = (int) now()->startOfHour()->addHour()->timestamp;
        $percentage = $limit > 0 ? round(($used / $limit) * 100, 1) : 0.0;

        return [
            'limit' => $limit,
            'remaining' => $remaining,
            'reset_at' => $resetAt,
            'used' => $used,
            'percentage' => $percentage,
        ];
    }

    /**
     * Check if the URL has exceeded its hourly rate limit.
     */
    public function isHourlyLimitExceeded(Url $url): bool
    {
        $limit = $this->getHourlyLimit();
        $key = $this->getHourlyKey($url);

        return RateLimiter::attempts($key) >= $limit;
    }

    /**
     * Check if the URL has exceeded its global rate limit.
     */
    public function isGlobalLimitExceeded(Url $url): bool
    {
        $key = $this->getGlobalKey($url);

        return RateLimiter::attempts($key) >= self::GLOBAL_LIMIT_PER_MINUTE;
    }

    /**
     * Get the hourly rate limit for a URL.
     */
    public function getHourlyLimit(): int
    {
        return (int) config('viewhook.rate_limit', 100);
    }

    /**
     * Get remaining requests for a URL in the current hour.
     */
    public function getRemainingRequests(Url $url): int
    {
        $limit = $this->getHourlyLimit();
        $key = $this->getHourlyKey($url);
        $used = RateLimiter::attempts($key);

        return (int) max(0, $limit - $used);
    }

    /**
     * Get the seconds until rate limit resets.
     */
    public function getSecondsUntilReset(): int
    {
        return (int) now()->diffInSeconds(now()->startOfHour()->addHour());
    }

    /**
     * Clear rate limit counters for a URL (useful for testing).
     */
    public function clearLimits(Url $url): void
    {
        RateLimiter::clear($this->getHourlyKey($url));
        RateLimiter::clear($this->getGlobalKey($url));
    }

    private function getHourlyKey(Url $url): string
    {
        return "url:hourly:{$url->id}";
    }

    private function getGlobalKey(Url $url): string
    {
        return "url:global:{$url->id}";
    }
}
