<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Caja;
use App\Models\TipoTurno;
use Illuminate\Validation\Rule;

class AdminCajaController extends Controller
{
    // 1. LISTAR VENTANILLAS DE LA SEDE
    public function index(Request $request)
    {
        try {
            $admin = auth()->user();
            $search = $request->query('search');

            $cajas = Caja::with('tiposDeTurno')
                ->where('sede_id', $admin->sede_id)
                ->withTrashed()
                ->when($search, function ($query, $search) {
                    $query->where('nombre', 'like', "%{$search}%");
                })
                ->orderBy('id', 'desc')
                ->paginate(10);

            // Evaluamos si están activas o en la papelera
            $cajas->getCollection()->transform(function ($caja) {
                $caja->is_active = !$caja->trashed();
                return $caja;
            });

            $tiposTurnos = TipoTurno::all();

            return response()->json([
                'status' => 'ok',
                'cajas' => $cajas,
                'tipo_turnos' => $tiposTurnos
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // 2. CREAR NUEVA VENTANILLA
    public function store(Request $request)
    {
        $admin = auth()->user();

        $request->validate([
            'nombre' => [
                'required', 'string', 'max:255',
                Rule::unique('cajas')->where(function ($query) use ($admin) {
                    // Validamos que sea único en esa sede y que no esté en la papelera
                    return $query->where('sede_id', $admin->sede_id)->whereNull('deleted_at');
                })
            ],
            'tipo_turnos' => 'array', 
            'tipo_turnos.*' => 'exists:tipo_turnos,id',
        ]);

        $nuevaCaja = Caja::create([
            'nombre' => $request->nombre,
            'sede_id' => $admin->sede_id,
        ]);

        if ($request->has('tipo_turnos')) {
            $nuevaCaja->tiposDeTurno()->sync($request->tipo_turnos);
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'Ventanilla creada exitosamente',
            'caja' => $nuevaCaja->load('tiposDeTurno')
        ]);
    }

    // 3. ACTUALIZAR NOMBRE DE VENTANILLA
    public function update(Request $request, $id)
    {
        $admin = auth()->user();
        $caja = Caja::where('sede_id', $admin->sede_id)->findOrFail($id);

        $request->validate([
          'nombre' => [
                'required', 'string', 'max:255',
                Rule::unique('cajas')->where(function ($query) use ($admin) {
                    return $query->where('sede_id', $admin->sede_id)->whereNull('deleted_at');
                })->ignore($id)
            ],
            'tipo_turnos' => 'array', 
            'tipo_turnos.*' => 'exists:tipo_turnos,id',
        ]);

        $caja->nombre = $request->nombre;
        $caja->save();

        if ($request->has('tipo_turnos')) {
            $caja->tiposDeTurno()->sync($request->tipo_turnos);
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'Ventanilla actualizada correctamente',
            'caja' => $caja->load('tiposDeTurno')
        ]);
    }

    // 4. ELIMINAR VENTANILLA
    public function toggleEstado($id)
    {
        try {
            $admin = auth()->user();
            $caja = Caja::where('sede_id', $admin->sede_id)
                ->withTrashed()
                ->findOrFail($id);

            if ($caja->trashed()) {
                $caja->restore();
                $mensaje = 'Ventanilla reactivada';
            } else {
                // Si la damos de baja, le quitamos la ventanilla a los cajeros que la tenían asignada
                \App\Models\User::where('caja_id', $caja->id)->update(['caja_id' => null]);
                
                $caja->delete();
                $mensaje = 'Ventanilla dada de baja';
            }

            return response()->json(['status' => 'ok', 'message' => $mensaje], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}