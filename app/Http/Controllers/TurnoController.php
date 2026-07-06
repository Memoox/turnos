<?php

namespace App\Http\Controllers;

use App\Models\Turno;
use App\Models\User;
use App\Models\TipoTurno;
use App\Models\Sede;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Events\TurnoLlamado;
use App\Events\TurnoGenerado;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;


class TurnoController extends Controller
{
    public function atenderTurno(Request $request)
    {
        $user = Auth::user();

        if (!$user->caja_id) {
            return response()->json([
                'status' => 'error', 
                'message' => 'No tienes una ventanilla asignada'
            ], 400);
        }
        
        DB::beginTransaction();
        try {
            
            $tiposPermitidos = DB::table('caja_tipo_turno')
                ->where('caja_id', $user->caja_id)
                ->pluck('tipo_turno_id');

            if (empty($tiposPermitidos)) {
                return response()->json(['message' => 'Tu ventanilla no tiene turnos configurados'], 400);
            }

            Turno::where('caja_id', $user->caja->id)
                 ->where('status', 1) 
                 ->update([
                     'status' => 2, 
                     'hora_atencion_fin' => now()
                 ]);


            $nuevoTurno = Turno::where('sede_id', $user->sede_id)
                               ->where('status', 0) 
                               ->whereDate('created_at', Carbon::today())
                               ->whereIn('tipo_turno_id', $tiposPermitidos) 
                               ->oldest() 
                               ->lockForUpdate() 
                               ->first();

            if (!$nuevoTurno) {
                DB::commit();
                return response()->json([
                    'status' => 'no-data', 
                    'message' => 'No hay turnos pendientes para tu ventanilla'
                ], 200);
            }

            $nuevoTurno->update([
                'status' => 1, // En atención
                'caja_id' => $user->caja->id,
                'user_id' => $user->id,
                'hora_atencion_inicio' => now(),
            ]);

            DB::commit();

            TurnoLlamado::dispatch($user->sede_id, $nuevoTurno->numero_turno, $user->caja->nombre);
            
            return response()->json([
                'status' => 'ok',
                'message' => 'Turno obtenido con éxito',
                'turno' => $nuevoTurno->numero_turno
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Error al asignar turno',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function generarTurno(Request $request)
    {

        $request->validate([
            'sede_id' => 'required|exists:sedes,id',
            'tipo_turno_id' => 'required|exists:tipo_turnos,id',
        ]);

        

        DB::beginTransaction();
        try {

            $tipo = TipoTurno::find($request->tipo_turno_id);
            $letra = $tipo->clave;
            $hoy = now()->toDateString();

            $ultimoTurno = Turno::where('sede_id', $request->sede_id)
                                ->where('tipo_turno_id', $request->tipo_turno_id)
                                ->whereDate('created_at', $hoy)
                                ->lockForUpdate()
                                ->latest('id')
                                ->first();

 
            $consecutivo = 1;
            if ($ultimoTurno) {
                $partes = explode('-', $ultimoTurno->numero_turno);
                $numeroAnterior = (int) end($partes);
                $consecutivo = $numeroAnterior + 1;
            }

            $numeroTurnoString = $tipo->clave . '-' . str_pad($consecutivo, 4, '0', STR_PAD_LEFT);

            $nuevoTurno = Turno::create([
                'sede_id' => $request->sede_id,
                'tipo_turno_id' => $request->tipo_turno_id,
                'numero_turno' => $numeroTurnoString,
                'status' => 0, 
            ]);

            DB::commit();

            TurnoGenerado::dispatch($request->sede_id);
            

            return response()->json([
                'status' => 'ok',
                'message' => 'Turno generado exitosamente',
                'turno' => $nuevoTurno, 
                'data' => [
                    'descripcion' => $tipo->descripcion,
                    'fecha' => now()->format('d/m/Y H:i:s')
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error interno al generar el turno',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function turnosPendientes($sede_id)
    {
        try {
            $user = Auth::user();

            if ($user && is_null($user->caja_id)) {
                return response()->json([
                    "status" => "ok",
                    "message" => "El usuario no tiene ventanilla asignada.",
                    "data" => [] // Ningún turno pendiente para mostrar
                ], 200);
            }

            $query = Turno::select('tipo_turno_id', DB::raw('count(*) as total'))
                ->where('sede_id', $sede_id)
                ->where('status', 0) 
                ->whereDate('created_at', Carbon::today());

            
                $tiposPermitidos = DB::table('caja_tipo_turno')
                    ->where('caja_id', $user->caja_id)
                    ->pluck('tipo_turno_id')
                    ->toArray();

            if (empty($tiposPermitidos)) {
                return response()->json([
                    "status" => "ok",
                    "message" => "La ventanilla no tiene trámites configurados.",
                    "data" => []
                ], 200);
            }

            $query->whereIn('tipo_turno_id', $tiposPermitidos);
            
            $conteos = $query->groupBy('tipo_turno_id')
                ->pluck('total', 'tipo_turno_id');
               

           
            return response()->json([
                "status" => "ok",
                "message" => "Turnos obtenidos con éxito",
                "data" => $conteos
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                "status" => "error",
                "message" => "Error al obtener los turnos pendientes.",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    public function turnosPantalla($sede_id)
    {
        try {
            $sede = \App\Models\Sede::findOrFail($sede_id);
            $turnos = Turno::with('caja') 
                ->where('sede_id', $sede_id)
                ->where('status', '>=', 1) 
                ->latest('hora_atencion_inicio')
                ->limit(10)
                ->get()
                ->map(function ($turno) {
                    return [
                        'turno' => $turno->numero_turno,
                        'caja'  => $turno->caja ? $turno->caja->nombre : '--'
                    ];
                });

            $turnosPadded = $turnos->pad(10, ['turno' => '--', 'caja' => '--']);

            return response()->json([
                "status"  => $turnos->isEmpty() ? "no-data" : "ok",
                'sede_nombre' => $sede->nombre,
                "turnos"  => $turnosPadded
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                "status" => "error",
                "message" => "Error al actualizar la pantalla.",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    public function tiposTurnoSede($sedeId)
    {
 
        $sede = Sede::with('tiposTurno')->find($sedeId);

        if (!$sede) {
            return response()->json(['status' => 'error', 'message' => 'Sede no encontrada'], 404);
        }

        return response()->json([
            'status' => 'ok',
            'sede' => $sede->nombre,
            'tipos_turno' => $sede->tiposTurno 
        ]);
    }

    public function finalizarTurno(Request $request)
    {
        $user = Auth::user();

        if (!$user->caja_id) {
            return response()->json(['status' => 'error', 'message' => 'No tienes una ventanilla asignada'], 400);
        }

        try {
            $turnoActual = Turno::where('sede_id', $user->sede_id)
                ->where('caja_id', $user->caja_id)
                ->where('status', 1) 
                ->first();

            if ($turnoActual) {
                $turnoActual->status = 2; 
                $turnoActual->hora_atencion_fin = Carbon::now();
                $turnoActual->save();

                $cajaNombre = DB::table('cajas')->where('id', $user->caja_id)->value('nombre');
                TurnoLlamado::dispatch($user->sede_id, $turnoActual->numero_turno, $cajaNombre);
            }

            return response()->json([
                'status' => 'ok',
                'message' => 'Atención finalizada correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al finalizar el turno',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}