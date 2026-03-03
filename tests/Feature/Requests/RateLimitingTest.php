<?php

declare(strict_types=1);

use App\Models\Url;
use App\Models\User;
use App\Services\RateLimitService;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->url = Url::factory()->for($this->user)->create();

    // Clear rate limits before each test
    RateLimiter::clear("url:hourly:{$this->url->id}");
    RateLimiter::clear("url:global:{$this->url->id}");
});

describe('rate limiting', function (): void {
    it('allows requests within rate limit', function (): void {
        $response = $this->get("/catch/{$this->url->resource_id}");

        $response->assertOk();
        $response->assertHeader('X-RateLimit-Limit', '100');
        $response->assertHeader('X-RateLimit-Remaining', '99');
    });

    it('includes rate limit headers on every response', function (): void {
        $response = $this->get("/catch/{$this->url->resource_id}");

        $response->assertHeader('X-RateLimit-Limit');
        $response->assertHeader('X-RateLimit-Remaining');
        $response->assertHeader('X-RateLimit-Reset');
    });

    it('decrements remaining count with each request', function (): void {
        $response1 = $this->get("/catch/{$this->url->resource_id}");
        $response1->assertHeader('X-RateLimit-Remaining', '99');

        $response2 = $this->get("/catch/{$this->url->resource_id}");
        $response2->assertHeader('X-RateLimit-Remaining', '98');

        $response3 = $this->get("/catch/{$this->url->resource_id}");
        $response3->assertHeader('X-RateLimit-Remaining', '97');
    });

    it('returns 429 when hourly rate limit exceeded', function (): void {
        // Directly exhaust the hourly counter without touching the global counter,
        // so the middleware reaches the hourly-exceeded branch specifically.
        $hourlyKey = "url:hourly:{$this->url->id}";
        for ($i = 0; $i < 100; $i++) {
            RateLimiter::hit($hourlyKey, 3600);
        }

        $response = $this->get("/catch/{$this->url->resource_id}");

        $response->assertStatus(429);
        $response->assertHeader('Retry-After');
        $response->assertJson([
            'error' => 'Too Many Requests',
        ]);
    });

    it('does not capture request when rate limited', function (): void {
        $hourlyKey = "url:hourly:{$this->url->id}";
        for ($i = 0; $i < 100; $i++) {
            RateLimiter::hit($hourlyKey, 3600);
        }

        $requestCountBefore = $this->url->requests()->count();

        $this->get("/catch/{$this->url->resource_id}");

        $requestCountAfter = $this->url->requests()->count();

        expect($requestCountAfter)->toBe($requestCountBefore);
    });

    it('resets rate limit at the top of the hour', function (): void {
        for ($i = 0; $i < 100; $i++) {
            $this->get("/catch/{$this->url->resource_id}");
        }

        RateLimiter::clear("url:hourly:{$this->url->id}");
        RateLimiter::clear("url:global:{$this->url->id}");

        $response = $this->get("/catch/{$this->url->resource_id}");

        $response->assertOk();
    });
});

describe('rate limit service', function (): void {
    it('returns correct rate limit info', function (): void {
        $service = app(RateLimitService::class);

        for ($i = 0; $i < 3; $i++) {
            $this->get("/catch/{$this->url->resource_id}");
        }

        $info = $service->getRateLimitInfo($this->url);

        expect($info['limit'])->toBe(100);
        expect($info['used'])->toBe(3);
        expect($info['remaining'])->toBe(97);
        expect($info['percentage'])->toBe(3.0);
    });

    it('correctly identifies when limit is exceeded', function (): void {
        $service = app(RateLimitService::class);

        expect($service->isHourlyLimitExceeded($this->url))->toBeFalse();

        for ($i = 0; $i < 100; $i++) {
            $this->get("/catch/{$this->url->resource_id}");
        }

        expect($service->isHourlyLimitExceeded($this->url))->toBeTrue();
    });

    it('clears limits correctly', function (): void {
        $service = app(RateLimitService::class);

        for ($i = 0; $i < 100; $i++) {
            $this->get("/catch/{$this->url->resource_id}");
        }

        expect($service->isHourlyLimitExceeded($this->url))->toBeTrue();

        $service->clearLimits($this->url);

        expect($service->isHourlyLimitExceeded($this->url))->toBeFalse();
        expect($service->getRemainingRequests($this->url))->toBe(100);
    });

    it('checks global limit exceeded via service', function (): void {
        $service = app(RateLimitService::class);
        $key = "url:global:{$this->url->id}";

        for ($i = 0; $i < 100; $i++) {
            RateLimiter::hit($key, 60);
        }

        expect($service->isGlobalLimitExceeded($this->url))->toBeTrue();

        RateLimiter::clear($key);
    });

    it('returns seconds until reset in valid range', function (): void {
        $service = app(RateLimitService::class);
        $seconds = $service->getSecondsUntilReset();

        expect($seconds)->toBeGreaterThanOrEqual(0);
        expect($seconds)->toBeLessThanOrEqual(3600);
    });
});

describe('rate limit middleware edge cases', function (): void {
    it('passes through when no resource id matches a url', function (): void {
        $response = $this->get('/');

        $response->assertStatus(200);
    });

    it('returns 404 when url resource id does not exist', function (): void {
        $fakeUuid = '00000000-0000-0000-0000-000000000000';
        $response = $this->get("/catch/{$fakeUuid}");

        $response->assertStatus(404);
    });

    it('enforces global per-minute rate limit', function (): void {
        $key = "url:global:{$this->url->id}";

        for ($i = 0; $i < 100; $i++) {
            RateLimiter::hit($key, 60);
        }

        $response = $this->get("/catch/{$this->url->resource_id}");

        $response->assertStatus(429);
        $response->assertHeader('X-RateLimit-Limit', '100');
        $response->assertHeader('X-RateLimit-Remaining', '0');

        RateLimiter::clear($key);
    });

    it('handles requests with multiple moderate-sized headers', function (): void {
        $headers = [];
        for ($i = 0; $i < 5; $i++) {
            $headers["X-Header-{$i}"] = str_repeat('x', 2000);
        }

        $response = $this->get("/catch/{$this->url->resource_id}", $headers);

        expect($response->status())->toBeLessThan(500);
    });
});

describe('rate limit in url resource', function (): void {
    it('includes rate limit info in url resource', function (): void {
        $response = $this->actingAs($this->user)
            ->get(route('urls.show', $this->url));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('url.rate_limit.limit')
            ->has('url.rate_limit.remaining')
            ->has('url.rate_limit.used')
            ->has('url.rate_limit.percentage')
            ->has('url.rate_limit.reset_at')
        );
    });
});
