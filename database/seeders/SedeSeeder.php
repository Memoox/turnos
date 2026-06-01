<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SedeSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('sedes')->insert([
            ['id' => 1, 'nombre' => 'Casa de Justicia Puebla', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'nombre' => 'Casa de Justicia Cholula', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'nombre' => 'Casa de Justicia Huejotzingo', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'nombre' => 'Casa de Justicia Laborales', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
