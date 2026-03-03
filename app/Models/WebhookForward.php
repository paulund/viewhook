<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasResourceId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $resource_id
 * @property int $request_id
 * @property int $url_id
 * @property string $target_url
 * @property string $method
 * @property int|null $status_code
 * @property string|null $response_body
 * @property int|null $response_time_ms
 * @property string|null $error
 * @property \Carbon\CarbonImmutable $created_at
 * @property \Carbon\CarbonImmutable $updated_at
 * @property-read Request $request
 * @property-read Url $url
 */
final class WebhookForward extends Model
{
    /** @use HasFactory<\Database\Factories\WebhookForwardFactory> */
    use HasFactory;
    use HasResourceId;

    protected $fillable = [
        'resource_id',
        'request_id',
        'url_id',
        'target_url',
        'method',
        'status_code',
        'response_body',
        'response_time_ms',
        'error',
    ];

    /**
     * @return array<string, string>
     */
    #[\Override]
    protected function casts(): array
    {
        return [
            'status_code' => 'integer',
            'response_time_ms' => 'integer',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    /**
     * @return BelongsTo<Request, $this>
     */
    public function request(): BelongsTo
    {
        return $this->belongsTo(Request::class);
    }

    /**
     * @return BelongsTo<Url, $this>
     */
    public function url(): BelongsTo
    {
        return $this->belongsTo(Url::class);
    }

    /**
     * Check if the forward was successful.
     */
    public function isSuccessful(): bool
    {
        return $this->status_code !== null
            && $this->status_code >= 200
            && $this->status_code < 300
            && $this->error === null;
    }

    /**
     * Check if the forward failed.
     */
    public function isFailed(): bool
    {
        return $this->error !== null || ($this->status_code !== null && $this->status_code >= 400);
    }
}
