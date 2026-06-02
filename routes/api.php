<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TurnoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminCajeroController;
use App\Http\Controllers\AdminCajaController;

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
    
    // RUTAS PARA LA GESTIÓN DE CAJEROS (Panel Admin)
    Route::get('/admin/cajeros', [AdminCajeroController::class, 'index']);
    Route::post('/admin/cajeros', [AdminCajeroController::class, 'store']);
    Route::put('/admin/cajeros/{id}', [AdminCajeroController::class, 'update']);
    Route::delete('/admin/cajeros/{id}', [AdminCajeroController::class, 'destroy']);

    // RUTAS PARA LA GESTIÓN DE VENTANILLAS (Panel Admin)
    Route::get('/admin/cajas', [AdminCajaController::class, 'index']);
    Route::post('/admin/cajas', [AdminCajaController::class, 'store']);
    Route::put('/admin/cajas/{id}', [AdminCajaController::class, 'update']);
    Route::delete('/admin/cajas/{id}', [AdminCajaController::class, 'destroy']);
});