<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

describe('Forgot Password', function (): void {
    test('forgot password requires valid email format', function (): void {
        $response = $this->post('/forgot-password', [
            'email' => 'not-an-email',
        ]);

        $response->assertSessionHasErrors('email');
    });

    test('forgot password sends reset link for existing user', function (): void {
        User::factory()->create(['email' => 'test@example.com']);

        $response = $this->post('/forgot-password', [
            'email' => 'test@example.com',
        ]);

        $response->assertSessionHasNoErrors();
    });

    test('forgot password returns error for non-existent email', function (): void {
        $response = $this->post('/forgot-password', [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertSessionHasErrors('email');
    });
});

describe('Reset Password', function (): void {
    test('reset password requires valid token', function (): void {
        $user = User::factory()->create();

        $response = $this->post('/reset-password', [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrors();
    });

    test('reset password with valid token updates password and fires event', function (): void {
        Event::fake([PasswordReset::class]);

        $user = User::factory()->create(['password' => Hash::make('oldpassword')]);
        $token = Password::createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasNoErrors();
        $user->refresh();
        expect(Hash::check('newpassword123', $user->password))->toBeTrue();
        Event::assertDispatched(PasswordReset::class);
    });
});

describe('Update Password', function (): void {
    test('authenticated user can update their password', function (): void {
        $user = User::factory()->create(['password' => Hash::make('currentpassword')]);

        $response = $this->actingAs($user)->put('/password', [
            'current_password' => 'currentpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasNoErrors();
        $user->refresh();
        expect(Hash::check('newpassword123', $user->password))->toBeTrue();
    });

    test('password update requires correct current password', function (): void {
        $user = User::factory()->create(['password' => Hash::make('currentpassword')]);

        $response = $this->actingAs($user)->put('/password', [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrors('current_password');
    });

    test('password update requires authentication', function (): void {
        $response = $this->put('/password', [
            'current_password' => 'currentpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect('/login');
    });
});
