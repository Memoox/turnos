<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Caja;
use App\Models\TipoTurno;

class AdminCajaController extends Controller
{
    // 1. LISTAR VENTANILLAS DE LA SEDE
    public function index(Request $request)
    {
        $admin = $request->user();

        $cajas = Caja::with('tiposDeTurno')
            ->where('sede_id', $admin->sede_id)
            ->get();

        $tiposTurnos = TipoTurno::all();

        return response()->json([
            'status' => 'ok',
            'cajas' => $cajas,
            'tipo_turnos' => $tiposTurnos
        ]);
    }

    // 2. CREAR NUEVA VENTANILLA
    public function store(Request $request)
    {
        $admin = $request->user();

        $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo_turnos' => 'array', // Validamos que manden un arreglo de IDs
            'tipo_turnos.*' => 'exists:tipo_turnos,id'
        ]);

        $nuevaCaja = Caja::create([
            'nombre' => $request->nombre,
            'sede_id' => $admin->sede_id, // Candado de seguridad
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
        $admin = $request->user();
        
        // El findOrFail con where asegura que no pueda editar cajas de otras sedes
        $caja = Caja::where('sede_id', $admin->sede_id)->findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo_turnos' => 'array',
            'tipo_turnos.*' => 'exists:tipo_turnos,id'
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
    public function destroy(Request $request, $id)
    {
        $admin = $request->user();
        $caja = Caja::where('sede_id', $admin->sede_id)->findOrFail($id);
        
        $caja->delete();

        return response()->json([
            'status' => 'ok',
            'message' => 'Ventanilla eliminada'
        ]);
    }
}