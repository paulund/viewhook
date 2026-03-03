<?php

declare(strict_types=1);

use App\Jobs\SendSlackNotificationJob;
use App\Models\Request as RequestModel;
use App\Models\Url;
use App\Models\User;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

describe('SendSlackNotificationJob via job handle', function (): void {
    test('it sends slack notification successfully', function (): void {
        Log::spy();

        $url = Url::factory()->create([
            'notify_slack' => true,
            'slack_webhook_url' => 'https://hooks.slack.com/services/TEST',
        ]);

        $request = RequestModel::factory()->for($url)->create();

        Http::fake(['hooks.slack.com/*' => Http::response('ok', 200)]);

        $job = new SendSlackNotificationJob($request, $url);
        $job->handle(app(\App\Services\IpSafetyService::class));

        Http::assertSent(fn ($httpRequest): bool => str_contains((string) $httpRequest->url(), 'hooks.slack.com'));

        Log::shouldHaveReceived('info')->with('Slack notification sent', \Mockery::any());
    });

    test('it skips when slack notification is not enabled', function (): void {
        $url = Url::factory()->create([
            'notify_slack' => false,
            'slack_webhook_url' => null,
        ]);

        $request = RequestModel::factory()->for($url)->create();

        Http::fake();

        $job = new SendSlackNotificationJob($request, $url);
        $job->handle(app(\App\Services\IpSafetyService::class));

        Http::assertNothingSent();
    });

    test('it logs warning on failed slack notification', function (): void {
        Log::spy();

        $url = Url::factory()->create([
            'notify_slack' => true,
            'slack_webhook_url' => 'https://hooks.slack.com/services/TEST',
        ]);

        $request = RequestModel::factory()->for($url)->create();

        Http::fake(['hooks.slack.com/*' => Http::response('Error', 500)]);

        $job = new SendSlackNotificationJob($request, $url);
        $job->handle(app(\App\Services\IpSafetyService::class));

        Log::shouldHaveReceived('warning')->with('Slack notification failed', \Mockery::any());
    });
});

test('it throws exception and logs error when HTTP call fails', function (): void {
    Log::spy();

    $url = Url::factory()->create([
        'notify_slack' => true,
        'slack_webhook_url' => 'https://hooks.slack.com/services/TEST',
    ]);

    $request = RequestModel::factory()->for($url)->create();

    Http::fake(['hooks.slack.com/*' => fn () => throw new \RuntimeException('Connection failed')]);

    $job = new SendSlackNotificationJob($request, $url);

    expect(fn () => $job->handle(app(\App\Services\IpSafetyService::class)))->toThrow(\RuntimeException::class);

    Log::shouldHaveReceived('error')->with('Slack notification error', \Mockery::any());
});

test('it sends slack notification for GET request', function (): void {
    Log::spy();

    $url = Url::factory()->create([
        'notify_slack' => true,
        'slack_webhook_url' => 'https://hooks.slack.com/services/TEST',
    ]);

    $request = RequestModel::factory()->get()->for($url)->create();

    Http::fake(['hooks.slack.com/*' => Http::response('ok', 200)]);

    $job = new SendSlackNotificationJob($request, $url);
    $job->handle(app(\App\Services\IpSafetyService::class));

    Http::assertSent(fn ($r): bool => str_contains((string) $r->url(), 'hooks.slack.com'));
});

test('it sends slack notification for PUT request', function (): void {
    Log::spy();

    $url = Url::factory()->create([
        'notify_slack' => true,
        'slack_webhook_url' => 'https://hooks.slack.com/services/TEST',
    ]);

    $request = RequestModel::factory()->for($url)->create(['method' => 'PUT']);

    Http::fake(['hooks.slack.com/*' => Http::response('ok', 200)]);

    $job = new SendSlackNotificationJob($request, $url);
    $job->handle(app(\App\Services\IpSafetyService::class));

    Http::assertSent(fn ($r): bool => str_contains((string) $r->url(), 'hooks.slack.com'));
});

test('it sends slack notification for POST request', function (): void {
    Log::spy();

    $url = Url::factory()->create([
        'notify_slack' => true,
        'slack_webhook_url' => 'https://hooks.slack.com/services/TEST',
    ]);

    $request = RequestModel::factory()->for($url)->create(['method' => 'POST']);

    Http::fake(['hooks.slack.com/*' => Http::response('ok', 200)]);

    $job = new SendSlackNotificationJob($request, $url);
    $job->handle(app(\App\Services\IpSafetyService::class));

    Http::assertSent(fn ($r): bool => str_contains((string) $r->url(), 'hooks.slack.com'));
});

test('it sends slack notification for PATCH request', function (): void {
    Log::spy();

    $url = Url::factory()->create([
        'notify_slack' => true,
        'slack_webhook_url' => 'https://hooks.slack.com/services/TEST',
    ]);

    $request = RequestModel::factory()->for($url)->create(['method' => 'PATCH']);

    Http::fake(['hooks.slack.com/*' => Http::response('ok', 200)]);

    $job = new SendSlackNotificationJob($request, $url);
    $job->handle(app(\App\Services\IpSafetyService::class));

    Http::assertSent(fn ($r): bool => str_contains((string) $r->url(), 'hooks.slack.com'));
});

test('it sends slack notification for DELETE request', function (): void {
    Log::spy();

    $url = Url::factory()->create([
        'notify_slack' => true,
        'slack_webhook_url' => 'https://hooks.slack.com/services/TEST',
    ]);

    $request = RequestModel::factory()->for($url)->create(['method' => 'DELETE']);

    Http::fake(['hooks.slack.com/*' => Http::response('ok', 200)]);

    $job = new SendSlackNotificationJob($request, $url);
    $job->handle(app(\App\Services\IpSafetyService::class));

    Http::assertSent(fn ($r): bool => str_contains((string) $r->url(), 'hooks.slack.com'));
});

test('it sends slack notification for non-standard method using default color', function (): void {
    Log::spy();

    $url = Url::factory()->create([
        'notify_slack' => true,
        'slack_webhook_url' => 'https://hooks.slack.com/services/TEST',
    ]);

    $request = RequestModel::factory()->for($url)->create(['method' => 'OPTIONS']);

    Http::fake(['hooks.slack.com/*' => Http::response('ok', 200)]);

    $job = new SendSlackNotificationJob($request, $url);
    $job->handle(app(\App\Services\IpSafetyService::class));

    Http::assertSent(fn ($r): bool => str_contains((string) $r->url(), 'hooks.slack.com'));
});

test('tags returns correct identifiers', function (): void {
    $url = Url::factory()->create([
        'notify_slack' => true,
        'slack_webhook_url' => 'https://hooks.slack.com/services/TEST',
    ]);

    $request = RequestModel::factory()->for($url)->create();

    $job = new SendSlackNotificationJob($request, $url);
    $tags = $job->tags();

    expect($tags)->toContain('slack-notification');
    expect($tags)->toContain('request:'.$request->resource_id);
    expect($tags)->toContain('url:'.$url->resource_id);
});

describe('Slack notification dispatch via HTTP route', function (): void {
    test('it dispatches slack notification for user with slack enabled', function (): void {
        Bus::fake();

        $user = User::factory()->create();
        $url = Url::factory()->for($user)->create([
            'notify_slack' => true,
            'slack_webhook_url' => 'https://hooks.slack.com/services/TEST',
        ]);

        $response = $this->post("/catch/{$url->resource_id}", ['test' => 'data']);

        $response->assertStatus(200);
        Bus::assertDispatched(SendSlackNotificationJob::class);
    });
});
