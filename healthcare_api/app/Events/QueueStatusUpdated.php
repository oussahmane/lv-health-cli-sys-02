<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QueueStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public $clinicId, public $queueId, public $status)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new Channel("clinic.{$this->clinicId}.queue"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'queue.status.updated';
    }
}
