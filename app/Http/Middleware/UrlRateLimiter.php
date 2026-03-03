<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Url;
use App\Services\RateLimitService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

final readonly class UrlRateLimiter
{
    public function __construct(
        private RateLimitService $rateLimitService,
    ) {}

    /**
     * Global rate limit per minute (DDoS protection).
     */
    private const int GLOBAL_LIMIT_PER_MINUTE = 100;

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Url $url */
        $url = $request->route('url');

        // Ensure we have the user relationship loaded
        $url->loadMissing('user');

        // Check global rate limit first (DDoS protection)
        $globalKey = "url:global:{$url->id}";
        if ($this->isGlobalLimitExceeded($globalKey)) {
            return $this->buildTooManyRequestsResponse(
                limit: self::GLOBAL_LIMIT_PER_MINUTE,
                remaining: 0,
                resetAt: (int) now()->startOfMinute()->addMinute()->timestamp,
                retryAfter: (int) now()->diffInSeconds(now()->startOfMinute()->addMinute()),
                message: 'Global rate limit exceeded. Maximum 100 requests per minute.',
            );
        }

        // Check hourly rate limit based on tier
        $hourlyKey = "url:hourly:{$url->id}";
        $limit = $this->getHourlyLimit();
        $resetAt = (int) now()->startOfHour()->addHour()->timestamp;
        $retryAfter = (int) now()->diffInSeconds(now()->startOfHour()->addHour());

        if ($this->isHourlyLimitExceeded($hourlyKey, $limit)) {
            $remaining = 0;

            return $this->buildTooManyRequestsResponse(
                limit: $limit,
                remaining: $remaining,
                resetAt: $resetAt,
                retryAfter: $retryAfter,
                message: "Rate limit exceeded. Maximum {$limit} requests per hour.",
            );
        }

        // Increment counters
        RateLimiter::hit($globalKey, 60); // 1 minute decay
        RateLimiter::hit($hourlyKey, 3600); // 1 hour decay

        $remaining = (int) max(0, $limit - RateLimiter::attempts($hourlyKey));

        // Process request
        $response = $next($request);

        // Add rate limit headers to response
        return $this->addRateLimitHeaders($response, $limit, $remaining, $resetAt);
    }

    private function getHourlyLimit(): int
    {
        return $this->rateLimitService->getHourlyLimit();
    }

    private function isGlobalLimitExceeded(string $key): bool
    {
        return RateLimiter::attempts($key) >= self::GLOBAL_LIMIT_PER_MINUTE;
    }

    private function isHourlyLimitExceeded(string $key, int $limit): bool
    {
        return RateLimiter::attempts($key) >= $limit;
    }

    private function buildTooManyRequestsResponse(
        int $limit,
        int $remaining,
        int $resetAt,
        int $retryAfter,
        string $message,
    ): Response {
        return response()->json([
            'error' => 'Too Many Requests',
            'message' => $message,
            'limit' => $limit,
            'remaining' => $remaining,
            'reset_at' => $resetAt,
        ], 429, [
            'X-RateLimit-Limit' => $limit,
            'X-RateLimit-Remaining' => $remaining,
            'X-RateLimit-Reset' => $resetAt,
            'Retry-After' => $retryAfter,
        ]);
    }

    private function addRateLimitHeaders(Response $response, int $limit, int $remaining, int $resetAt): Response
    {
        $response->headers->set('X-RateLimit-Limit', (string) $limit);
        $response->headers->set('X-RateLimit-Remaining', (string) $remaining);
        $response->headers->set('X-RateLimit-Reset', (string) $resetAt);

        return $response;
    }
}
