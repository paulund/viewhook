<?php

use App\Models\Url;
use App\Models\User;

describe('DashboardController', function (): void {
    test('it renders dashboard with user data', function (): void {
        $user = User::factory()->create();
        Url::factory(3)->for($user)->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page
                ->component('Dashboard')
                ->has('urls', 3)
        );
    });

    test('it includes stats in dashboard view', function (): void {
        $user = User::factory()->create();
        Url::factory(5)->for($user)->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertInertia(
            fn ($page) => $page
                ->component('Dashboard')
                ->where('stats.total_urls', 5)
                ->where('stats.total_requests', 0)
        );
    });

    test('it shows total urls count correctly', function (): void {
        $user = User::factory()->create();
        Url::factory(10)->for($user)->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertInertia(
            fn ($page) => $page
                ->component('Dashboard')
                ->where('stats.total_urls', 10)
        );
    });

    test('it limits dashboard urls to 5 most recent', function (): void {
        $user = User::factory()->create();
        Url::factory(10)->for($user)->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertInertia(
            fn ($page) => $page
                ->component('Dashboard')
                ->has('urls', 5)
                ->where('stats.total_urls', 10)
        );
    });

    test('it only shows urls belonging to the authenticated user', function (): void {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        Url::factory(3)->for($user)->create();
        Url::factory(5)->for($otherUser)->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertInertia(
            fn ($page) => $page
                ->component('Dashboard')
                ->has('urls', 3)
                ->where('stats.total_urls', 3)
        );
    });
});
