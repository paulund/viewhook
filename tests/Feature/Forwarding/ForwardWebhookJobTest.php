<?php

declare(strict_types=1);

use App\Jobs\ForwardWebhookJob;
use App\Models\Request as RequestModel;
use App\Models\Url;
use App\Models\User;
use App\Services\WebhookForwardingService;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

describe('ForwardWebhookJob via job handle', function (): void {
    test('it skips forwarding when forwarding is disabled', function (): void {
        Log::spy();

        $user = User::factory()->create();
        $url = Url::factory()->for($user)->create(['forward_to_url' => null]);
        $request = RequestModel::factory()->for($url)->create();

        Http::fake();

        $job = new ForwardWebhookJob($request, $url);
        $job->handle(app(WebhookForwardingService::class));

        Http::assertNothingSent();
        Log::shouldHaveReceived('info')->with('Webhook forwarding skipped - forwarding disabled', \Mockery::any());
    });

    test('it creates a webhook forward record and updates it with success data', function (): void {
        $user = User::factory()->create();
        $url = Url::factory()->for($user)->create([
            'forward_to_url' => 'https://example.com/webhook',
            'forward_method' => 'POST',
        ]);
        $request = RequestModel::factory()->post()->for($url)->create();

        Http::fake(['https://example.com/webhook' => Http::response('{"ok":true}', 200)]);

        $job = new ForwardWebhookJob($request, $url);
        $job->handle(app(WebhookForwardingService::class));

        $this->assertDatabaseHas('webhook_forwards', [
            'request_id' => $request->id,
            'url_id' => $url->id,
            'target_url' => 'https://example.com/webhook',
            'method' => 'POST',
            'status_code' => 200,
            'response_body' => '{"ok":true}',
            'error' => null,
        ]);
    });

    test('it creates a webhook forward record and updates it with error on connection failure', function (): void {
        $user = User::factory()->create();
        $url = Url::factory()->for($user)->create([
            'forward_to_url' => 'https://example.com/webhook',
            'forward_method' => 'POST',
        ]);
        $request = RequestModel::factory()->post()->for($url)->create();

        Http::fake(fn () => throw new \Illuminate\Http\Client\ConnectionException('Connection refused'));

        $job = new ForwardWebhookJob($request, $url);
        $job->handle(app(WebhookForwardingService::class));

        $forward = \App\Models\WebhookForward::where('request_id', $request->id)->first();

        expect($forward)->not->toBeNull()
            ->and($forward->error)->toContain('Connection refused')
            ->and($forward->status_code)->toBeNull();
    });

    test('it returns correct tags', function (): void {
        $url = Url::factory()->create();
        $request = RequestModel::factory()->for($url)->create();

        $job = new ForwardWebhookJob($request, $url);
        $tags = $job->tags();

        expect($tags)->toContain('webhook-forward');
        expect($tags)->toContain('request:'.$request->resource_id);
        expect($tags)->toContain('url:'.$url->resource_id);
    });
});

test('it forwards with GET method', function (): void {
    $user = User::factory()->create();
    $url = Url::factory()->for($user)->create([
        'forward_to_url' => 'https://example.com/webhook',
        'forward_method' => 'GET',
    ]);
    $request = RequestModel::factory()->get()->for($url)->create();

    Http::fake(['https://example.com/webhook' => Http::response('ok', 200)]);

    $job = new ForwardWebhookJob($request, $url);
    $job->handle(app(WebhookForwardingService::class));

    $this->assertDatabaseHas('webhook_forwards', [
        'request_id' => $request->id,
        'status_code' => 200,
    ]);
});

test('it forwards with PUT method and non-JSON body', function (): void {
    $user = User::factory()->create();
    $url = Url::factory()->for($user)->create([
        'forward_to_url' => 'https://example.com/webhook',
        'forward_method' => 'PUT',
    ]);
    $request = RequestModel::factory()->for($url)->create([
        'method' => 'PUT',
        'content_type' => 'text/plain',
        'body' => 'plain text body',
    ]);

    Http::fake(['https://example.com/webhook' => Http::response('ok', 200)]);

    $job = new ForwardWebhookJob($request, $url);
    $job->handle(app(WebhookForwardingService::class));

    $this->assertDatabaseHas('webhook_forwards', [
        'request_id' => $request->id,
        'status_code' => 200,
    ]);
});

test('it forwards with PATCH method', function (): void {
    $user = User::factory()->create();
    $url = Url::factory()->for($user)->create([
        'forward_to_url' => 'https://example.com/webhook',
        'forward_method' => 'PATCH',
    ]);
    $request = RequestModel::factory()->for($url)->create([
        'method' => 'PATCH',
        'content_type' => 'application/json',
        'body' => '{"key":"value"}',
    ]);

    Http::fake(['https://example.com/webhook' => Http::response('ok', 200)]);

    $job = new ForwardWebhookJob($request, $url);
    $job->handle(app(WebhookForwardingService::class));

    $this->assertDatabaseHas('webhook_forwards', [
        'request_id' => $request->id,
        'status_code' => 200,
    ]);
});

test('it forwards with DELETE method', function (): void {
    $user = User::factory()->create();
    $url = Url::factory()->for($user)->create([
        'forward_to_url' => 'https://example.com/webhook',
        'forward_method' => 'DELETE',
    ]);
    $request = RequestModel::factory()->get()->for($url)->create(['method' => 'DELETE']);

    Http::fake(['https://example.com/webhook' => Http::response('ok', 200)]);

    $job = new ForwardWebhookJob($request, $url);
    $job->handle(app(WebhookForwardingService::class));

    $this->assertDatabaseHas('webhook_forwards', [
        'request_id' => $request->id,
        'status_code' => 200,
    ]);
});

test('it merges custom forward headers from url config', function (): void {
    $user = User::factory()->create();
    $url = Url::factory()->for($user)->create([
        'forward_to_url' => 'https://example.com/webhook',
        'forward_method' => 'POST',
        'forward_headers' => ['X-Custom-Header' => 'custom-value'],
    ]);
    $request = RequestModel::factory()->post()->for($url)->create();

    Http::fake(['https://example.com/webhook' => Http::response('ok', 200)]);

    $job = new ForwardWebhookJob($request, $url);
    $job->handle(app(WebhookForwardingService::class));

    Http::assertSent(fn ($httpRequest) => $httpRequest->hasHeader('X-Custom-Header', 'custom-value'));
});

test('it truncates large response bodies', function (): void {
    $user = User::factory()->create();
    $url = Url::factory()->for($user)->create([
        'forward_to_url' => 'https://example.com/webhook',
        'forward_method' => 'POST',
    ]);
    $request = RequestModel::factory()->post()->for($url)->create();

    $largeBody = str_repeat('x', 65 * 1024 + 100); // > 64 KB

    Http::fake(['https://example.com/webhook' => Http::response($largeBody, 200)]);

    $job = new ForwardWebhookJob($request, $url);
    $job->handle(app(WebhookForwardingService::class));

    $forward = \App\Models\WebhookForward::where('request_id', $request->id)->first();

    expect($forward)->not->toBeNull()
        ->and($forward->response_body)->toEndWith('... [truncated]');
});

test('it truncates long error messages', function (): void {
    $user = User::factory()->create();
    $url = Url::factory()->for($user)->create([
        'forward_to_url' => 'https://example.com/webhook',
        'forward_method' => 'POST',
    ]);
    $request = RequestModel::factory()->post()->for($url)->create();

    $longMessage = str_repeat('e', 1100);

    Http::fake(fn () => throw new \RuntimeException($longMessage));

    $job = new ForwardWebhookJob($request, $url);
    $job->handle(app(WebhookForwardingService::class));

    $forward = \App\Models\WebhookForward::where('request_id', $request->id)->first();

    expect($forward)->not->toBeNull()
        ->and($forward->error)->toEndWith('...')
        ->and(strlen((string) $forward->error))->toBe(1003); // 1000 chars + '...'
});

describe('ForwardWebhookJob dispatch via HTTP route', function (): void {
    test('it dispatches forwarding job for user with forwarding enabled', function (): void {
        Bus::fake();

        $user = User::factory()->create();
        $url = Url::factory()->for($user)->create([
            'forward_to_url' => 'https://example.com/webhook',
        ]);

        $response = $this->post("/catch/{$url->resource_id}", ['test' => 'data']);

        $response->assertStatus(200);
        Bus::assertDispatched(ForwardWebhookJob::class);
    });

    test('it does not dispatch forwarding job when no forwarding url', function (): void {
        Bus::fake();

        $user = User::factory()->create();
        $url = Url::factory()->for($user)->create([
            'forward_to_url' => null,
        ]);

        $response = $this->post("/catch/{$url->resource_id}", ['test' => 'data']);

        $response->assertStatus(200);
        Bus::assertNotDispatched(ForwardWebhookJob::class);
    });
});
