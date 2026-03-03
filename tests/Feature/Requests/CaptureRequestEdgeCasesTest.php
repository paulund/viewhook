<?php

use App\Models\Url;
use App\Models\User;

describe('Capture Request - Edge Cases', function (): void {
    test('capture request action handles multiple path segments', function (): void {
        $user = User::factory()->create();
        $url = Url::factory()->for($user)->create();

        $response = $this->post("/catch/{$url->resource_id}/deeply/nested/path", [
            'test' => 'data',
        ]);

        $response->assertStatus(200);
    });

    test('rejects request with single header exceeding 16 KB', function (): void {
        $url = Url::factory()->create();
        $largeValue = str_repeat('a', 17000);

        $response = $this->get("/catch/{$url->resource_id}", [
            'X-Large-Header' => $largeValue,
        ]);

        $response->assertStatus(431);
    });

    test('rejects request with total headers exceeding 64 KB', function (): void {
        $url = Url::factory()->create();

        $headers = [];
        for ($i = 0; $i < 10; $i++) {
            $headers["X-Header-{$i}"] = str_repeat('a', 8000);
        }

        $response = $this->get("/catch/{$url->resource_id}", $headers);

        $response->assertStatus(431);
    });

    test('rejects request with unsupported HTTP method', function (): void {
        $url = Url::factory()->create();

        $response = $this->call('TRACE', "/catch/{$url->resource_id}");

        $response->assertStatus(405);
    });

    test('rejects request when Content-Length header exceeds 10 MB', function (): void {
        $url = Url::factory()->create();
        $oversizeLength = 11 * 1024 * 1024;

        $response = $this->withHeaders(['Content-Length' => (string) $oversizeLength])
            ->post("/catch/{$url->resource_id}");

        $response->assertStatus(413);
    });
});
