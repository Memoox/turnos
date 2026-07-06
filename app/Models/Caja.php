<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use OwenIt\Auditing\Contracts\Auditable;


class Caja extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;
    
    protected $guarded = [];

    protected $auditExclude = [
        'updated_at',
    ];
    
    // Una caja pertenece a una sede
    public function sede(): BelongsTo
    {
        return $this->belongsTo(Sede::class);
    }

    // LA MAGIA: Una caja soporta MUCHOS tipos de turno a través de la tabla pivote
    public function tiposDeTurno(): BelongsToMany
    {
        return $this->belongsToMany(TipoTurno::class, 'caja_tipo_turno', 'caja_id', 'tipo_turno_id');
    }
}
