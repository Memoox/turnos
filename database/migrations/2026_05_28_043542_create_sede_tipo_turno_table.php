<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sede_tipo_turno', function (Blueprint $table) {
            $table->id();
            // Llave foránea hacia la sede
            $table->foreignId('sede_id')->constrained('sedes')->onDelete('cascade');
        
            // Llave foránea hacia el tipo de turno (ajusta 'tipo_turnos' al nombre exacto de tu tabla si es diferente)
            $table->foreignId('tipo_turno_id')->constrained('tipo_turnos')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sede_tipo_turno');
    }
};
