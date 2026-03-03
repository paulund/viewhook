<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Facades\Log;

final readonly class UpdateUserProfileAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $user, array $data): User
    {
        Log::info('UpdateUserProfileAction::execute');

        $user->fill($data);
        $user->save();

        return $user;
    }
}
