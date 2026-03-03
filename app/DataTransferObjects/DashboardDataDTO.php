<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

use App\Models\Url;
use Illuminate\Database\Eloquent\Collection;

final readonly class DashboardDataDTO
{
    /**
     * @param  Collection<int, Url>  $urls
     */
    public function __construct(
        public Collection $urls,
        public int $totalUrls,
        public int $totalRequests,
    ) {}
}
