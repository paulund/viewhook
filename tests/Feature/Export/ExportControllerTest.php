<?php

declare(strict_types=1);

use App\Models\Url;
use App\Models\User;

describe('Export Controller', function (): void {
    test('other user cannot export another users url', function (): void {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $url = Url::factory()->for($otherUser)->create();

        $response = $this->actingAs($user)->get(route('urls.export', ['url' => $url->resource_id]));

        $response->assertStatus(403);
    });

    test('user can export url requests as csv', function (): void {
        $user = User::factory()->create();
        $url = Url::factory()->for($user)->create();
        $url->requests()->create([
            'resource_id' => 'req_test_'.uniqid(),
            'method' => 'POST',
            'path' => '/',
            'content_type' => 'application/json',
            'content_length' => 13,
            'headers' => ['Content-Type' => 'application/json'],
            'body' => '{"test":true}',
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->actingAs($user)->get(route('urls.export', ['url' => $url->resource_id, 'format' => 'csv']));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    });

    test('user can export url requests as json', function (): void {
        $user = User::factory()->create();
        $url = Url::factory()->for($user)->create();

        $response = $this->actingAs($user)->get(route('urls.export', ['url' => $url->resource_id, 'format' => 'json']));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
    });

    test('invalid export format returns validation error', function (): void {
        $user = User::factory()->create();
        $url = Url::factory()->for($user)->create();

        $response = $this->actingAs($user)->get(route('urls.export', ['url' => $url->resource_id, 'format' => 'invalid']));

        $response->assertSessionHasErrors('format');
    });
});
