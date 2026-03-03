<?php

declare(strict_types=1);

use App\Models\Request;
use App\Models\Url;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->url = Url::factory()->for($this->user)->create();
    // Use 1-hour retention in tests for predictable deletion timing
    config()->set('viewhook.retention_hours', 1);
});

describe('data retention cleanup', function (): void {
    it('deletes requests older than the configured retention period', function (): void {
        $oldRequests = Request::factory(5)->for($this->url)->create([
            'created_at' => now()->subHours(2),
        ]);

        $recentRequests = Request::factory(3)->for($this->url)->create([
            'created_at' => now()->subMinutes(30),
        ]);

        dispatch_sync(new \App\Jobs\CleanupExpiredRequestsJob);

        foreach ($oldRequests as $request) {
            $this->assertDatabaseMissing('requests', ['id' => $request->id]);
        }

        foreach ($recentRequests as $request) {
            $this->assertDatabaseHas('requests', ['id' => $request->id]);
        }
    });

    it('keeps requests exactly at the retention boundary', function (): void {
        $boundaryRequest = Request::factory()->for($this->url)->create([
            'created_at' => now()->subMinutes(59),
        ]);

        dispatch_sync(new \App\Jobs\CleanupExpiredRequestsJob);

        $this->assertDatabaseHas('requests', ['id' => $boundaryRequest->id]);
    });

    it('deletes requests just past the retention boundary', function (): void {
        $expiredRequest = Request::factory()->for($this->url)->create([
            'created_at' => now()->subMinutes(61),
        ]);

        dispatch_sync(new \App\Jobs\CleanupExpiredRequestsJob);

        $this->assertDatabaseMissing('requests', ['id' => $expiredRequest->id]);
    });

    it('handles large numbers of expired requests in chunks', function (): void {
        Request::factory(2500)->for($this->url)->create([
            'created_at' => now()->subHours(2),
        ]);

        dispatch_sync(new \App\Jobs\CleanupExpiredRequestsJob);

        expect($this->url->requests()->count())->toBe(0);
    });

    it('can be triggered via artisan command with sync flag', function (): void {
        Request::factory(5)->for($this->url)->create([
            'created_at' => now()->subHours(2),
        ]);

        $this->artisan('requests:cleanup', ['--sync' => true])
            ->expectsOutput('Starting expired requests cleanup...')
            ->expectsOutput('Cleanup completed synchronously.')
            ->assertExitCode(0);

        expect($this->url->requests()->count())->toBe(0);
    });

    it('can be run asynchronously via artisan command', function (): void {
        $this->artisan('requests:cleanup')
            ->expectsOutput('Starting expired requests cleanup...')
            ->expectsOutput('Cleanup job dispatched to queue.')
            ->assertExitCode(0);
    });

    it('logs cleanup statistics', function (): void {
        \Illuminate\Support\Facades\Log::spy();

        Request::factory(10)->for($this->url)->create([
            'created_at' => now()->subHours(2),
        ]);

        dispatch_sync(new \App\Jobs\CleanupExpiredRequestsJob);

        \Illuminate\Support\Facades\Log::shouldHaveReceived('info')
            ->withArgs(fn ($message, $context): bool => $message === 'Expired requests cleanup completed'
                && $context['deleted_count'] === 10);
    });

    it('skips cleanup when retention_hours is null (keep forever)', function (): void {
        config()->set('viewhook.retention_hours');

        \Illuminate\Support\Facades\Log::spy();

        Request::factory(5)->for($this->url)->create([
            'created_at' => now()->subHours(2),
        ]);

        dispatch_sync(new \App\Jobs\CleanupExpiredRequestsJob);

        expect($this->url->requests()->count())->toBe(5);

        \Illuminate\Support\Facades\Log::shouldHaveReceived('info')
            ->withArgs(fn ($message): bool => $message === 'Expired requests cleanup skipped: retention is set to forever');
    });
});

describe('retention across multiple urls', function (): void {
    it('deletes expired requests from all urls', function (): void {
        $url2 = Url::factory()->for($this->user)->create();

        Request::factory(3)->for($this->url)->create([
            'created_at' => now()->subHours(2),
        ]);

        Request::factory(3)->for($url2)->create([
            'created_at' => now()->subHours(2),
        ]);

        dispatch_sync(new \App\Jobs\CleanupExpiredRequestsJob);

        expect($this->url->requests()->count())->toBe(0);
        expect($url2->requests()->count())->toBe(0);
    });
});
