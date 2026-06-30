<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TipoTurno;
use App\Models\Sede;
use Illuminate\Validation\Rule;

class SuperadminTipoTurnoController extends Controller
{
    public function index(Request $request)
    {
        try {
            $search = $request->query('search');

            $tramites = TipoTurno::with('sedes')->withTrashed()
                ->when($search, function ($query, $search) {
                    $query->where(function($q) use ($search) {
                        $q->where('clave', 'like', "%{$search}%")
                          ->orWhere('descripcion', 'like', "%{$search}%");
                    });
                })
                ->orderBy('id', 'desc')
                ->paginate(10);

            $tramites->getCollection()->transform(function ($tramite) {
                $tramite->is_active = !$tramite->trashed();
                return $tramite;
            });

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

    public function store(Request $request)
    {
        $request->validate([
            'clave' => 'required|string|max:10|unique:tipo_turnos,clave',
            'descripcion' => 'required|string|max:255|unique:tipo_turnos,descripcion',
            'sedes' => 'array', 
            'icono' => 'nullable|string|max:10', 
        ]);

        try {
            $tramite = TipoTurno::create([
                'clave' => strtoupper($request->clave),
                'descripcion' => $request->descripcion,
                'icono' => $request->icono,
                
            ]);

            if ($request->has('sedes')) {
                $tramite->sedes()->sync($request->sedes);
            }

            return response()->json(['status' => 'ok', 'message' => 'Trámite creado con éxito'], 201);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'clave' => [
                'required', 
                'string', 
                'max:50', 
                Rule::unique('tipo_turnos', 'clave')->ignore($id)
            ],
            'descripcion' => [
                'required', 
                'string', 
                'max:255', 
                Rule::unique('tipo_turnos', 'descripcion')->ignore($id)
            ],
            'sedes' => 'array',
            'icono' => 'nullable|string|max:10',
        ]);

        try {
            $tramite = TipoTurno::withTrashed()->findOrFail($id);
            
            $tramite->update([
                'clave' => strtoupper($request->clave),
                'descripcion' => $request->descripcion,
                'icono' => $request->icono,
            ]);

            if ($request->has('sedes')) {
                $tramite->sedes()->sync($request->sedes);
            }

            return response()->json(['status' => 'ok', 'message' => 'Trámite actualizado con éxito'], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

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

    public function forceDelete($id)
    {
        try {
            $tramite = TipoTurno::withTrashed()->findOrFail($id); 

            $tieneTurnos = \App\Models\Turno::where('tipo_turno_id', $id)->exists();

            if ($tieneTurnos) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No se puede eliminar este Trámite porque ya tiene turnos generados en el historial. Solo puedes darlo de baja.'
                ], 400);
            }

            \Illuminate\Support\Facades\DB::table('sede_tipo_turno')
                ->where('tipo_turno_id', $id)
                ->delete();

            $tramite->forceDelete();

            return response()->json(['status' => 'ok', 'message' => 'Trámite eliminado permanentemente'], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}