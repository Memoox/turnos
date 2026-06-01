<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TurnoGenerado implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $sede_id;

    public function __construct($sede_id)
    {
        // Solo necesitamos saber en qué sede se generó el turno
        $this->sede_id = $sede_id;
    }

    
    public function broadcastOn(): array
    {
        // Transmitimos en el canal de pendientes de esa sede específica
        return [
            new Channel('sede.' . $this->sede_id . '.pendientes'),
        ];
    }
}
