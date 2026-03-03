<?php

declare(strict_types=1);

use App\Models\Url;
use App\Models\User;

describe('URL Management', function (): void {
    describe('index', function (): void {
        it('displays the urls index page for authenticated users', function (): void {
            $user = User::factory()->create();

            $response = $this->actingAs($user)->get(route('urls.index'));

            $response->assertOk();
            $response->assertInertia(
                fn ($page) => $page
                    ->component('Urls/Index')
                    ->has('urls')
            );
        });

        it('only shows urls owned by the authenticated user', function (): void {
            $user = User::factory()->create();
            $otherUser = User::factory()->create();

            $userUrl = Url::factory()->for($user)->create(['name' => 'My URL']);
            Url::factory()->for($otherUser)->create(['name' => 'Other URL']);

            $response = $this->actingAs($user)->get(route('urls.index'));

            $response->assertOk();
            $response->assertInertia(
                fn ($page) => $page
                    ->component('Urls/Index')
                    ->has('urls', 1)
                    ->where('urls.0.name', 'My URL')
            );
        });

        it('redirects unauthenticated users to login', function (): void {
            $response = $this->get(route('urls.index'));

            $response->assertRedirect(route('login'));
        });
    });

    describe('store', function (): void {
        it('creates a new url for authenticated users', function (): void {
            $user = User::factory()->create();

            $response = $this->actingAs($user)->post(route('urls.store'), [
                'name' => 'My Webhook',
                'description' => 'Test description',
            ]);

            $response->assertRedirect();
            $this->assertDatabaseHas('urls', [
                'user_id' => $user->id,
                'name' => 'My Webhook',
                'description' => 'Test description',
            ]);
        });

        it('generates a unique resource_id on creation', function (): void {
            $user = User::factory()->create();

            $this->actingAs($user)->post(route('urls.store'), [
                'name' => 'My Webhook',
            ]);

            $url = Url::first();
            expect($url->resource_id)->toBeString();
            expect(mb_strlen((string) $url->resource_id))->toBe(36);
        });

        it('validates required name field', function (): void {
            $user = User::factory()->create();

            $response = $this->actingAs($user)->post(route('urls.store'), [
                'name' => '',
            ]);

            $response->assertSessionHasErrors(['name']);
        });

        it('validates name max length', function (): void {
            $user = User::factory()->create();

            $response = $this->actingAs($user)->post(route('urls.store'), [
                'name' => str_repeat('a', 101),
            ]);

            $response->assertSessionHasErrors(['name']);
        });
    });

    describe('show', function (): void {
        it('displays the url detail page for the owner', function (): void {
            $user = User::factory()->create();
            $url = Url::factory()->for($user)->create();

            $response = $this->actingAs($user)->get(route('urls.show', $url));

            $response->assertOk();
            $response->assertInertia(
                fn ($page) => $page
                    ->component('Urls/Show')
                    ->has('url')
                    ->where('url.id', $url->resource_id)
            );
        });

        it('returns 403 for non-owner', function (): void {
            $owner = User::factory()->create();
            $otherUser = User::factory()->create();
            $url = Url::factory()->for($owner)->create();

            $response = $this->actingAs($otherUser)->get(route('urls.show', $url));

            $response->assertForbidden();
        });

        it('uses resource_id for route model binding', function (): void {
            $user = User::factory()->create();
            $url = Url::factory()->for($user)->create();

            $response = $this->actingAs($user)->get('/urls/'.$url->resource_id);

            $response->assertOk();
        });
    });

    describe('update', function (): void {
        it('updates the url for the owner', function (): void {
            $user = User::factory()->create();
            $url = Url::factory()->for($user)->create(['name' => 'Old Name']);

            $response = $this->actingAs($user)->put(route('urls.update', $url), [
                'name' => 'New Name',
            ]);

            $response->assertRedirect();
            expect($url->fresh()->name)->toBe('New Name');
        });

        it('returns 403 for non-owner', function (): void {
            $owner = User::factory()->create();
            $otherUser = User::factory()->create();
            $url = Url::factory()->for($owner)->create();

            $response = $this->actingAs($otherUser)->put(route('urls.update', $url), [
                'name' => 'Hacked',
            ]);

            $response->assertForbidden();
        });
    });

    describe('destroy', function (): void {
        it('deletes the url for the owner', function (): void {
            $user = User::factory()->create();
            $url = Url::factory()->for($user)->create();

            $response = $this->actingAs($user)->delete(route('urls.destroy', $url));

            $response->assertRedirect(route('urls.index'));
            $this->assertDatabaseMissing('urls', ['id' => $url->id]);
        });

        it('returns 403 for non-owner', function (): void {
            $owner = User::factory()->create();
            $otherUser = User::factory()->create();
            $url = Url::factory()->for($owner)->create();

            $response = $this->actingAs($otherUser)->delete(route('urls.destroy', $url));

            $response->assertForbidden();
            $this->assertDatabaseHas('urls', ['id' => $url->id]);
        });
    });
});
