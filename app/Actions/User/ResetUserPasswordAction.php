<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final readonly class ResetUserPasswordAction
{
    public function execute(User $user, string $newPassword): User
    {
        Log::info('ResetUserPasswordAction::execute');

        $user->forceFill([
            'password' => Hash::make($newPassword),
            'remember_token' => Str::random(60),
        ])->save();

        event(new PasswordReset($user));

        return $user;
    }
}
