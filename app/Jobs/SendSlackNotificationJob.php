<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Request;
use App\Models\Url;
use App\Services\IpSafetyService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

final class SendSlackNotificationJob implements ShouldQueue
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
    public array $backoff = [5, 15, 30];

    public function __construct(
        public readonly Request $request,
        public readonly Url $url,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(IpSafetyService $ipSafetyService): void
    {
        if (! $this->url->hasSlackNotification()) {
            return;
        }

        /** @var string $webhookUrl */
        $webhookUrl = $this->url->slack_webhook_url;

        try {
            // Re-validate at execution time to prevent SSRF via DNS rebinding (TOCTOU)
            $ipSafetyService->assertSafe($webhookUrl);

            $payload = $this->buildSlackPayload();

            $response = Http::timeout(10)
                ->post($webhookUrl, $payload);

            if (! $response->successful()) {
                Log::warning('Slack notification failed', [
                    'request_id' => $this->request->resource_id,
                    'url_id' => $this->url->resource_id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            } else {
                Log::info('Slack notification sent', [
                    'request_id' => $this->request->resource_id,
                    'url_id' => $this->url->resource_id,
                ]);
            }
        } catch (Throwable $e) {
            Log::error('Slack notification error', [
                'request_id' => $this->request->resource_id,
                'url_id' => $this->url->resource_id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Build the Slack message payload.
     *
     * @return array<string, mixed>
     */
    private function buildSlackPayload(): array
    {
        $requestUrl = route('urls.requests.show', [
            'url' => $this->url->resource_id,
            'request' => $this->request->resource_id,
        ]);

        $methodColor = match ($this->request->method) {
            'GET' => '#22c55e',     // green
            'POST' => '#3b82f6',    // blue
            'PUT' => '#f59e0b',     // amber
            'PATCH' => '#8b5cf6',   // purple
            'DELETE' => '#ef4444', // red
            default => '#6b7280',  // gray
        };

        return [
            'blocks' => [
                [
                    'type' => 'header',
                    'text' => [
                        'type' => 'plain_text',
                        'text' => "🔔 New {$this->request->method} Request",
                        'emoji' => true,
                    ],
                ],
                [
                    'type' => 'section',
                    'fields' => [
                        [
                            'type' => 'mrkdwn',
                            'text' => "*Endpoint:*\n{$this->url->name}",
                        ],
                        [
                            'type' => 'mrkdwn',
                            'text' => "*Path:*\n`{$this->request->path}`",
                        ],
                        [
                            'type' => 'mrkdwn',
                            'text' => "*Content Type:*\n".($this->request->content_type ?? 'N/A'),
                        ],
                        [
                            'type' => 'mrkdwn',
                            'text' => "*Size:*\n{$this->request->content_length} bytes",
                        ],
                    ],
                ],
                [
                    'type' => 'section',
                    'text' => [
                        'type' => 'mrkdwn',
                        'text' => '*IP Address:* '.($this->request->ip_address ?? 'N/A'),
                    ],
                    'accessory' => [
                        'type' => 'button',
                        'text' => [
                            'type' => 'plain_text',
                            'text' => 'View Request',
                            'emoji' => true,
                        ],
                        'url' => $requestUrl,
                        'action_id' => 'view_request',
                    ],
                ],
                [
                    'type' => 'context',
                    'elements' => [
                        [
                            'type' => 'mrkdwn',
                            'text' => "Captured at {$this->request->created_at->format('Y-m-d H:i:s')} UTC",
                        ],
                    ],
                ],
            ],
            'attachments' => [
                [
                    'color' => $methodColor,
                    'blocks' => [],
                ],
            ],
        ];
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array<int, string>
     */
    public function tags(): array
    {
        return [
            'slack-notification',
            'request:'.$this->request->resource_id,
            'url:'.$this->url->resource_id,
        ];
    }
}
