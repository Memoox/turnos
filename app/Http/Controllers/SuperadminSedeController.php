<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sede;

class SuperadminSedeController extends Controller
{

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

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:sedes,nombre'
        ]);

        try {
            $sede = Sede::create([
                'nombre' => $request->nombre
            ]);

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

    public function update(Request $request, $id)
    {
        $request->validate([
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

    public function toggleStatus($id)
    {
        try {
            $sede = Sede::withTrashed()->findOrFail($id);

            if ($sede->trashed()) {
                $sede->restore(); 
                $mensaje = 'Sede reactivada con éxito';
            } else {
                $sede->delete(); 
                $mensaje = 'Sede dada de baja';
            }

            return response()->json([
                'status' => 'ok',
                'message' => $mensaje,
                'sede_status' => !$sede->trashed() 
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function forceDelete($id)
    {
        try {
            $sede = Sede::withTrashed()->findOrFail($id);

            $tieneUsuarios = \App\Models\User::where('sede_id', $id)->exists();
            $tieneTurnos = \App\Models\Turno::where('sede_id', $id)->exists();

            if ($tieneUsuarios || $tieneTurnos) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No se puede eliminar esta Sede porque ya tiene Usuarios o Turnos registrados. Por seguridad, solo puedes darla de baja.'
                ], 400); // 400 Bad Request
            }

            $sede->tiposTurno()->detach(); 
            $sede->forceDelete();

            return response()->json(['status' => 'ok', 'message' => 'Sede eliminada permanentemente'], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function listaDesplegable()
    {
        
        $sedes = Sede::select('id', 'nombre')->get(); 
        return response()->json(['status' => 'ok', 'sedes' => $sedes]);
    }
}