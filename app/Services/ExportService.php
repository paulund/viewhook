<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Request;
use App\Models\Url;
use Illuminate\Support\Collection;

final class ExportService
{
    /**
     * Export requests to CSV format.
     *
     * @param  Collection<int, Request>  $requests
     */
    public function toCsv(Collection $requests): string
    {
        $handle = fopen('php://temp', 'r+');
        assert($handle !== false);

        fputcsv($handle, [
            'ID',
            'Method',
            'Path',
            'Content Type',
            'Content Length',
            'IP Address',
            'User Agent',
            'Query Parameters',
            'Headers',
            'Body',
            'Created At',
        ],
            escape: '\\');

        foreach ($requests as $request) {
            fputcsv($handle, [
                $request->resource_id,
                $request->method,
                $request->path,
                $request->content_type ?? '',
                (string) $request->content_length,
                $request->ip_address ?? '',
                $request->user_agent ?? '',
                $this->encodeJson($request->query_params),
                $this->encodeJson($request->headers),
                $request->body ?? '',
                $request->created_at->toIso8601String(),
            ],
                escape: '\\');
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv !== false ? $csv : '';
    }

    /**
     * Export requests to JSON format.
     *
     * @param  Collection<int, Request>  $requests
     */
    public function toJson(Collection $requests): string
    {
        $data = $requests->map(fn (Request $request): array => [
            'id' => $request->resource_id,
            'method' => $request->method,
            'path' => $request->path,
            'content_type' => $request->content_type,
            'content_length' => $request->content_length,
            'ip_address' => $request->ip_address,
            'user_agent' => $request->user_agent,
            'query_params' => $request->query_params,
            'headers' => $request->headers,
            'body' => $request->body,
            'parsed_body' => $request->getParsedBody(),
            'created_at' => $request->created_at->toIso8601String(),
        ])->values()->all();

        $json = json_encode(['requests' => $data, 'exported_at' => now()->toIso8601String()], JSON_PRETTY_PRINT);

        return $json !== false ? $json : '{}';
    }

    /**
     * Generate a filename for the export.
     */
    public function generateFilename(Url $url, string $format): string
    {
        $timestamp = now()->format('Y-m-d_His');
        $slug = preg_replace('/[^a-zA-Z0-9_-]/', '_', $url->name);

        return "requests_{$slug}_{$timestamp}.{$format}";
    }

    /**
     * Encode array to JSON string for CSV.
     *
     * @param  array<string, mixed>|null  $data
     */
    private function encodeJson(?array $data): string
    {
        if ($data === null) {
            return '';
        }

        $json = json_encode($data);

        return $json !== false ? $json : '';
    }
}
