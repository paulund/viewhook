<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Request\DeleteRequestAction;
use App\Http\Resources\RequestResource;
use App\Http\Resources\UrlResource;
use App\Models\Request;
use App\Models\Url;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class RequestController extends Controller
{
    public function index(Url $url): Response
    {
        $this->authorize('view', $url);

        $requests = $url->requests()
            ->latest()
            ->paginate(50);

        return Inertia::render('Urls/Requests/Index', [
            'url' => new UrlResource($url)->resolve(),
            'requests' => RequestResource::collection($requests),
        ]);
    }

    public function show(Url $url, Request $request): Response
    {
        $this->authorize('view', $url);

        // Ensure the request belongs to this URL
        abort_unless($request->url_id === $url->id, 404);

        $request->loadCount('webhookForwards');

        return Inertia::render('Urls/Requests/Show', [
            'url' => new UrlResource($url)->resolve(),
            'request' => new RequestResource($request)->resolve(),
        ]);
    }

    public function destroy(
        Url $url,
        Request $request,
        DeleteRequestAction $deleteRequest,
    ): RedirectResponse {
        $this->authorize('delete', $url);

        abort_unless($request->url_id === $url->id, 404);

        $deleteRequest->execute($request);

        return to_route('urls.requests.index', $url)
            ->with('success', 'Request deleted successfully.');
    }
}
