<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Hash;

describe('Confirm Password', function (): void {
    test('authenticated user can confirm their password', function (): void {
        $user = User::factory()->create(['password' => Hash::make('correct-password')]);

        $response = $this->actingAs($user)->post('/confirm-password', [
            'password' => 'correct-password',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
    });

    test('confirm password fails with incorrect password', function (): void {
        $user = User::factory()->create(['password' => Hash::make('correct-password')]);

        $response = $this->actingAs($user)->post('/confirm-password', [
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors();
    });

    test('confirm password requires password field', function (): void {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/confirm-password', []);

        $response->assertSessionHasErrors('password');
    });
});
