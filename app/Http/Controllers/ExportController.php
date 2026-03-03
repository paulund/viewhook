<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ExportUrlRequest;
use App\Models\Url;
use App\Services\ExportService;
use Illuminate\Http\Response;

final class ExportController extends Controller
{
    public function __construct(
        private readonly ExportService $exportService,
    ) {}

    /**
     * Export requests for a URL.
     */
    public function __invoke(ExportUrlRequest $request, Url $url): Response
    {
        $format = $request->getExportFormat();

        // Get all requests for this URL (up to 10,000 for performance)
        $requests = $url->requests()->latest()
            ->limit(10000)
            ->get();

        if ($format === 'json') {
            $content = $this->exportService->toJson($requests);
            $contentType = 'application/json';
        } else {
            $content = $this->exportService->toCsv($requests);
            $contentType = 'text/csv';
        }

        $filename = $this->exportService->generateFilename($url, $format);

        return response($content, 200, [
            'Content-Type' => $contentType,
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }
}
