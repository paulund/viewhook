<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Url;
use App\Services\RateLimitService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Url
 */
final class UrlResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[\Override]
    public function toArray(Request $request): array
    {
        $rateLimitService = app(RateLimitService::class);
        $rateLimitInfo = $rateLimitService->getRateLimitInfo($this->resource);

        return [
            'id' => $this->resource_id,
            'name' => $this->name,
            'description' => $this->description,
            'last_request_at' => $this->last_request_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'endpoint_url' => $this->getUrl(),
            'requests_count' => $this->whenCounted('requests'),
            'requests_expire_after_hours' => (int) config('viewhook.retention_hours', 168),
            'rate_limit' => [
                'limit' => $rateLimitInfo['limit'],
                'remaining' => $rateLimitInfo['remaining'],
                'used' => $rateLimitInfo['used'],
                'percentage' => $rateLimitInfo['percentage'],
                'reset_at' => $rateLimitInfo['reset_at'],
            ],
            'forward_to_url' => $this->forward_to_url,
            'forward_method' => $this->forward_method,
            'forward_headers' => $this->forward_headers,
            'has_forwarding' => $this->hasForwarding(),
            'notify_email' => $this->notify_email,
            'notify_slack' => $this->notify_slack,
            'slack_webhook_url' => $this->slack_webhook_url !== null
                ? str_repeat('*', max(0, strlen($this->slack_webhook_url) - 4)).substr($this->slack_webhook_url, -4)
                : null,
            'has_slack_webhook_url' => $this->slack_webhook_url !== null,
            'has_email_notification' => $this->hasEmailNotification(),
            'has_slack_notification' => $this->hasSlackNotification(),
            'requests' => $this->when($this->relationLoaded('requests'), fn () => RequestResource::collection($this->requests)->resolve()),
        ];
    }
}
