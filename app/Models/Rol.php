<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    protected $table = 'roles';

    protected $fillable = [
        'clave', 
        'nombre',
       
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'rol_id');
    }
}
