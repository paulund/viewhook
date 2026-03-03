<?php

declare(strict_types=1);

use App\Models\Request;
use App\Models\Url;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->url = Url::factory()->for($this->user)->create();
    $this->request = Request::factory()->for($this->url)->create();
});

describe('request list page', function (): void {
    it('displays requests for authenticated user', function (): void {
        $response = $this->actingAs($this->user)
            ->get(route('urls.requests.index', $this->url));

        $response->assertOk();
        $response->assertInertia(
            fn (Assert $page): \Inertia\Testing\AssertableInertia => $page
                ->component('Urls/Requests/Index')
                ->has('url')
                ->has('requests.data', 1)
        );
    });

    it('paginates requests at 50 per page', function (): void {
        Request::factory(60)->for($this->url)->create();

        $response = $this->actingAs($this->user)
            ->get(route('urls.requests.index', $this->url));

        $response->assertInertia(
            fn (Assert $page): \Inertia\Testing\AssertableInertia => $page
                ->has('requests.data', 50)
                ->where('requests.meta.last_page', 2)
        );
    });

    it('shows newest requests first', function (): void {
        $oldRequest = Request::factory()->for($this->url)->create([
            'created_at' => now()->subHour(),
        ]);
        $newRequest = Request::factory()->for($this->url)->create([
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('urls.requests.index', $this->url));

        $response->assertInertia(
            fn (Assert $page): \Inertia\Testing\AssertableInertia => $page
                ->where('requests.data.0.id', $newRequest->resource_id)
        );
    });

    it('denies access to other users urls', function (): void {
        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)
            ->get(route('urls.requests.index', $this->url));

        $response->assertForbidden();
    });

    it('requires authentication', function (): void {
        $response = $this->get(route('urls.requests.index', $this->url));

        $response->assertRedirect(route('login'));
    });
});

describe('request detail page', function (): void {
    it('displays request details', function (): void {
        $response = $this->actingAs($this->user)
            ->get(route('urls.requests.show', [$this->url, $this->request]));

        $response->assertOk();
        $response->assertInertia(
            fn (Assert $page): \Inertia\Testing\AssertableInertia => $page
                ->component('Urls/Requests/Show')
                ->has('url')
                ->has('request')
                ->where('request.id', $this->request->resource_id)
        );
    });

    it('returns 404 for request belonging to different url', function (): void {
        $otherUrl = Url::factory()->for($this->user)->create();
        $otherRequest = Request::factory()->for($otherUrl)->create();

        $response = $this->actingAs($this->user)
            ->get(route('urls.requests.show', [$this->url, $otherRequest]));

        $response->assertNotFound();
    });
});

describe('request deletion', function (): void {
    it('deletes a request', function (): void {
        $response = $this->actingAs($this->user)
            ->delete(route('urls.requests.destroy', [$this->url, $this->request]));

        $response->assertRedirect(route('urls.requests.index', $this->url));

        $this->assertDatabaseMissing('requests', [
            'id' => $this->request->id,
        ]);
    });

    it('denies deletion to other users', function (): void {
        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)
            ->delete(route('urls.requests.destroy', [$this->url, $this->request]));

        $response->assertForbidden();

        $this->assertDatabaseHas('requests', [
            'id' => $this->request->id,
        ]);
    });
});
