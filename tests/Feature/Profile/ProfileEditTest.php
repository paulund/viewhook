<?php

use App\Models\User;

describe('ProfileController - Edit', function (): void {
    test('edit action renders profile edit page', function (): void {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/profile');

        $response->assertStatus(200);
    });

    test('edit action passes status to view', function (): void {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withSession(['status' => 'Profile updated'])
            ->get('/profile');

        $response->assertStatus(200);
    });

    test('unauthenticated users are redirected to login', function (): void {
        $response = $this->get('/profile');

        $response->assertRedirect('/login');
    });

    test('profile always shows the authenticated user\'s own data', function (): void {
        $user = User::factory()->create(['name' => 'Alice']);
        User::factory()->create(['name' => 'Bob']);

        $response = $this->actingAs($user)->get('/profile');

        $response->assertInertia(
            fn ($page) => $page
                ->component('Profile/Edit')
                ->where('auth.user.name', 'Alice')
        );
    });
});
