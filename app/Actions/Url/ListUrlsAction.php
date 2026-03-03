<?php

declare(strict_types=1);

namespace App\Actions\Url;

use App\Models\Url;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

final readonly class ListUrlsAction
{
    /**
     * @return Collection<int, Url>
     */
    public function execute(User $user): Collection
    {
        return Url::query()
            ->where('user_id', $user->id)
            ->withCount('requests')
            ->latest()
            ->get();
    }
}
