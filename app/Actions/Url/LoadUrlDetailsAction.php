<?php

declare(strict_types=1);

namespace App\Actions\Url;

use App\Models\Url;

final readonly class LoadUrlDetailsAction
{
    public function execute(Url $url): Url
    {
        $url->loadCount('requests');
        $url->load(['requests' => fn ($q) => $q->latest()->limit(5)]);
        $url->loadCount('webhookForwards');
        $url->load(['webhookForwards' => fn ($q) => $q->latest()->limit(5)]);

        return $url;
    }
}
