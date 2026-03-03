<?php

declare(strict_types=1);

namespace App\Actions\Url;

use App\Models\Url;
use Illuminate\Support\Facades\Log;

final readonly class DeleteUrlAction
{
    public function execute(Url $url): void
    {
        Log::info('DeleteUrlAction::execute', [
            'url_id' => $url->resource_id,
        ]);

        $url->delete();
    }
}
