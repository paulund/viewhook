<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Url;
use App\Models\User;

final class UrlPolicy
{
    public function viewAny(): bool
    {
        return true;
    }

    public function view(User $user, Url $url): bool
    {
        return $url->user_id === $user->id;
    }

    public function create(): bool
    {
        return true;
    }

    public function update(User $user, Url $url): bool
    {
        return $user->id === $url->user_id;
    }

    public function delete(User $user, Url $url): bool
    {
        return $user->id === $url->user_id;
    }
}
