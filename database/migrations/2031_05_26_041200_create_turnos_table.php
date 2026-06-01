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
        Schema::create('turnos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sede_id')->constrained('sedes');
            $table->foreignId('tipo_turno_id')->constrained('tipo_turnos');
            
            // Nullables porque cuando se crea el turno en el Kiosco, aún no tiene caja ni usuario
            $table->foreignId('caja_id')->nullable()->constrained('cajas');
            $table->foreignId('user_id')->nullable()->constrained('users');
            
            $table->string('numero_turno'); // Ej: 'T0045'
            
            // 0 = En espera, 1 = En atención, 2 = Finalizado, 3 = Cancelado/No llegó
            $table->tinyInteger('status')->default(0); 
            
            // Tiempos para las métricas del Jefe Final
            $table->timestamp('hora_atencion_inicio')->nullable();
            $table->timestamp('hora_atencion_fin')->nullable();
            
            $table->softDeletes();
            $table->timestamps(); // Created_at será nuestra "hora_registro"
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('turnos');
    }
};
