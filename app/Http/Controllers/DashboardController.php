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

        $enEspera = Turno::where('sede_id', $sedeId)
            ->where('status', 0) 
            ->whereDate('created_at', $hoy)
            ->count();

        $atendidosHoy = Turno::where('sede_id', $sedeId)
            ->where('status', 2) 
            ->whereDate('created_at', $hoy)
            ->count();

        $ventanillasActivas = User::where('sede_id', $sedeId)
            ->whereNotNull('caja_id')
            ->count();

        $promediosHoy = Turno::where('sede_id', $sedeId)
            ->whereDate('created_at', $hoy)
            ->whereIn('status', [1, 2]) 
            ->get()
            ->groupBy('tipo_turno_id')
            ->map(function ($turnos) {
                // Sacamos el promedio de minutos que esperó cada turno
                $promedio = $turnos->avg(function ($turno) {
                    return $turno->created_at->diffInMinutes($turno->updated_at);
                });
                return round($promedio); 
            });

        
        $cajas = Caja::where('sede_id', $sedeId)->get();
        
        $cajerosActivos = User::where('sede_id', $sedeId)
            ->whereNotNull('caja_id')
            ->get()
            ->keyBy('caja_id');

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
                'turno_actual' => $turnoActual ? $turnoActual->numero_turno : null 
            ];
        });

       
        $fila = Turno::with('tipoTurno')
            ->where('sede_id', $sedeId)
            ->whereDate('created_at', $hoy)
            ->where('status', 0) 
            ->get()
            ->groupBy('tipo_turno_id')
            ->map(function ($turnos) use ($promediosHoy) {
                
                $tipoId = $turnos->first()->tipo_turno_id;
                $nombreTramite = $turnos->first()->tipoTurno->descripcion ?? 'Desconocido';

                $minutos = $promediosHoy->get($tipoId);

                $textoTiempo = $minutos !== null ? $minutos . ' min' : 'Calculando...';

                return [
                    'id' => $tipoId,
                    'nombre' => $nombreTramite,
                    'cantidad' => $turnos->count(),
                    'tiempoPromedio' => $textoTiempo 
                ];
            })->values();

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