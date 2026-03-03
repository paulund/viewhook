<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Request;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class NewRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Request $request,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = $this->request->url;
        $requestUrl = route('urls.requests.show', [
            'url' => $url->resource_id,
            'request' => $this->request->resource_id,
        ]);

        return (new MailMessage)
            ->subject("[Viewhook] New {$this->request->method} request captured")
            ->greeting('New Webhook Request')
            ->line("A new **{$this->request->method}** request was captured on **{$url->name}**.")
            ->line("**Path:** {$this->request->path}")
            ->line('**Content Type:** '.($this->request->content_type ?? 'N/A'))
            ->line("**Content Length:** {$this->request->content_length} bytes")
            ->line('**IP Address:** '.($this->request->ip_address ?? 'N/A'))
            ->action('View Request', $requestUrl)
            ->line('You received this notification because email notifications are enabled for this webhook URL.')
            ->salutation('— Viewhook');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'request_id' => $this->request->resource_id,
            'url_id' => $this->request->url->resource_id,
            'method' => $this->request->method,
            'path' => $this->request->path,
        ];
    }
}
