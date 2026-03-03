<?php

declare(strict_types=1);

use App\Models\Url;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{resourceId}', fn (User $user, string $resourceId): bool => $user->resource_id === $resourceId);

Broadcast::channel('urls.{resourceId}', function ($user, string $resourceId): bool {
    $url = Url::where('resource_id', $resourceId)->first();

    return $url !== null && $url->user_id === $user->id;
});
