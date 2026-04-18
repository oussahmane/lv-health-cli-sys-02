<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QueuePrioritySet implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public $clinicId, public $queueId, public $priority, public $priorityType = 'regular')
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
        return 'queue.priority.set';
    }
}
