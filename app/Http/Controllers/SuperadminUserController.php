<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Sede;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SuperadminUserController extends Controller
{
    // 1. Obtener todos los usuarios y catálogo de sedes
    public function index()
    {
        try {
            // Traemos los usuarios con su sede y ordenados por los más recientes
            $usuarios = User::with('sede')->withTrashed()->orderBy('id', 'desc')->get()->map(function ($user) {
                $user->is_active = !$user->trashed(); 
                return $user;
            });

            // Para el formulario (dropdown de sedes)
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

    // 2. Crear un nuevo usuario
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'rol_id' => 'required|integer',
            // La sede puede ser nula si es Superadmin (rol 1), pero es obligatoria para Admins y Cajeros
            'sede_id' => 'nullable|exists:sedes,id' 
        ]);

        try {
            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password), // Encriptamos la clave
                'rol_id' => $request->rol_id,
                'sede_id' => $request->rol_id == 1 ? null : $request->sede_id, // Forzamos null si es Superadmin
                'caja_id' => null // El superadmin no asigna cajas, eso lo hace el admin local
            ]);

            return response()->json(['status' => 'ok', 'message' => 'Usuario creado con éxito'], 201);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // 3. Editar un usuario existente
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($id)], // Ignoramos su propio email
            'rol_id' => 'required|integer',
            'sede_id' => 'nullable|exists:sedes,id'
        ]);

        try {
            $user = User::withTrashed()->findOrFail($id);
            
            // Actualizamos datos básicos
            $user->name = $request->name;
            $user->email = $request->email;
            $user->rol_id = $request->rol_id;
            $user->sede_id = $request->rol_id == 1 ? null : $request->sede_id;

            // Solo actualizamos la contraseña si el Superadmin escribió una nueva
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            return response()->json(['status' => 'ok', 'message' => 'Usuario actualizado con éxito'], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // 4. Activar o Desactivar (SoftDelete)
    public function toggleStatus($id)
    {
        try {
            // Protegemos al Superadmin Global original (ID 1) para que no se auto-borre por accidente
            if ($id == 1) {
                return response()->json(['status' => 'error', 'message' => 'No puedes dar de baja al Superadmin principal'], 403);
            }

            $user = User::withTrashed()->findOrFail($id);

            if ($user->trashed()) {
                $user->restore();
            } else {
                // Opcional: Si el usuario era cajero y estaba atendiendo, aquí podríamos liberar su caja.
                // Por ahora, solo lo desactivamos para que ya no pueda loguearse.
                $user->delete();
            }

            return response()->json(['status' => 'ok', 'message' => 'Estado del usuario actualizado'], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}