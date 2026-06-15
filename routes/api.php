<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TurnoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminCajeroController;
use App\Http\Controllers\AdminCajaController;
use App\Http\Controllers\SuperadminSedeController;
use App\Http\Controllers\SuperadminTipoTurnoController;
use App\Http\Controllers\SuperadminUserController;
use App\Http\Controllers\SuperadminDashboardController;
use App\Http\Controllers\ReporteController;

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
    Route::post('/turnos/finalizar', [TurnoController::class, 'finalizarTurno']);
    
    // RUTAS PARA LA GESTIÓN DE CAJEROS (Panel Admin)
    Route::get('/admin/cajeros', [AdminCajeroController::class, 'index']);
    Route::post('/admin/cajeros', [AdminCajeroController::class, 'store']);
    Route::put('/admin/cajeros/{id}', [AdminCajeroController::class, 'update']);
    Route::delete('/admin/cajeros/{id}', [AdminCajeroController::class, 'destroy']);
    Route::patch('/admin/cajeros/{id}/toggle', [AdminCajeroController::class, 'toggleEstado']);

    // RUTAS PARA LA GESTIÓN DE VENTANILLAS (Panel Admin)
    Route::get('/admin/cajas', [AdminCajaController::class, 'index']);
    Route::post('/admin/cajas', [AdminCajaController::class, 'store']);
    Route::put('/admin/cajas/{id}', [AdminCajaController::class, 'update']);
    Route::delete('/admin/cajas/{id}', [AdminCajaController::class, 'destroy']);
    Route::patch('/admin/cajas/{id}/toggle', [AdminCajaController::class, 'toggleEstado']);

    Route::get('/reportes/descargar', [ReporteController::class, 'descargarReporteExcel']);

    // RUTAS DEL SÚPER ADMINISTRADOR
    Route::prefix('superadmin')->group(function () {
    
        // Gestión de Sedes
        Route::get('/sedes', [SuperadminSedeController::class, 'index']);
        Route::post('/sedes', [SuperadminSedeController::class, 'store']);
        Route::put('/sedes/{id}', [SuperadminSedeController::class, 'update']);
        Route::put('/sedes/{id}/toggle', [SuperadminSedeController::class, 'toggleStatus']);

        // Gestión de Trámites (Tipos de Turno)
        Route::get('/tramites', [SuperadminTipoTurnoController::class, 'index']);
        Route::post('/tramites', [SuperadminTipoTurnoController::class, 'store']);
        Route::put('/tramites/{id}', [SuperadminTipoTurnoController::class, 'update']);
        Route::put('/tramites/{id}/toggle', [SuperadminTipoTurnoController::class, 'toggleStatus']);

        // Gestión de Usuarios (Administradores y Cajeros)
        Route::get('/usuarios', [SuperadminUserController::class, 'index']);
        Route::post('/usuarios', [SuperadminUserController::class, 'store']);
        Route::put('/usuarios/{id}', [SuperadminUserController::class, 'update']);
        Route::put('/usuarios/{id}/toggle', [SuperadminUserController::class, 'toggleStatus']);

        Route::get('/dashboard', [SuperadminDashboardController::class, 'getMapaGlobal']);
        Route::delete('/sedes/{id}/force', [SuperadminSedeController::class, 'forceDelete']);
        Route::delete('/tramites/{id}/force', [SuperadminTipoTurnoController::class, 'forceDelete']);
        Route::delete('/usuarios/{id}/force', [SuperadminUserController::class, 'forceDelete']);

        Route::get('/sedes-lista', [SuperadminSedeController::class, 'listaDesplegable']);
    
    });
});