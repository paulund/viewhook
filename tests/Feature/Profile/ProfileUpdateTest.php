<?php

use App\Models\User;

describe('ProfileController - Update', function (): void {
    test('update action updates profile information', function (): void {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
        ]);

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => 'New Name',
            'email' => 'new@example.com',
        ]);

        $response->assertRedirect('/profile');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
            'email' => 'new@example.com',
        ]);
    });

    test('update action validates required fields', function (): void {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => '',
            'email' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'email']);
    });

    test('update action validates email format', function (): void {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => 'Valid Name',
            'email' => 'invalid-email',
        ]);

        $response->assertSessionHasErrors(['email']);
    });

    test('update action validates email max length', function (): void {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => 'Valid Name',
            'email' => str_repeat('a', 256),
        ]);

        $response->assertSessionHasErrors(['email']);
    });

    test('update action validates name max length', function (): void {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => str_repeat('a', 256),
            'email' => 'valid@example.com',
        ]);

        $response->assertSessionHasErrors(['name']);
    });

    test('update action validates email uniqueness', function (): void {
        $user1 = User::factory()->create(['email' => 'existing@example.com']);
        $user2 = User::factory()->create(['email' => 'user2@example.com']);

        $response = $this->actingAs($user2)->patch('/profile', [
            'name' => 'User 2',
            'email' => 'existing@example.com',
        ]);

        $response->assertSessionHasErrors(['email']);
    });

    test('update action allows user to keep their existing email', function (): void {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => 'John Updated',
            'email' => 'john@example.com',
        ]);

        $response->assertRedirect('/profile');
    });

    test('destroy action deletes user account', function (): void {
        $user = User::factory()->create();
        $userId = $user->id;

        $response = $this->actingAs($user)->delete('/profile', [
            'password' => 'password',
        ]);

        $response->assertRedirect('/');
        $this->assertDatabaseMissing('users', ['id' => $userId]);
    });

    test('destroy action logs out user', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user)->delete('/profile', [
            'password' => 'password',
        ]);

        $this->assertGuest();
    });
});
