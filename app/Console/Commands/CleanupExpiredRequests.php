<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

final class CleanupExpiredRequests extends Command
{
    /**
     * @var string
     */
    protected $signature = 'requests:cleanup
                            {--sync : Run synchronously instead of dispatching to queue}';

    /**
     * @var string
     */
    protected $description = 'Clean up expired requests based on retention policies';

    public function handle(): int
    {
        $this->info('Starting expired requests cleanup...');

        if ($this->option('sync')) {
            dispatch_sync(new \App\Jobs\CleanupExpiredRequestsJob);
            $this->info('Cleanup completed synchronously.');
        } else {
            dispatch(new \App\Jobs\CleanupExpiredRequestsJob);
            $this->info('Cleanup job dispatched to queue.');
        }

        return Command::SUCCESS;
    }
}
