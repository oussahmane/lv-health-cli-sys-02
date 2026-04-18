<?php
 
namespace App\Events;
 
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
 
class ClinicSettingsUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
 
    public function __construct(public $clinicId, public $clinicData)
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
        return 'clinic.settings.updated';
    }
}
