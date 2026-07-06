<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;


class TipoTurno extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $guarded = [];

    protected $auditExclude = [
        'updated_at',
    ];

    // Un tipo de turno (ej. Demanda Nueva) puede ser atendido por MUCHAS cajas
    public function cajas()
    {
        return $this->belongsToMany(Caja::class, 'caja_tipo_turno');
    }

    public function sedes()
    {
        return $this->belongsToMany(Sede::class, 'sede_tipo_turno');
    }
}
