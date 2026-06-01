<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TurnoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;

// --- RUTAS PÚBLICAS ---
// Autenticación
Route::post('/login', [AuthController::class, 'login']);

// Kiosco y TV (Cualquiera puede sacar un turno o ver la pantalla)
Route::post('/turnos/generar', [TurnoController::class, 'generarTurno']);
Route::get('/turnos/pantalla/{sede_id}', [TurnoController::class, 'turnosPantalla']);
Route::get('/turnos/pendientes/{sede_id}', [TurnoController::class, 'turnosPendientes']);

Route::get('/sedes/{sede}/tipos-turno', [TurnoController::class, 'tiposTurnoSede']);


// --- RUTAS PRIVADAS (Solo usuarios logueados) ---
Route::middleware('auth:sanctum')->group(function () {
    
    // Saber quién está logueado
    Route::get('/me', [AuthController::class, 'me']);
    
    // Cerrar sesión
    Route::post('/logout', [AuthController::class, 'logout']);

    
    Route::post('/turnos/atender', [TurnoController::class, 'atenderTurno']);

    Route::get('/dashboard/admin', [DashboardController::class, 'adminStats']);

    Route::get('/dashboard/superadmin', [DashboardController::class, 'superadminStats']);
    
    
});