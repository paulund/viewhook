<?php

use App\Models\Url;
use App\Models\User;

describe('Url - Updates', function (): void {
    test('update url request with name update', function (): void {
        $user = User::factory()->create();
        $url = Url::factory()->for($user)->create(['name' => 'Old Name']);

        $response = $this->actingAs($user)->put("/urls/{$url->resource_id}", [
            'name' => 'New Name',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('urls', [
            'resource_id' => $url->resource_id,
            'name' => 'New Name',
        ]);
    });

    test('user cannot set forward_to_url to a private IP', function (): void {
        $user = User::factory()->create();
        $url = Url::factory()->for($user)->create();

        $response = $this->actingAs($user)->put("/urls/{$url->resource_id}", [
            'forward_to_url' => 'https://10.0.0.1/webhook',
        ]);

        $response->assertSessionHasErrors('forward_to_url');
    });

    test('user cannot set forward_to_url to an http URL', function (): void {
        $user = User::factory()->create();
        $url = Url::factory()->for($user)->create();

        $response = $this->actingAs($user)->put("/urls/{$url->resource_id}", [
            'forward_to_url' => 'http://example.com/webhook',
        ]);

        $response->assertSessionHasErrors('forward_to_url');
    });
});
