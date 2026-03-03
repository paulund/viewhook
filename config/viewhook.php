<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Hourly request rate limit per URL
    |--------------------------------------------------------------------------
    |
    | Maximum number of requests a single webhook URL can receive per hour.
    |
    */
    'rate_limit' => (int) env('VIEWHOOK_RATE_LIMIT', 100),

    /*
    |--------------------------------------------------------------------------
    | Request retention period (hours)
    |--------------------------------------------------------------------------
    |
    | How long captured requests are kept before being cleaned up.
    | Set to null (empty) to keep requests forever (no automatic cleanup).
    |
    */
    'retention_hours' => env('VIEWHOOK_RETENTION_HOURS') !== null ? (int) env('VIEWHOOK_RETENTION_HOURS') : null,

    /*
    |--------------------------------------------------------------------------
    | Maximum payload size (KB)
    |--------------------------------------------------------------------------
    |
    | Maximum size for incoming webhook payloads in kilobytes.
    | Default: 10240 KB (10 MB).
    |
    */
    'max_payload_kb' => (int) env('VIEWHOOK_MAX_PAYLOAD_KB', 10240),
];
