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
 * @property int $url_id
 * @property string $method
 * @property string $path
 * @property string|null $content_type
 * @property int $content_length
 * @property array<string, string|array<string>> $headers
 * @property array<string, string|array<string>>|null $query_params
 * @property string|null $body
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Carbon\CarbonImmutable $created_at
 * @property \Carbon\CarbonImmutable $updated_at
 * @property-read Url $url
 * @property-read \Illuminate\Database\Eloquent\Collection<int, WebhookForward> $webhookForwards
 */
final class Request extends Model
{
    /** @use HasFactory<\Database\Factories\RequestFactory> */
    use HasFactory;
    use HasResourceId;

    protected $fillable = [
        'resource_id',
        'url_id',
        'method',
        'path',
        'content_type',
        'content_length',
        'headers',
        'query_params',
        'body',
        'ip_address',
        'user_agent',
    ];

    /**
     * @return array<string, string>
     */
    #[\Override]
    protected function casts(): array
    {
        return [
            'headers' => 'array',
            'query_params' => 'array',
            'content_length' => 'integer',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    /**
     * @return BelongsTo<Url, $this>
     */
    public function url(): BelongsTo
    {
        return $this->belongsTo(Url::class);
    }

    /**
     * @return HasMany<WebhookForward, $this>
     */
    public function webhookForwards(): HasMany
    {
        return $this->hasMany(WebhookForward::class);
    }

    public function isJson(): bool
    {
        if ($this->content_type === null) {
            return false;
        }

        return str_contains($this->content_type, 'application/json');
    }

    public function isFormData(): bool
    {
        if ($this->content_type === null) {
            return false;
        }

        return str_contains($this->content_type, 'application/x-www-form-urlencoded')
            || str_contains($this->content_type, 'multipart/form-data');
    }

    public function isXml(): bool
    {
        if ($this->content_type === null) {
            return false;
        }

        return str_contains($this->content_type, 'application/xml')
            || str_contains($this->content_type, 'text/xml');
    }

    /**
     * Get parsed body as array if JSON, otherwise returns null.
     *
     * @return array<string, mixed>|null
     */
    public function getParsedBody(): ?array
    {
        if (! $this->isJson() || $this->body === null) {
            return null;
        }

        $decoded = json_decode($this->body, true);

        return is_array($decoded) ? $decoded : null;
    }
}
