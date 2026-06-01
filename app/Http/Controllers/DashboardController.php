<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Turno;
use App\Models\Caja;
use App\Models\Sede;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function adminStats(Request $request)
    {
        // 1. Obtenemos al administrador y su sede
        $user = $request->user()->load('sede');
        $sedeId = $user->sede_id;
        
        $hoy = Carbon::today();

        // 2. Calculamos los turnos creados HOY en esta sede
        $turnosHoy = Turno::where('sede_id', $sedeId)
                          ->whereDate('created_at', $hoy)
                          ->count();

        // 3. Calculamos cuántos siguen en espera (status = 0)
        $turnosEspera = Turno::where('sede_id', $sedeId)
                             ->where('status', 0)
                             ->whereDate('created_at', $hoy)
                             ->count();

        // 4. Calculamos las ventanillas activas en esta sede
        $ventanillasActivas = Caja::where('sede_id', $sedeId)
                                  ->where('status', 1)
                                  ->count();

        // Enviamos todo empaquetado al frontend
        return response()->json([
            'status' => 'ok',
            'sede_id' => $sedeId,
            'sede_nombre' => $user->sede ? $user->sede->nombre : 'Sede Desconocida',
            'stats' => [
                'turnos_hoy' => $turnosHoy,
                'turnos_espera' => $turnosEspera,
                'ventanillas_activas' => $ventanillasActivas,
            ]
        ]);
    }

    public function superadminStats()
    {
        // Obtenemos todas las sedes registradas y las mapeamos para agregarles datos extra
        $sedes = Sede::all()->map(function ($sede) {
            $hoy = Carbon::today();
            
            // Calculamos cuántos turnos en espera tiene esta sede en específico hoy
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