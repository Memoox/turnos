<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class Caja extends Model
{
    use SoftDeletes;
    
    protected $guarded = [];

    // Una caja pertenece a una sede
    public function sede(): BelongsTo
    {
        return $this->belongsTo(Sede::class);
    }

    // LA MAGIA: Una caja soporta MUCHOS tipos de turno a través de la tabla pivote
    public function tiposDeTurno(): BelongsToMany
    {
        return $this->belongsToMany(TipoTurno::class, 'caja_tipo_turno');
    }
}
