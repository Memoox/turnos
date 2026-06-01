<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'id' => 1,
                'name' => 'Memo Admin',
                'email' => 'admin@turnos.test',
                'username' => 'memo_admin',
                'password' => Hash::make('password123'), // Contraseña por defecto
                'rol_id' => 1, // Administrador
                'sede_id' => 1, // Puebla
                'caja_id' => null, // El admin no atiende ventanilla
                'created_at' => now(), 
                'updated_at' => now()
            ]
        ]);
    }
}
