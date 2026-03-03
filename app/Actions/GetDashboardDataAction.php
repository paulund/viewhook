<?php

declare(strict_types=1);

namespace App\Actions;

use App\DataTransferObjects\DashboardDataDTO;
use App\Models\Request;
use App\Models\User;

final readonly class GetDashboardDataAction
{
    public function execute(User $user): DashboardDataDTO
    {
        $urls = $user->urls()
            ->withCount('requests')
            ->latest()
            ->limit(5)
            ->get();

        $totalUrls = $user->urls()->count();
        $totalRequests = (int) Request::whereIn('url_id', $user->urls()->select('id'))->count();

        return new DashboardDataDTO(
            urls: $urls,
            totalUrls: $totalUrls,
            totalRequests: $totalRequests,
        );
    }
}
