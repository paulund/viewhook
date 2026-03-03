<?php

declare(strict_types=1);

namespace App\Events;

use App\Http\Resources\RequestResource;
use App\Models\Request;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class RequestCaptured implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly Request $request,
    ) {}

    /**
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('urls.'.$this->request->url->resource_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'request.captured';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'request' => new RequestResource($this->request)->resolve(),
        ];
    }
}
