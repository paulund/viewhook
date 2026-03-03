<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\WebhookForward;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin WebhookForward
 */
final class WebhookForwardResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[\Override]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource_id,
            'status_code' => $this->status_code,
            'response_time_ms' => $this->response_time_ms,
            'error' => $this->error,
            'is_successful' => $this->isSuccessful(),
            'is_failed' => $this->isFailed(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
