<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sede;
use App\Models\Caja;
use App\Models\TipoTurno;
use App\Models\User;
// use App\Models\Rol;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. LIMPIEZA DE CATÁLOGOS: Tipos de Turno
        $tipoD = TipoTurno::create(['clave' => 'D', 'descripcion' => 'Demanda Nueva', 'status' => true]);
        $tipoE = TipoTurno::create(['clave' => 'E', 'descripcion' => 'Escritos', 'status' => true]);
        $tipoI = TipoTurno::create(['clave' => 'I', 'descripcion' => 'Información', 'status' => true]);

        $rolSuper = \App\Models\Rol::create(['clave' => 'superadmin', 'nombre' => 'Super Administrador']);
        $rolAdmin = \App\Models\Rol::create(['clave' => 'admin', 'nombre' => 'Administrador Local']);
        $rolCajero = \App\Models\Rol::create(['clave' => 'cajero', 'nombre' => 'Cajero de Ventanilla']);

        // 2. CREACIÓN DE SEDES (Para probar el aislamiento del Admin)
        $sedePuebla = Sede::create(['nombre' => 'Casa de Justicia Puebla']);
        $sedeCholula = Sede::create(['nombre' => 'Casa de Justicia Cholula']);

        $sedePuebla->tiposTurno()->attach([1, 2, 3]);
        $sedeCholula->tiposTurno()->attach([3]);

        // 3. CREACIÓN DE CAJAS / VENTANILLAS (Para Sede Puebla)
        $caja1 = Caja::create(['sede_id' => $sedePuebla->id, 'nombre' => 'Ventanilla 1', 'status' => 1]);
        $caja1->tiposDeTurno()->attach([$tipoD->id, $tipoE->id, $tipoI->id]); // Atiende todo

        $caja2 = Caja::create(['sede_id' => $sedePuebla->id, 'nombre' => 'Ventanilla 2', 'status' => 1]);
        $caja2->tiposDeTurno()->attach([$tipoI->id]); // Solo Información

        // 4. SIEMBRA DE USUARIOS (Tus 3 Niveles de Roles)
        
        // Nivel 1: Superadmin (Global - Sin sede ni caja asignada de forma fija)
        User::create([
            'name' => 'Super Admin Global',
            'email' => 'superadmin@test.com',
            'password' => bcrypt('12345678'),
            'rol_id' => $rolSuper->id,
            'sede_id' => null,
            'caja_id' => null,
        ]);

        // Nivel 2: Admin (Local - Solo gobierna la Sede Puebla)
        User::create([
            'name' => 'Admin Sede Puebla',
            'email' => 'admin.puebla@test.com',
            'password' => bcrypt('12345678'),
            'rol_id' => $rolAdmin->id,
            'sede_id' => $sedePuebla->id,
            'caja_id' => null,
        ]);

        // Nivel 3: Cajero (Operativo - Ventanilla 1 en Sede Puebla)
        User::create([
            'name' => 'Memo Cajero',
            'email' => 'cajero@test.com',
            'password' => bcrypt('12345678'),
            'rol_id' => $rolCajero->id,
            'sede_id' => $sedePuebla->id,
            'caja_id' => $caja1->id,
        ]);
    }
}