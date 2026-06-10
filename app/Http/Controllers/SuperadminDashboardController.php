<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sede;
use App\Models\Turno;
use Illuminate\Support\Carbon;

class SuperadminDashboardController extends Controller
{
    public function getMapaGlobal()
    {
        try {
            $hoy = Carbon::today();

            // 1. Traemos solo las sedes activas (las que NO tienen SoftDelete)
            $sedes = Sede::orderBy('nombre', 'asc')->get()->map(function ($sede) use ($hoy) {
                
                // 2. Buscamos todos los turnos de esta sede creados hoy
                $turnosHoy = Turno::where('sede_id', $sede->id)
                    ->whereDate('created_at', $hoy)
                    ->get();

                // 3. Empaquetamos las métricas
                return [
                    'id' => $sede->id,
                    'nombre' => $sede->nombre,
                    'status' => true, // Si llegó hasta aquí, está activa
                    'en_espera' => $turnosHoy->where('status', 0)->count(),
                    'en_ventanilla' => $turnosHoy->where('status', 1)->count(),
                    'finalizados' => $turnosHoy->where('status', 2)->count(),
                ];
            });

            return response()->json([
                'status' => 'ok',
                'sedes' => $sedes
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al cargar el mapa global',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}