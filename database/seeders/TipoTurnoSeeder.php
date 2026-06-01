<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoTurnoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tipo_turnos')->insert([
            ['id' => 1, 'clave' => 'T', 'descripcion' => 'Escritos con anexos', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'clave' => 'S', 'descripcion' => 'Apelaciones', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'clave' => 'A', 'descripcion' => 'Trabajadores', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'clave' => 'R', 'descripcion' => 'Sin anexos', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'clave' => 'D', 'descripcion' => 'Demanda Nueva', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6, 'clave' => 'J', 'descripcion' => 'Citas Oralidad', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 7, 'clave' => 'E', 'descripcion' => 'Exhortos', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
