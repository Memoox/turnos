<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use OwenIt\Auditing\Contracts\Auditable;
// use Illuminate\Database\Eloquent\SoftDeletes;
// class Turno extends Model implements Auditable
class Turno extends Model 
{
    // use \OwenIt\Auditing\Auditable;
    // use SoftDeletes;
    // protected $auditExclude = [
    //     'updated_at',
    // ];

    protected $guarded = [];

    protected $casts = [
        'hora_atencion_inicio' => 'datetime',
        'hora_atencion_fin' => 'datetime',
    ];

    public function sede()
    {
        return $this->belongsTo(Sede::class);
    }

    public function tipoTurno()
    {
        return $this->belongsTo(TipoTurno::class);
    }

    public function caja()
    {
        return $this->belongsTo(Caja::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
