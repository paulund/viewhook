<?php

declare(strict_types=1);

use App\Models\Request as RequestModel;
use App\Models\Url;
use App\Models\User;
use App\Models\WebhookForward;

describe('Url Show', function (): void {
    test('show page returns recentForwards in Inertia props', function (): void {
        $user = User::factory()->create();
        $url = Url::factory()->for($user)->create();
        $request = RequestModel::factory()->for($url)->create();

        WebhookForward::factory()->for($url)->for($request)->count(3)->create();

        $response = $this->actingAs($user)->get(route('urls.show', $url));

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page
                ->component('Urls/Show')
                ->has('recentForwards', 3)
        );
    });

    test('show page masks slack webhook url', function (): void {
        $user = User::factory()->create();
        $url = Url::factory()->for($user)->create([
            'notify_slack' => true,
            'slack_webhook_url' => 'https://hooks.slack.com/services/T00000/B00000/XXXXXXXXXXXX',
        ]);

        $response = $this->actingAs($user)->get(route('urls.show', $url));

        $response->assertOk();
        $response->assertInertia(
            fn ($page) => $page->has('url.slack_webhook_url')
        );
    });

    test('show page returns correct forward shape', function (): void {
        $user = User::factory()->create();
        $url = Url::factory()->for($user)->create();
        $request = RequestModel::factory()->for($url)->create();

        WebhookForward::factory()->for($url)->for($request)->create();

        $response = $this->actingAs($user)->get(route('urls.show', $url));

        $response->assertStatus(200);
        $response->assertInertia(
            fn ($page) => $page
                ->has(
                    'recentForwards.0',
                    fn ($forward) => $forward
                        ->has('id')
                        ->has('status_code')
                        ->has('response_time_ms')
                        ->has('error')
                        ->has('is_successful')
                        ->has('is_failed')
                        ->has('created_at')
                )
        );
    });

});
