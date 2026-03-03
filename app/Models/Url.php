<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasResourceId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $resource_id
 * @property int $user_id
 * @property string $name
 * @property string|null $description
 * @property \Carbon\CarbonImmutable|null $last_request_at
 * @property string|null $forward_to_url
 * @property string $forward_method
 * @property array<string, string>|null $forward_headers
 * @property bool $notify_email
 * @property bool $notify_slack
 * @property string|null $slack_webhook_url
 * @property \Carbon\CarbonImmutable $created_at
 * @property \Carbon\CarbonImmutable $updated_at
 * @property-read User $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Request> $requests
 * @property-read \Illuminate\Database\Eloquent\Collection<int, WebhookForward> $webhookForwards
 */
final class Url extends Model
{
    /** @use HasFactory<\Database\Factories\UrlFactory> */
    use HasFactory;
    use HasResourceId;

    protected $fillable = [
        'resource_id',
        'user_id',
        'name',
        'description',
        'last_request_at',
        'forward_to_url',
        'forward_method',
        'forward_headers',
        'notify_email',
        'notify_slack',
        'slack_webhook_url',
    ];

    /**
     * @return array<string, string>
     */
    #[\Override]
    protected function casts(): array
    {
        return [
            'notify_email' => 'boolean',
            'notify_slack' => 'boolean',
            'forward_headers' => 'array',
            'last_request_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    /**
     * Get the route key name for Laravel route model binding.
     */
    #[\Override]
    public function getRouteKeyName(): string
    {
        return 'resource_id';
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Request, $this>
     */
    public function requests(): HasMany
    {
        return $this->hasMany(Request::class);
    }

    /**
     * @return HasMany<WebhookForward, $this>
     */
    public function webhookForwards(): HasMany
    {
        return $this->hasMany(WebhookForward::class);
    }

    public function getUrl(): string
    {
        return url("/catch/{$this->resource_id}");
    }

    /**
     * Check if the URL has forwarding configured.
     */
    public function hasForwarding(): bool
    {
        return filled($this->forward_to_url);
    }

    /**
     * Check if the URL has email notifications enabled.
     */
    public function hasEmailNotification(): bool
    {
        return $this->notify_email;
    }

    /**
     * Check if the URL has Slack notifications enabled.
     */
    public function hasSlackNotification(): bool
    {
        return $this->notify_slack && filled($this->slack_webhook_url);
    }
}
