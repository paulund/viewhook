<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CaptureRequestAction;
use App\Models\Url;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CaptureController extends Controller
{
    public function __construct(
        private readonly CaptureRequestAction $captureRequest,
    ) {}

    public function capture(Request $request, Url $url, ?string $path = null): JsonResponse
    {
        $this->captureRequest->execute($request, $url, $path);

        return response()->json([
            'message' => 'Request captured successfully.',
            'url' => $url->name,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
