<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Facades\Log;

final readonly class DeleteUserAccountAction
{
    public function execute(User $user): void
    {
        Log::info('DeleteUserAccountAction::execute');

        $user->delete();
    }
}
