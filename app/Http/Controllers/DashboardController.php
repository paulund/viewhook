<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\GetDashboardDataAction;
use App\Http\Resources\UrlResource;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Inertia\Inertia;
use Inertia\Response;

final class DashboardController extends Controller
{
    public function __construct(
        #[CurrentUser]
        private readonly User $user,
        private readonly GetDashboardDataAction $getDashboardData,
    ) {}

    public function __invoke(): Response
    {
        $data = $this->getDashboardData->execute($this->user);

        return Inertia::render('Dashboard', [
            'urls' => UrlResource::collection($data->urls)->resolve(),
            'stats' => [
                'total_urls' => $data->totalUrls,
                'total_requests' => $data->totalRequests,
            ],
        ]);
    }
}
