<?php

use App\Http\Controllers\CaptureController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\UrlController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('Welcome'));

Route::middleware(['auth'])->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('urls', UrlController::class)->except(['create', 'edit']);

    // Request routes (nested under urls)
    Route::get('urls/{url}/requests', [RequestController::class, 'index'])
        ->name('urls.requests.index');
    Route::get('urls/{url}/requests/{request}', [RequestController::class, 'show'])
        ->name('urls.requests.show');
    Route::delete('urls/{url}/requests/{request}', [RequestController::class, 'destroy'])
        ->name('urls.requests.destroy');

    // Export routes
    Route::get('urls/{url}/export', ExportController::class)
        ->name('urls.export');
});

require __DIR__.'/auth.php';

Route::any('/catch/{url}/{path?}', [CaptureController::class, 'capture'])
    ->middleware('url.ratelimit')
    ->where('url', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
    ->where('path', '.*')
    ->name('capture');
