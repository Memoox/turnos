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

        $cajas = Caja::with('tiposTurnos')
            ->where('sede_id', $admin->sede_id)
            ->get();

        $tiposTurnos = TipoTurno::all();

        return response()->json([
            'status' => 'ok',
            'cajas' => $cajas,
            'tipos_turnos' => $tiposTurnos
        ]);
    }

    // 2. CREAR NUEVA VENTANILLA
    public function store(Request $request)
    {
        $admin = $request->user();

        $request->validate([
            'nombre' => 'required|string|max:255',
            'tipos_turnos' => 'array', // Validamos que manden un arreglo de IDs
            'tipos_turnos.*' => 'exists:tipos_turnos,id'
        ]);

        $nuevaCaja = Caja::create([
            'nombre' => $request->nombre,
            'sede_id' => $admin->sede_id, // Candado de seguridad
        ]);

        return response()->json([
            'status' => 'ok',
            'message' => 'Ventanilla creada exitosamente',
            'caja' => $nuevaCaja
        ]);
    }

    // 3. ACTUALIZAR NOMBRE DE VENTANILLA
    public function update(Request $request, $id)
    {
        $admin = $request->user();
        
        // El findOrFail con where asegura que no pueda editar cajas de otras sedes
        $caja = Caja::where('sede_id', $admin->sede_id)->findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255'
        ]);

        $caja->nombre = $request->nombre;
        $caja->save();

        return response()->json([
            'status' => 'ok',
            'message' => 'Ventanilla actualizada correctamente',
            'caja' => $caja
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