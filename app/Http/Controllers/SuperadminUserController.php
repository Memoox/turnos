<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Sede;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SuperadminUserController extends Controller
{
    public function index(Request $request)
    {
        try {
            
            $search = $request->query('search');

            
            $usuarios = User::with('sede')
                ->withTrashed()
                ->when($search, function ($query, $search) {
                    $query->where(function($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                    });
                })
                ->orderBy('id', 'desc')
                ->paginate(10);

            $usuarios->getCollection()->transform(function ($user) {
                $user->is_active = !$user->trashed(); 
                return $user;
            });

            $sedes = Sede::orderBy('nombre', 'asc')->get();
            
            return response()->json([
                'status' => 'ok',
                'usuarios' => $usuarios,
                'sedes_disponibles' => $sedes
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'rol_id' => 'required|integer',
            'sede_id' => 'nullable|exists:sedes,id' 
        ]);

        try {
            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password), 
                'rol_id' => $request->rol_id,
                'sede_id' => $request->rol_id == 1 ? null : $request->sede_id, 
                'caja_id' => null 
            ]);

            return response()->json(['status' => 'ok', 'message' => 'Usuario creado con éxito'], 201);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($id)], 
            'rol_id' => 'required|integer',
            'sede_id' => 'nullable|exists:sedes,id'
        ]);

        try {
            $user = User::withTrashed()->findOrFail($id);

            if ($request->rol_id != 3 || $user->getOriginal('sede_id') != $request->sede_id) {
                $user->caja_id = null; //
            }
            
            $user->name = $request->name;
            $user->email = $request->email;
            $user->rol_id = $request->rol_id;
            $user->sede_id = $request->rol_id == 1 ? null : $request->sede_id;

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            return response()->json(['status' => 'ok', 'message' => 'Usuario actualizado con éxito'], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function toggleStatus($id)
    {
        try {
            if ($id == 1) {
                return response()->json(['status' => 'error', 'message' => 'No puedes dar de baja al Superadmin principal'], 403);
            }

            $user = User::withTrashed()->findOrFail($id);

            if ($user->trashed()) {
                $user->restore();
            } else {

                $user->caja_id = null;
                $user->save();
                $user->delete();
            }

            return response()->json(['status' => 'ok', 'message' => 'Estado del usuario actualizado'], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function forceDelete($id)
    {
        try {
            $user = User::withTrashed()->findOrFail($id);
            if ($user->id == 1) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'El Superadministrador principal del sistema no puede ser eliminado.'
                ], 403); 
            }

            $tieneTurnos = \App\Models\Turno::where('user_id', $id)->exists();

            if ($tieneTurnos) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No se puede eliminar a este usuario porque ya tiene turnos atendidos. Solo puedes darlo de baja.'
                ], 400);
            }
            

            if ($user->rol_id == 2) {
                $horasDesdeCreacion = $user->created_at->diffInHours(now());
                
                if ($horasDesdeCreacion > 48) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'El periodo de gracia expiró. Este Administrador tiene más de 48 horas en el sistema y, por motivos de auditoría, ya solo puede ser dado de baja.'
                    ], 400);
                }
            }

            $user->forceDelete();

            return response()->json(['status' => 'ok', 'message' => 'Usuario eliminado permanentemente'], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}