<?php

declare(strict_types=1);

use App\Models\Request as RequestModel;
use App\Models\Url;
use App\Models\User;
use App\Notifications\NewRequestNotification;
use Illuminate\Support\Facades\Notification;

describe('Email notification dispatch via HTTP route', function (): void {
    test('it sends email notification for user with email enabled', function (): void {
        Notification::fake();

        $user = User::factory()->create();
        $url = Url::factory()->for($user)->create([
            'notify_email' => true,
        ]);

        $response = $this->post("/catch/{$url->resource_id}", ['test' => 'data']);

        $response->assertStatus(200);
        Notification::assertSentTo($user, NewRequestNotification::class);
    });
});

describe('NewRequestNotification', function (): void {
    test('it uses mail channel', function (): void {
        $user = User::factory()->create();
        $url = Url::factory()->for($user)->create();
        $request = RequestModel::factory()->for($url)->create();

        $notification = new NewRequestNotification($request);

        expect($notification->via($user))->toBe(['mail']);
    });

    test('it generates correct mail subject and intro', function (): void {
        $user = User::factory()->create();
        $url = Url::factory()->for($user)->create(['name' => 'Test Webhook']);
        $request = RequestModel::factory()->for($url)->create([
            'method' => 'POST',
            'path' => '/test',
            'content_type' => 'application/json',
            'content_length' => 42,
            'ip_address' => '192.168.1.1',
        ]);

        $notification = new NewRequestNotification($request);
        $mail = $notification->toMail($user);

        expect($mail->subject)->toBe('[Viewhook] New POST request captured');
        expect($mail->greeting)->toBe('New Webhook Request');
        expect($mail->introLines)->toContain('A new **POST** request was captured on **Test Webhook**.');
    });

    test('it returns correct array representation', function (): void {
        $user = User::factory()->create();
        $url = Url::factory()->for($user)->create();
        $request = RequestModel::factory()->for($url)->create([
            'method' => 'GET',
            'path' => '/api/test',
        ]);

        $notification = new NewRequestNotification($request);
        $array = $notification->toArray($user);

        expect($array)->toHaveKeys(['request_id', 'url_id', 'method', 'path']);
        expect($array['method'])->toBe('GET');
        expect($array['path'])->toBe('/api/test');
    });

    test('it is queueable', function (): void {
        $notification = new NewRequestNotification(
            RequestModel::factory()->make()
        );

        expect($notification)->toBeInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class);
    });
});
