<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Url\CreateUrlAction;
use App\Actions\Url\DeleteUrlAction;
use App\Actions\Url\ListUrlsAction;
use App\Actions\Url\LoadUrlDetailsAction;
use App\Actions\Url\UpdateUrlAction;
use App\Http\Requests\StoreUrlRequest;
use App\Http\Requests\UpdateUrlRequest;
use App\Http\Resources\UrlResource;
use App\Http\Resources\WebhookForwardResource;
use App\Models\Url;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class UrlController extends Controller
{
    public function __construct(
        #[CurrentUser]
        private readonly User $user,
    ) {}

    public function index(ListUrlsAction $listUrls): Response
    {
        $this->authorize('viewAny', Url::class);

        $urls = $listUrls->execute($this->user);

        return Inertia::render('Urls/Index', [
            'urls' => UrlResource::collection($urls)->resolve(),
        ]);
    }

    public function store(
        StoreUrlRequest $request,
        CreateUrlAction $createUrl,
    ): RedirectResponse {
        $url = $createUrl->execute($this->user, $request->validated());

        return to_route('urls.show', $url)
            ->with('success', 'Webhook URL created successfully.');
    }

    public function show(Url $url, LoadUrlDetailsAction $loadUrlDetails): Response
    {
        $this->authorize('view', $url);

        $loadUrlDetails->execute($url);

        return Inertia::render('Urls/Show', [
            'url' => new UrlResource($url)->resolve(),
            'recentForwards' => WebhookForwardResource::collection($url->webhookForwards)->resolve(),
        ]);
    }

    public function update(
        UpdateUrlRequest $request,
        Url $url,
        UpdateUrlAction $updateUrl,
    ): RedirectResponse {
        $this->authorize('update', $url);

        $updateUrl->execute($url, $request->validated());

        return to_route('urls.show', $url)
            ->with('success', 'Webhook URL updated successfully.');
    }

    public function destroy(
        Url $url,
        DeleteUrlAction $deleteUrl,
    ): RedirectResponse {
        $this->authorize('delete', $url);

        $deleteUrl->execute($url);

        return to_route('urls.index')
            ->with('success', 'Webhook URL deleted successfully.');
    }
}
