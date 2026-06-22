<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Rol; 
use App\Models\Caja;
use Illuminate\Support\Facades\Hash;

class AdminCajeroController extends Controller
{
    public function index(Request $request)
    {
        $admin = auth()->user();
        $search = $request->query('search');

        $cajeros = User::with('caja') 
            ->where('sede_id', $admin->sede_id)
            ->whereHas('rol', function ($query) {
                $query->where('clave', 'cajero');
            })
            ->withTrashed()
                ->when($search, function ($query, $search) {
                    $query->where(function($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                    });
            })
            ->orderBy('id', 'desc')
            ->paginate(10);
        
        $cajeros->getCollection()->transform(function ($cajero) {
            $cajero->is_active = !$cajero->trashed();
            return $cajero;
        });

        $cajas = Caja::where('sede_id', $admin->sede_id)->get();

        return response()->json([
            'status' => 'ok',
            'cajeros' => $cajeros,
            'cajas' => $cajas
        ]);
    }

    public function store(Request $request)
    {
        $admin = $request->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'caja_id' => 'nullable|exists:cajas,id|unique:users,caja_id'
        ]);

        $rolCajero = Rol::where('clave', 'cajero')->first();

        $nuevoCajero = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'rol_id' => $rolCajero->id,
            'sede_id' => $admin->sede_id, 
            'caja_id' => $request->caja_id,
        ]);

        return response()->json([
            'status' => 'ok',
            'message' => 'Cajero registrado exitosamente',
            'cajero' => $nuevoCajero->load('caja')
        ]);
    }

    public function update(Request $request, $id)
    {
        $admin = $request->user();
        
        $cajero = User::where('sede_id', $admin->sede_id)->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$cajero->id,
            'password' => 'nullable|string|min:6', 
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

    // public function destroy(Request $request, $id)
    // {
    //     $admin = $request->user();
    //     $cajero = User::where('sede_id', $admin->sede_id)->findOrFail($id);
        
    //     $cajero->caja_id = null;
    //     $cajero->save();
        
    //     $cajero->delete();

    //     return response()->json([
    //         'status' => 'ok',
    //         'message' => 'Cajero dado de baja'
    //     ]);
    // }

    public function toggleEstado($id)
    {
        try {
            $admin = auth()->user();
            
            $cajero = User::where('sede_id', $admin->sede_id)
                ->where('rol_id', 3)
                ->withTrashed()
                ->findOrFail($id);

            if ($cajero->trashed()) {
                $cajero->restore();
                $mensaje = 'Cajero reactivado';
            } else {
                $cajero->caja_id = null;
                $cajero->save();
                
                $cajero->delete();
                $mensaje = 'Cajero dado de baja';
            }

            return response()->json(['status' => 'ok', 'message' => $mensaje], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}