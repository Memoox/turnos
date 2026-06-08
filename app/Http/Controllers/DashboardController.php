<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Turno;
use App\Models\Caja;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function adminStats(Request $request)
    {
        $user = $request->user()->load('sede');
        $sedeId = $user->sede_id;
        $hoy = Carbon::today();

        // ==========================================
        // 1. KPIs (RESUMEN SUPERIOR)
        // ==========================================
        // Usamos tu lógica: status = 0 es "En espera"
        $enEspera = Turno::where('sede_id', $sedeId)
            ->where('status', 0) 
            ->whereDate('created_at', $hoy)
            ->count();

        // Asumo que status = 2 es "Atendido" (Cámbialo si usas otro número)
        $atendidosHoy = Turno::where('sede_id', $sedeId)
            ->where('status', 2) 
            ->whereDate('created_at', $hoy)
            ->count();

        $ventanillasActivas = User::where('sede_id', $sedeId)
            ->whereNotNull('caja_id')
            ->count();

        $promediosHoy = Turno::where('sede_id', $sedeId)
            ->whereDate('created_at', $hoy)
            ->whereIn('status', [1, 2]) // Los que ya fueron llamados o terminaron
            ->get()
            ->groupBy('tipo_turno_id')
            ->map(function ($turnos) {
                // Sacamos el promedio de minutos que esperó cada turno
                $promedio = $turnos->avg(function ($turno) {
                    // diffInMinutes es una función nativa mágica de Carbon
                    return $turno->created_at->diffInMinutes($turno->updated_at);
                });
                return round($promedio); // Redondeamos para evitar decimales (ej. 5.33 min)
            });

        // ==========================================
        // 2. CUADRÍCULA DE VENTANILLAS
        // ==========================================
        $cajas = Caja::where('sede_id', $sedeId)->get();
        
        $cajerosActivos = User::where('sede_id', $sedeId)
            ->whereNotNull('caja_id')
            ->get()
            ->keyBy('caja_id');

        // Asumo que status = 1 es "En Atención" (Llamando en ventanilla)
        $turnosEnAtencion = Turno::where('sede_id', $sedeId)
            ->whereDate('created_at', $hoy)
            ->where('status', 1) 
            ->get()
            ->keyBy('caja_id');

        $cajasMapeadas = $cajas->map(function($caja) use ($cajerosActivos, $turnosEnAtencion) {
            $cajero = $cajerosActivos->get($caja->id);
            $turnoActual = $turnosEnAtencion->get($caja->id);

            return [
                'id' => $caja->id,
                'nombre' => $caja->nombre,
                'cajero' => $cajero ? ['name' => $cajero->name] : null,
                // Ajusta 'folio' al nombre real de la columna de tu BD (ej. clave_turno)
                'turno_actual' => $turnoActual ? $turnoActual->numero_turno : null 
            ];
        });

        // ==========================================
        // 3. LA FILA POR TRÁMITE (AGRUPACIÓN)
        // ==========================================
        $fila = Turno::with('tipoTurno')
            ->where('sede_id', $sedeId)
            ->whereDate('created_at', $hoy)
            ->where('status', 0) // Solo los que están en espera
            ->get()
            ->groupBy('tipo_turno_id')
            ->map(function ($turnos) use ($promediosHoy) {
                
                $tipoId = $turnos->first()->tipo_turno_id;
                $nombreTramite = $turnos->first()->tipoTurno->descripcion ?? 'Desconocido';

                // Buscamos si ya tenemos un promedio calculado para este trámite hoy
                $minutos = $promediosHoy->get($tipoId);
                
                // Si nadie ha pasado hoy a este trámite, mostramos "Calculando..."
                $textoTiempo = $minutos !== null ? $minutos . ' min' : 'Calculando...';

                return [
                    'id' => $tipoId,
                    'nombre' => $nombreTramite,
                    'cantidad' => $turnos->count(),
                    'tiempoPromedio' => $textoTiempo 
                ];
            })->values();

        // Enviamos todo con la estructura que Vue espera
        return response()->json([
            'status' => 'ok',
            'sede_id' => $sedeId,
            'sede_nombre' => $user->sede ? $user->sede->nombre : 'Sede Desconocida',
            'resumen' => [
                'enEspera' => $enEspera,
                'atendidosHoy' => $atendidosHoy,
                'ventanillasActivas' => $ventanillasActivas
            ],
            'cajas' => $cajasMapeadas,
            'fila' => $fila
        ]);
    }

    public function superadminStats()
    {
        // ... (Tu función de superadmin queda exactamente igual) ...
        $sedes = Sede::all()->map(function ($sede) {
            $hoy = Carbon::today();
            $turnosEspera = Turno::where('sede_id', $sede->id)
                                 ->where('status', 0)
                                 ->whereDate('created_at', $hoy)
                                 ->count();
            return [
                'id' => $sede->id,
                'nombre' => $sede->nombre,
                'status' => $sede->status,
                'turnos_espera' => $turnosEspera
            ];
        });

        return response()->json([
            'status' => 'ok',
            'sedes' => $sedes
        ]);
    }
}