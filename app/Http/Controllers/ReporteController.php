<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\TurnosExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Turno;
use App\Models\Sede;
use App\Models\TipoTurno;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ReporteController extends Controller
{
    public function descargarReporteExcel(Request $request)
    {
        try {
            $sedeId = $request->query('sede_id');
            $fechaInicio = $request->query('fecha_inicio', Carbon::today()->toDateString());
            $fechaFin = $request->query('fecha_fin', Carbon::today()->toDateString());
            
            $sede = Sede::findOrFail($sedeId);

            $tiposTurno = $sede->tiposTurno()->orderBy('tipo_turnos.id')->pluck('descripcion', 'tipo_turnos.id')->toArray();

            if (empty($tiposTurno)) {
                $tiposTurno = [0 => 'Sin trámites asignados'];
            }

            $turnos = Turno::where('sede_id', $sedeId)
                ->where('status', 2) 
                ->whereNotNull('hora_atencion_inicio')
                ->whereNotNull('hora_atencion_fin')
                ->whereDate('created_at', '>=', $fechaInicio)
                ->whereDate('created_at', '<=', $fechaFin)
                ->get();

            $totalTurnos = $turnos->count();

            $matrizTiempos = [];
            $usuariosPorHora = [];
            $tiemposGlobalesPorTipo = []; 

            for ($h = 8; $h <= 14; $h++) {
                $etiquetaHora = $h . ' a ' . ($h + 1);
                $usuariosPorHora[$etiquetaHora] = 0;
                $matrizTiempos[$etiquetaHora] = [];

                foreach ($tiposTurno as $tipoId => $nombre) {
                    $matrizTiempos[$etiquetaHora][$tipoId] = [
                        'suma_minutos' => 0,
                        'cantidad' => 0
                    ];
                }
            }

            foreach ($tiposTurno as $tipoId => $nombre) {
                $tiemposGlobalesPorTipo[$tipoId] = [
                    'suma_minutos' => 0,
                    'cantidad' => 0
                ];
            }

            foreach ($turnos as $turno) {
                $inicio = Carbon::parse($turno->hora_atencion_inicio);
                $fin = Carbon::parse($turno->hora_atencion_fin);

                $minutos = $fin->diffInSeconds($inicio) / 60; 

                $horaDelTurno = $inicio->hour;
                $etiquetaHora = $horaDelTurno . ' a ' . ($horaDelTurno + 1);

                if (isset($matrizTiempos[$etiquetaHora])) {
                    $usuariosPorHora[$etiquetaHora]++; 

                    $matrizTiempos[$etiquetaHora][$turno->tipo_turno_id]['suma_minutos'] += $minutos;
                    $matrizTiempos[$etiquetaHora][$turno->tipo_turno_id]['cantidad']++;

                    $tiemposGlobalesPorTipo[$turno->tipo_turno_id]['suma_minutos'] += $minutos;
                    $tiemposGlobalesPorTipo[$turno->tipo_turno_id]['cantidad']++;
                }
            }

            $matrizFinal = [];
            foreach ($matrizTiempos as $hora => $tipos) {
                $matrizFinal[$hora] = [];
                foreach ($tipos as $tipoId => $data) {
                    if ($data['cantidad'] > 0) {
                        $promedio = $data['suma_minutos'] / $data['cantidad'];
                        $matrizFinal[$hora][$tipoId] = number_format($promedio, 2) . ' min';
                    } else {
                        $matrizFinal[$hora][$tipoId] = '0.00 min';
                    }
                }
            }

            $promediosGenerales = [];
            foreach ($tiemposGlobalesPorTipo as $tipoId => $data) {
                if ($data['cantidad'] > 0) {
                    $promedio = $data['suma_minutos'] / $data['cantidad'];
                    $promediosGenerales[$tipoId] = number_format($promedio, 2) . ' min';
                } else {
                    $promediosGenerales[$tipoId] = '0.00 min';
                }
            }

            $datosProcesados = [
                'sede_nombre' => $sede->nombre,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'total_turnos' => $totalTurnos,
                'tipos_turno' => $tiposTurno,
                'matriz_tiempos' => $matrizFinal,
                'promedios_generales' => $promediosGenerales,
                'usuarios_por_hora' => $usuariosPorHora,
                'total_atendidos' => array_sum($usuariosPorHora)
            ];


            $nombreBase = 'Reporte_' . str_replace(' ', '_', $sede->nombre) . '_' . date('Ymd_His');
            $formato = $request->query('formato', 'excel');

            if ($formato === 'pdf') {
                $orientacion = count($tiposTurno) > 5 ? 'landscape' : 'portrait';
                
                $pdf = Pdf::loadView('excel.reporte-turnos', ['datos' => $datosProcesados])
                          ->setPaper('a4', $orientacion);
                
                return $pdf->download($nombreBase . '.pdf');
            }
 
            return Excel::download(new TurnosExport($datosProcesados), $nombreBase . '.xlsx');

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Error al generar el reporte: ' . $e->getMessage()], 500);
        }
    }

    public function obtenerMiSede(Request $request)
    {
    
        $user = $request->user(); 

        $sede = \App\Models\Sede::find($user->sede_id); 

        if (!$sede) {
            return response()->json([
                'status' => 'error', 
                'message' => 'El usuario no tiene una sede asignada'
            ], 404);
        }

        return response()->json([
            'status' => 'ok',
            'sede_id' => $sede->id,
            'sede_nombre' => $sede->nombre
        ]);
    }
}