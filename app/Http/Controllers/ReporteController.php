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
            // Si no nos mandan fechas desde el Vue, tomamos por defecto el día de hoy
            $fechaInicio = $request->query('fecha_inicio', Carbon::today()->toDateString());
            $fechaFin = $request->query('fecha_fin', Carbon::today()->toDateString());
            
            $sede = Sede::findOrFail($sedeId);

            // 1. Obtenemos SOLO los trámites vinculados a esta Sede usando tu relación de Eloquent
            $tiposTurno = $sede->tiposTurno()->orderBy('tipo_turnos.id')->pluck('descripcion', 'tipo_turnos.id')->toArray();

            // Si la sede no tiene trámites asignados, ponemos un valor por defecto para que el Excel no truene
            if (empty($tiposTurno)) {
                $tiposTurno = [0 => 'Sin trámites asignados'];
            }

            // 2. Traemos todos los turnos FINALIZADOS en ese rango de fechas para esa sede
            $turnos = Turno::where('sede_id', $sedeId)
                ->where('status', 2) // 2 = Finalizado
                ->whereNotNull('hora_atencion_inicio')
                ->whereNotNull('hora_atencion_fin')
                ->whereDate('created_at', '>=', $fechaInicio)
                ->whereDate('created_at', '<=', $fechaFin)
                ->get();

            $totalTurnos = $turnos->count();

            // 3. Preparamos las matrices vacías para llenarlas (Horario de 8 AM a 3 PM)
            $matrizTiempos = [];
            $usuariosPorHora = [];
            $tiemposGlobalesPorTipo = []; 

            // Inicializamos el horario base (8 a 14 hrs, que se traduce en filas de "8 a 9", "9 a 10", etc.)
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

            // Inicializamos los acumuladores globales para el "Promedio del día"
            foreach ($tiposTurno as $tipoId => $nombre) {
                $tiemposGlobalesPorTipo[$tipoId] = [
                    'suma_minutos' => 0,
                    'cantidad' => 0
                ];
            }

            // 4. Recorremos los turnos para hacer las matemáticas
            foreach ($turnos as $turno) {
                $inicio = Carbon::parse($turno->hora_atencion_inicio);
                $fin = Carbon::parse($turno->hora_atencion_fin);
                
                // Calculamos la diferencia exacta en segundos y la dividimos entre 60 para tener minutos con decimales
                $minutos = $fin->diffInSeconds($inicio) / 60; 

                $horaDelTurno = $inicio->hour;
                $etiquetaHora = $horaDelTurno . ' a ' . ($horaDelTurno + 1);

                // Si el turno ocurrió dentro de nuestro horario laborable (8 a 15)
                if (isset($matrizTiempos[$etiquetaHora])) {
                    $usuariosPorHora[$etiquetaHora]++; // Contamos a la persona

                    // Sumamos el tiempo a su celda específica (Hora + Tipo de Trámite)
                    $matrizTiempos[$etiquetaHora][$turno->tipo_turno_id]['suma_minutos'] += $minutos;
                    $matrizTiempos[$etiquetaHora][$turno->tipo_turno_id]['cantidad']++;

                    // Sumamos al promedio general del día
                    $tiemposGlobalesPorTipo[$turno->tipo_turno_id]['suma_minutos'] += $minutos;
                    $tiemposGlobalesPorTipo[$turno->tipo_turno_id]['cantidad']++;
                }
            }

            // 5. Formateamos las matrices finales sacando los promedios (Suma / Cantidad)
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

            // 6. Empaquetamos todo y disparamos la descarga
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

            // Generamos un nombre de archivo bonito y único
            // $nombreArchivo = 'Reporte_' . str_replace(' ', '_', $sede->nombre) . '_' . date('Ymd_His') . '.xlsx';
            $nombreBase = 'Reporte_' . str_replace(' ', '_', $sede->nombre) . '_' . date('Ymd_His');
            $formato = $request->query('formato', 'excel');

            if ($formato === 'pdf') {
                $orientacion = count($tiposTurno) > 5 ? 'landscape' : 'portrait';
                
                $pdf = Pdf::loadView('excel.reporte-turnos', ['datos' => $datosProcesados])
                          ->setPaper('a4', $orientacion);
                
                return $pdf->download($nombreBase . '.pdf');
            }
            
            // return Excel::download(new TurnosExport($datosProcesados), $nombreArchivo);
            return Excel::download(new TurnosExport($datosProcesados), $nombreBase . '.xlsx');

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Error al generar el reporte: ' . $e->getMessage()], 500);
        }
    }

    public function obtenerMiSede(Request $request)
    {
        // Obtenemos al usuario autenticado
        $user = $request->user(); 
        
        // Buscamos su sede
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