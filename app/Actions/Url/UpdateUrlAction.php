<?php

declare(strict_types=1);

namespace App\Actions\Url;

use App\Models\Url;
use Illuminate\Support\Facades\Log;

final readonly class UpdateUrlAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Url $url, array $data): Url
    {
        Log::info('UpdateUrlAction::execute', [
            'url_id' => $url->resource_id,
        ]);

        $url->update($data);

        return $url->fresh() ?? $url;
    }
}
