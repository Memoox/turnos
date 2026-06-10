<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TipoTurno;
use App\Models\Sede;

class SuperadminTipoTurnoController extends Controller
{
    // 1. Obtener todos los trámites (y la lista de sedes para el formulario)
    public function index()
    {
        try {
            // Traemos los trámites incluyendo las sedes donde están asignados
            $tramites = TipoTurno::with('sedes')->withTrashed()->orderBy('id', 'desc')->get()->map(function ($tramite) {
                $tramite->is_active = !$tramite->trashed(); 
                return $tramite;
            });

            // También mandamos el catálogo de sedes activas para pintar los checkboxes en Vue
            $sedesDisponibles = Sede::orderBy('nombre', 'asc')->get();
            
            return response()->json([
                'status' => 'ok',
                'tramites' => $tramites,
                'sedes_disponibles' => $sedesDisponibles
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // 2. Crear Trámite
    public function store(Request $request)
    {
        $request->validate([
            'clave' => 'required|string|max:10|unique:tipo_turnos,clave',
            'descripcion' => 'required|string|max:255',
            'sedes' => 'array' // Validamos que manden un arreglo de sedes
        ]);

        try {
            $tramite = TipoTurno::create([
                'clave' => strtoupper($request->clave),
                'descripcion' => $request->descripcion,
                'status' => 1 // Llenamos tu columna status manual por si no tiene default
            ]);

            // MAGIA: Sincronizamos la tabla pivote (sede_tipo_turno)
            if ($request->has('sedes')) {
                $tramite->sedes()->sync($request->sedes);
            }

            return response()->json(['status' => 'ok', 'message' => 'Trámite creado con éxito'], 201);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // 3. Editar Trámite
    public function update(Request $request, $id)
    {
        $request->validate([
            'clave' => 'required|string|max:10|unique:tipo_turnos,clave,' . $id,
            'descripcion' => 'required|string|max:255',
            'sedes' => 'array'
        ]);

        try {
            $tramite = TipoTurno::withTrashed()->findOrFail($id);
            
            $tramite->update([
                'clave' => strtoupper($request->clave),
                'descripcion' => $request->descripcion
            ]);

            // Actualizamos la tabla pivote automáticamente
            if ($request->has('sedes')) {
                $tramite->sedes()->sync($request->sedes);
            }

            return response()->json(['status' => 'ok', 'message' => 'Trámite actualizado con éxito'], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // 4. Activar o Desactivar (SoftDelete)
    public function toggleStatus($id)
    {
        try {
            $tramite = TipoTurno::withTrashed()->findOrFail($id);

            if ($tramite->trashed()) {
                $tramite->restore();
            } else {
                $tramite->delete();
            }

            return response()->json(['status' => 'ok', 'message' => 'Estado del trámite actualizado'], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}