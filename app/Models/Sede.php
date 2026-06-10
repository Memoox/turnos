<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sede extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombre',
    ];

    public function tiposTurno()
    {
        
        return $this->belongsToMany(TipoTurno::class, 'sede_tipo_turno', 'sede_id', 'tipo_turno_id');
    }

    public function cajas()
    {
        return $this->hasMany(Caja::class);
    }

    // Ejemplo: Una sede tiene muchos turnos
    public function turnos()
    {
        return $this->hasMany(Turno::class);
    }
}
