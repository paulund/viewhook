<?php

declare(strict_types=1);

namespace App\Actions\Url;

use App\Models\Url;
use App\Models\User;
use Illuminate\Support\Facades\Log;

final readonly class CreateUrlAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $user, array $data): Url
    {
        Log::info('CreateUrlAction::execute');

        return $user->urls()->create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);
    }
}
