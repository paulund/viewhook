<?php

declare(strict_types=1);

use App\Events\RequestCaptured;
use App\Models\Url;
use App\Models\User;
use Illuminate\Support\Facades\Event;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->url = Url::factory()->for($this->user)->create();
});

describe('request capture endpoint', function (): void {
    it('captures a GET request', function (): void {
        $response = $this->get("/catch/{$this->url->resource_id}");

        $response->assertOk();
        $response->assertJson(['message' => 'Request captured successfully.']);

        $this->assertDatabaseHas('requests', [
            'url_id' => $this->url->id,
            'method' => 'GET',
            'path' => '/',
        ]);
    });

    it('captures a POST request with JSON body', function (): void {
        $payload = ['name' => 'Test', 'value' => 123];

        $response = $this->postJson("/catch/{$this->url->resource_id}", $payload);

        $response->assertOk();

        $this->assertDatabaseHas('requests', [
            'url_id' => $this->url->id,
            'method' => 'POST',
            'content_type' => 'application/json',
        ]);

        $request = $this->url->requests()->first();
        expect($request->body)->toContain('"name"');
        expect($request->getParsedBody())->toBe($payload);
    });

    it('captures requests with subpaths', function (): void {
        $response = $this->post("/catch/{$this->url->resource_id}/webhooks/stripe");

        $response->assertOk();

        $this->assertDatabaseHas('requests', [
            'url_id' => $this->url->id,
            'path' => '/webhooks/stripe',
        ]);
    });

    it('captures query parameters', function (): void {
        $response = $this->get("/catch/{$this->url->resource_id}?foo=bar&baz=qux");

        $response->assertOk();

        $request = $this->url->requests()->first();
        expect($request->query_params)->toBe(['foo' => 'bar', 'baz' => 'qux']);
    });

    it('captures request headers', function (): void {
        $response = $this->get("/catch/{$this->url->resource_id}", [
            'X-Custom-Header' => 'custom-value',
        ]);

        $response->assertOk();

        $request = $this->url->requests()->first();
        expect($request->headers)->toHaveKey('x-custom-header');
    });

    it('updates url last_request_at timestamp', function (): void {
        expect($this->url->last_request_at)->toBeNull();

        $this->get("/catch/{$this->url->resource_id}");

        $this->url->refresh();
        expect($this->url->last_request_at)->not->toBeNull();
    });

    it('returns 404 for non-existent url', function (): void {
        $fakeUuid = '00000000-0000-0000-0000-000000000000';

        $response = $this->get("/catch/{$fakeUuid}");

        $response->assertNotFound();
    });

    it('rejects payload exceeding 10 MB', function (): void {
        $largePayload = str_repeat('x', 11 * 1024 * 1024);

        $response = $this->call('POST', "/catch/{$this->url->resource_id}", [], [], [], [
            'CONTENT_TYPE' => 'text/plain',
        ], $largePayload);

        $response->assertStatus(413);
    });

    it('rejects unsupported content types', function (): void {
        $response = $this->call('POST', "/catch/{$this->url->resource_id}", [], [], [], [
            'CONTENT_TYPE' => 'application/octet-stream',
        ], 'binary data');

        $response->assertStatus(415);
    });

    it('supports all HTTP methods', function (string $method): void {
        $response = $this->call($method, "/catch/{$this->url->resource_id}");

        if ($method === 'HEAD') {
            $response->assertOk();
        } else {
            $response->assertOk();
            $response->assertJson(['message' => 'Request captured successfully.']);
        }

        $this->assertDatabaseHas('requests', [
            'url_id' => $this->url->id,
            'method' => $method,
        ]);
    })->with(['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS']);

    it('captures form-urlencoded data', function (): void {
        $response = $this->post("/catch/{$this->url->resource_id}", [
            'email' => 'test@example.com',
            'name' => 'Test User',
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('requests', [
            'url_id' => $this->url->id,
            'method' => 'POST',
        ]);
    });

    it('broadcasts RequestCaptured event when request is captured', function (): void {
        Event::fake([RequestCaptured::class]);

        $this->postJson("/catch/{$this->url->resource_id}", ['test' => 'data']);

        Event::assertDispatched(RequestCaptured::class, fn (RequestCaptured $event): bool => $event->request->url_id === $this->url->id);
    });
});
