<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Request as RequestModel;
use App\Models\Url;
use App\Models\WebhookForward;
use App\Services\WebhookForwardingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

final class ForwardWebhookJob implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var array<int, int>
     */
    public array $backoff = [10, 30, 60];

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly RequestModel $request,
        public readonly Url $url,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(WebhookForwardingService $service): void
    {
        // Verify the URL still has forwarding enabled
        if (! $this->url->hasForwarding()) {
            Log::info('Webhook forwarding skipped - forwarding disabled', [
                'request_id' => $this->request->resource_id,
                'url_id' => $this->url->resource_id,
            ]);

            return;
        }

        /** @var string $targetUrl */
        $targetUrl = $this->url->forward_to_url;

        $forward = WebhookForward::create([
            'request_id' => $this->request->id,
            'url_id' => $this->url->id,
            'target_url' => $targetUrl,
            'method' => $this->url->forward_method,
        ]);

        $result = $service->forward($this->request, $this->url);

        if ($result->error !== null) {
            $forward->update([
                'error' => $result->error,
                'response_time_ms' => $result->responseTimeMs,
            ]);

            Log::warning('Webhook forwarding failed', [
                'forward_id' => $forward->resource_id,
                'request_id' => $this->request->resource_id,
                'target_url' => $targetUrl,
                'error' => $result->error,
            ]);
        } else {
            $forward->update([
                'status_code' => $result->statusCode,
                'response_body' => $result->responseBody,
                'response_time_ms' => $result->responseTimeMs,
            ]);

            Log::info('Webhook forwarded successfully', [
                'forward_id' => $forward->resource_id,
                'request_id' => $this->request->resource_id,
                'target_url' => $targetUrl,
                'status_code' => $result->statusCode,
                'response_time_ms' => $result->responseTimeMs,
            ]);
        }
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array<int, string>
     */
    public function tags(): array
    {
        return [
            'webhook-forward',
            'request:'.$this->request->resource_id,
            'url:'.$this->url->resource_id,
        ];
    }
}
