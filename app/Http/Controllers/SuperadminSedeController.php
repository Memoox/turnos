<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sede;

class SuperadminSedeController extends Controller
{
    // 1. Obtener todas las sedes
    public function index(Request $request)
    {
        try {
            $search = $request->query('search');

            $sedes = Sede::withTrashed()
                ->when($search, function ($query, $search) {
                    $query->where('nombre', 'like', "%{$search}%");
                })
                ->orderBy('id', 'desc')
                ->paginate(10);

            $sedes->getCollection()->transform(function ($sede) {
                $sede->is_active = !$sede->trashed();
                return $sede;
            });
            
            return response()->json([
                'status' => 'ok',
                'sedes' => $sedes
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // 2. Crear una nueva sede
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:sedes,nombre'
        ]);

        try {
            // Ya solo guardamos el nombre
            $sede = Sede::create([
                'nombre' => $request->nombre
            ]);

            // Le inyectamos el status true para que Vue lo pinte bien al agregarlo a la tabla
            $sede->status = true;

            return response()->json([
                'status' => 'ok',
                'message' => 'Sede creada con éxito',
                'sede' => $sede
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // 3. Editar una sede existente
    public function update(Request $request, $id)
    {
        $request->validate([
            // Le concatenamos el $id al final para que IGNORE esta sede al buscar duplicados
            'nombre' => 'required|string|max:255|unique:sedes,nombre,' . $id
        ]);

        try {
            $sede = Sede::withTrashed()->findOrFail($id);
            $sede->update([
                'nombre' => $request->nombre
            ]);

            return response()->json([
                'status' => 'ok',
                'message' => 'Sede actualizada con éxito',
                'sede' => $sede
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // 4. Activar o Desactivar una Sede (SoftDelete / Restore)
    public function toggleStatus($id)
    {
        try {
            // Buscamos la sede, incluso si está eliminada lógicamente
            $sede = Sede::withTrashed()->findOrFail($id);

            if ($sede->trashed()) {
                $sede->restore(); // Si estaba dada de baja, la reactivamos
                $mensaje = 'Sede reactivada con éxito';
            } else {
                $sede->delete(); // Si estaba activa, le aplicamos el SoftDelete
                $mensaje = 'Sede dada de baja';
            }

            return response()->json([
                'status' => 'ok',
                'message' => $mensaje,
                // Devolvemos el nuevo estado para actualizar la vista
                'sede_status' => !$sede->trashed() 
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}