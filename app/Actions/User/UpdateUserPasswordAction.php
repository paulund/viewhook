<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

final readonly class UpdateUserPasswordAction
{
    public function execute(User $user, string $newPassword): User
    {
        Log::info('UpdateUserPasswordAction::execute');

        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        return $user;
    }
}
