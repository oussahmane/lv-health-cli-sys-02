<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QueuePatientJoined implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public $clinicId, public $patientData)
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
        return 'queue.patient.joined';
    }
}
