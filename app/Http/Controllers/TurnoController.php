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
        // Validamos que la petición traiga el ID del usuario
        // $request->validate(['user_id' => 'required|exists:users,id']);

        DB::beginTransaction();
        try {
            // 1. CARGA INTELIGENTE (Eager Loading)
            // Traemos al usuario, con su caja, y con los tipos de turno que esa caja permite
            // $user = User::with('caja.tiposDeTurno')->findOrFail($request->user_id);

            // Si el usuario no está asignado a ninguna ventanilla, cortamos
            // if (!$user->caja) {
            //     return response()->json(['message' => 'No tienes una ventanilla asignada'], 400);
            // }

            // MAGIA: Sacamos el arreglo limpio de permisos (Ej: [1, 2, 5]) 
            // ¡Adiós a los códigos concatenados como "1235"!
            // $tiposPermitidos = $user->caja->tiposDeTurno->pluck('id')->toArray();

            $tiposPermitidos = DB::table('caja_tipo_turno')
                ->where('caja_id', $user->caja_id)
                ->pluck('tipo_turno_id');

            if (empty($tiposPermitidos)) {
                return response()->json(['message' => 'Tu ventanilla no tiene turnos configurados'], 400);
            }

            // 2. CERRAR EL TURNO ANTERIOR
            // Buscamos cualquier turno de esta caja que siga "En atención" (status = 1)
            Turno::where('caja_id', $user->caja->id)
                 ->where('status', 1) 
                 ->update([
                     'status' => 2, // Finalizado
                     'hora_atencion_fin' => now()
                 ]);

            // 3. LA BÚSQUEDA DEL SIGUIENTE TURNO (Con bloqueo anti-colisiones)
            $nuevoTurno = Turno::where('sede_id', $user->sede_id)
                               ->where('status', 0) // Que esté pendiente
                               ->whereDate('created_at', Carbon::today())
                               ->whereIn('tipo_turno_id', $tiposPermitidos) // Que la caja pueda atenderlo
                               ->oldest() // Laravel usa created_at automáticamente para FIFO
                               ->lockForUpdate() // Evita que otro cajero lo robe al mismo tiempo
                               ->first();

            if (!$nuevoTurno) {
                DB::commit();
                return response()->json([
                    'status' => 'no-data', 
                    'message' => 'No hay turnos pendientes para tu ventanilla'
                ], 200);
            }

            // 4. ASIGNAR EL NUEVO TURNO
            $nuevoTurno->update([
                'status' => 1, // En atención
                'caja_id' => $user->caja->id,
                'user_id' => $user->id,
                'hora_atencion_inicio' => now(),
            ]);

            DB::commit();

            // DISPARAMOS EL WEBSOCKET A LA PANTALLA
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
        // 1. Validación estricta (El escudo de nuestra API)
        $request->validate([
            'sede_id' => 'required|exists:sedes,id',
            'tipo_turno_id' => 'required|exists:tipo_turnos,id',
        ]);

        

        DB::beginTransaction();
        try {
            // 2. Obtenemos la letra del trámite (ej: 'D')
            $tipo = TipoTurno::find($request->tipo_turno_id);
            $letra = $tipo->clave;
            $hoy = now()->toDateString();

            // 2. EL BLINDAJE: Buscamos el último turno de HOY, de esta SEDE y de este TIPO
            $ultimoTurno = Turno::where('sede_id', $request->sede_id)
                                ->where('tipo_turno_id', $request->tipo_turno_id)
                                ->whereDate('created_at', $hoy)
                                ->lockForUpdate() // Congela la fila por unos milisegundos para evitar duplicados
                                ->latest('id')
                                ->first();

            // 3. LÓGICA DEL CONSECUTIVO (Reinicia a 1 cada día)
            $consecutivo = 1;
            if ($ultimoTurno) {
                // Asumiendo que el formato será "T-045", lo separamos por el guion y tomamos el número
                $partes = explode('-', $ultimoTurno->numero_turno);
                $numeroAnterior = (int) end($partes);
                $consecutivo = $numeroAnterior + 1;
            }

            // Armamos el string final rellenando con ceros a la izquierda (Ej: T-001, T-045, T-120)
            $numeroTurnoString = $tipo->clave . '-' . str_pad($consecutivo, 4, '0', STR_PAD_LEFT);

            // 4. CREAMOS EL TURNO
            $nuevoTurno = Turno::create([
                'sede_id' => $request->sede_id,
                'tipo_turno_id' => $request->tipo_turno_id,
                'numero_turno' => $numeroTurnoString,
                'status' => 0, // Nace en la sala de espera
                // caja_id, user_id, y las horas de atención se quedan en null por ahora
            ]);

            DB::commit();

            // DISPARAMOS EL WEBSOCKET A LOS CAJEROS
            TurnoGenerado::dispatch($request->sede_id);
            
            // Retornamos la info lista para que el Kiosco mande a imprimir el ticket
            // return response()->json([
            //     'status' => 'ok',
            //     'message' => 'Turno generado exitosamente',
            //     'data' => [
            //         'turno' => $nuevoTurno->numero_turno,
            //         'descripcion' => $tipo->descripcion,
            //         'fecha' => now()->format('d/m/Y H:i:s')
            //     ]
            // ], 201);
            return response()->json([
                'status' => 'ok',
                'message' => 'Turno generado exitosamente',
                // Mandamos el objeto $nuevoTurno directamente en la llave 'turno' 
                // para que Kiosco.vue pueda hacer: response.data.turno.turno
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
            // UN SOLO VIAJE A LA BD: Agrupamos y contamos por tipo de turno
            $query = Turno::select('tipo_turno_id', DB::raw('count(*) as total'))
                ->where('sede_id', $sede_id)
                ->where('status', 0) // status 0 = En espera
                ->whereDate('created_at', Carbon::today());

            if ($user && $user->caja_id) {
                $tiposPermitidos = DB::table('caja_tipo_turno')
                    ->where('caja_id', $user->caja_id)
                    ->pluck('tipo_turno_id')
                    ->toArray();

                $query->whereIn('tipo_turno_id', $tiposPermitidos);
            }

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
            // 1. Eager Loading para traer la caja en la misma consulta
            $turnos = Turno::with('caja') 
                ->where('sede_id', $sede_id)
                ->where('status', '>=', 1) // 1 = En atención, 2 = Finalizados
                ->latest('hora_atencion_inicio')
                ->limit(10)
                ->get()
                ->map(function ($turno) {
                    return [
                        'turno' => $turno->numero_turno, // Ej: T-045
                        'caja'  => $turno->caja ? $turno->caja->nombre : '--'
                    ];
                });

            // 2. Rellenamos el arreglo hasta llegar a 10 elementos para que la TV no se vea vacía
            $turnosPadded = $turnos->pad(10, ['turno' => '--', 'caja' => '--']);

            return response()->json([
                "status"  => $turnos->isEmpty() ? "no-data" : "ok",
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
        // Buscamos la sede con sus tipos de turno cargados
        $sede = Sede::with('tiposTurno')->find($sedeId);

        if (!$sede) {
            return response()->json(['status' => 'error', 'message' => 'Sede no encontrada'], 404);
        }

        return response()->json([
            'status' => 'ok',
            'sede' => $sede->nombre,
            // Devolvemos solo los tipos de turno habilitados para esta sede
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
            // Buscamos el turno que este cajero tiene en atención (status = 1)
            $turnoActual = Turno::where('sede_id', $user->sede_id)
                ->where('caja_id', $user->caja_id)
                ->where('status', 1) 
                ->first();

            if ($turnoActual) {
                $turnoActual->status = 2; // Lo marcamos como finalizado
                $turnoActual->hora_atencion_fin = Carbon::now();
                $turnoActual->save();

                $cajaNombre = DB::table('cajas')->where('id', $user->caja_id)->value('nombre');
                // Aquí podrías disparar un evento Reverb "TurnoFinalizado" si quieres
                // broadcast(new TurnoFinalizado($user->sede_id))->toOthers(); 
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