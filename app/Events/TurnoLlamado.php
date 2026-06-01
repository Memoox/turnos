<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TurnoLlamado implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $sede_id;
    public $turno;
    public $caja;

    public function __construct($sede_id, $turno, $caja)
    {
        $this->sede_id = $sede_id;
        $this->turno = $turno;
        $this->caja = $caja;
    }

    // Le decimos en qué canal de la sede debe gritar este evento
    public function broadcastOn(): array
    {
        // Ej: sede.1.pantalla
        return [
            new Channel('sede.' . $this->sede_id . '.pantalla'),
        ];
    }
}
