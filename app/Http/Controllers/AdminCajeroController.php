<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Rol; // O Rol, dependiendo de cómo llamaste a tu modelo
use App\Models\Caja;
use Illuminate\Support\Facades\Hash;

class AdminCajeroController extends Controller
{
    // 1. LISTAR CAJEROS Y VENTANILLAS
    public function index(Request $request)
    {
        $admin = $request->user();
        $search = $request->query('search');

        // Traemos solo a los usuarios que son de la misma sede y tienen rol de cajero
        $cajeros = User::with('caja') // Eager loading para traer el nombre de su ventanilla
            ->where('sede_id', $admin->sede_id)
            ->whereHas('rol', function ($query) {
                $query->where('clave', 'cajero');
            })
            ->get();

        // Traemos las cajas (ventanillas) de esta sede para el <select> del formulario en Vue
        $cajas = Caja::where('sede_id', $admin->sede_id)->get();

        return response()->json([
            'status' => 'ok',
            'cajeros' => $cajeros,
            'cajas' => $cajas
        ]);
    }

    // 2. CREAR UN NUEVO CAJERO
    public function store(Request $request)
    {
        $admin = $request->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'caja_id' => 'nullable|exists:cajas,id|unique:users,caja_id'
        ]);

        // Buscamos el ID del rol 'cajero' en la base de datos
        $rolCajero = Rol::where('clave', 'cajero')->first();

        $nuevoCajero = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'rol_id' => $rolCajero->id,
            'sede_id' => $admin->sede_id, // Lo atamos automáticamente a la sede del admin
            'caja_id' => $request->caja_id,
        ]);

        return response()->json([
            'status' => 'ok',
            'message' => 'Cajero registrado exitosamente',
            'cajero' => $nuevoCajero->load('caja')
        ]);
    }

    // 3. ACTUALIZAR CAJERO (Ej: Cambiarlo de ventanilla)
    public function update(Request $request, $id)
    {
        $admin = $request->user();
        
        $cajero = User::where('sede_id', $admin->sede_id)->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$cajero->id,
            'password' => 'nullable|string|min:6', // Opcional por si quiere cambiar la clave
            'caja_id' => 'nullable|exists:cajas,id|unique:users,caja_id,'.$cajero->id,
        ]);

        $cajero->name = $request->name;
        $cajero->email = $request->email;
        $cajero->caja_id = $request->caja_id;

        if ($request->filled('password')) {
            $cajero->password = Hash::make($request->password);
        }

        $cajero->save();

        return response()->json([
            'status' => 'ok',
            'message' => 'Datos actualizados correctamente',
            'cajero' => $cajero->load('caja')
        ]);
    }

    // 4. ELIMINAR CAJERO (Baja del sistema)
    public function destroy(Request $request, $id)
    {
        $admin = $request->user();
        $cajero = User::where('sede_id', $admin->sede_id)->findOrFail($id);
        
        $cajero->caja_id = null;
        $cajero->save();
        
        $cajero->delete();

        return response()->json([
            'status' => 'ok',
            'message' => 'Cajero dado de baja'
        ]);
    }
}