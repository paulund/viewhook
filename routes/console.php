<?php

declare(strict_types=1);

use App\Jobs\CleanupExpiredRequestsJob;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your scheduled tasks. The
| Laravel scheduler will automatically run these tasks on the appropriate
| schedule.
|
*/

Schedule::job(CleanupExpiredRequestsJob::class)
    ->hourly()
    ->withoutOverlapping()
    ->onOneServer()
    ->name('cleanup-expired-requests');
