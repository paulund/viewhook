<?php

declare(strict_types=1);

namespace App\Actions\Request;

use App\Models\Request;
use Illuminate\Support\Facades\Log;

final readonly class DeleteRequestAction
{
    public function execute(Request $request): void
    {
        Log::info('DeleteRequestAction::execute', [
            'request_id' => $request->resource_id,
        ]);

        $request->delete();
    }
}
