<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait HasResourceId
{
    public static function bootHasResourceId(): void
    {
        static::creating(function ($model): void {
            if (empty($model->resource_id)) {
                $model->resource_id = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'resource_id';
    }
}
