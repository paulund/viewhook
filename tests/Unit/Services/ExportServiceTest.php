<?php

declare(strict_types=1);

use App\Models\Request as RequestModel;
use App\Models\Url;
use App\Services\ExportService;
use Carbon\CarbonImmutable;

uses(Tests\TestCase::class);

/**
 * Build a Request model instance without touching the database.
 *
 * @param  array<string, mixed>  $overrides
 */
function makeRequestInstance(array $overrides = []): RequestModel
{
    $instance = new RequestModel(array_merge([
        'resource_id' => '550e8400-e29b-41d4-a716-446655440000',
        'method' => 'POST',
        'path' => '/',
        'content_type' => 'application/json',
        'content_length' => 42,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'TestAgent/1.0',
        'query_params' => null,
        'headers' => ['Content-Type' => 'application/json'],
        'body' => '{"test":true}',
    ], $overrides));

    $instance->created_at = CarbonImmutable::parse('2025-01-01 12:00:00');

    return $instance;
}

describe('ExportService', function (): void {
    test('toCsv generates valid CSV with headers', function (): void {
        $requests = collect([
            makeRequestInstance(['resource_id' => 'uuid-1']),
            makeRequestInstance(['resource_id' => 'uuid-2']),
            makeRequestInstance(['resource_id' => 'uuid-3']),
        ]);

        $service = new ExportService;
        $csv = $service->toCsv($requests);

        expect($csv)->toContain('ID');
        expect($csv)->toContain('Method');
        expect($csv)->toContain('Path');
        expect($csv)->toContain('Content Type');
        expect(substr_count($csv, "\n"))->toBeGreaterThan(3);
    });

    test('toCsv handles empty collection', function (): void {
        $service = new ExportService;
        $csv = $service->toCsv(collect([]));

        expect($csv)->toContain('ID');
        expect(substr_count($csv, "\n"))->toBe(1);
    });

    test('toJson generates valid JSON with metadata', function (): void {
        $requests = collect([
            makeRequestInstance(['resource_id' => 'uuid-a', 'method' => 'GET']),
            makeRequestInstance(['resource_id' => 'uuid-b', 'method' => 'POST']),
        ]);

        $service = new ExportService;
        $json = $service->toJson($requests);

        $data = json_decode($json, true);

        expect($data)->toHaveKey('requests');
        expect($data)->toHaveKey('exported_at');
        expect($data['requests'])->toHaveCount(2);
        expect($data['requests'][0])->toHaveKeys(['id', 'method', 'path', 'created_at']);
    });

    test('toJson handles empty collection', function (): void {
        $service = new ExportService;
        $json = $service->toJson(collect([]));

        $data = json_decode($json, true);

        expect($data)->toHaveKey('requests');
        expect($data['requests'])->toBeEmpty();
    });

    test('generateFilename creates valid filename matching pattern', function (): void {
        $url = new Url(['name' => 'My Test Webhook']);

        $service = new ExportService;
        $filename = $service->generateFilename($url, 'csv');

        expect($filename)->toContain('requests_');
        expect($filename)->toContain('.csv');
        expect($filename)->toMatch('/^requests_[a-zA-Z0-9_-]+_\d{4}-\d{2}-\d{2}_\d{6}\.csv$/');
    });

    test('generateFilename sanitises special characters', function (): void {
        $url = new Url(['name' => 'Test @ Webhook #1']);

        $service = new ExportService;
        $filename = $service->generateFilename($url, 'json');

        expect($filename)->not->toContain('@');
        expect($filename)->not->toContain('#');
        expect($filename)->toContain('.json');
    });
});
