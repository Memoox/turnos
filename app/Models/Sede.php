<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sede extends Model
{
    public function tiposTurno()
    {
        
        return $this->belongsToMany(TipoTurno::class, 'sede_tipo_turno', 'sede_id', 'tipo_turno_id');
    }
}
