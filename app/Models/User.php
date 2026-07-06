<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use OwenIt\Auditing\Contracts\Auditable;

#[Fillable(['name', 'email', 'password','sede_id','caja_id','rol_id',])]
#[Hidden(['password', 'remember_token'])]

class User extends Authenticatable implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $auditExclude = [
        'updated_at',
    ];
    
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function sede()
    {
        return $this->belongsTo(Sede::class);
    }

    // La ventanilla en la que está logueado actualmente
    public function caja()
    {
        return $this->belongsTo(Caja::class);
    }
}
