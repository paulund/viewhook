<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Request;
use Closure;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

final class CleanupExpiredRequestsJob implements ShouldQueue
{
    use Queueable;

    private const int CHUNK_SIZE = 1000;

    public function handle(): void
    {
        $retentionHours = config('viewhook.retention_hours');

        if ($retentionHours === null) {
            Log::info('Expired requests cleanup skipped: retention is set to forever');

            return;
        }

        $threshold = now()->subHours((int) $retentionHours);

        $deletedCount = $this->deleteInChunks(
            fn () => Request::query()
                ->where('created_at', '<', $threshold)
                ->limit(self::CHUNK_SIZE)
                ->delete()
        );

        Log::info('Expired requests cleanup completed', ['deleted_count' => $deletedCount]);
    }

    /** @param Closure(): int $deleteChunk */
    private function deleteInChunks(Closure $deleteChunk): int
    {
        $totalDeleted = 0;

        do {
            $deleted = $deleteChunk();
            $totalDeleted += $deleted;

            if ($deleted === self::CHUNK_SIZE) {
                \Illuminate\Support\Sleep::usleep(100_000);
            }
        } while ($deleted === self::CHUNK_SIZE);

        return $totalDeleted;
    }
}
