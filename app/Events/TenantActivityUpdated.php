<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TenantActivityUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public string $communityId)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('community.'.$this->communityId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'TenantActivityUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'type' => 'activity_updated',
        ];
    }
}
