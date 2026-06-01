<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // 1. Iniciar Sesión
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            // Genera la sesión segura de Laravel
            $request->session()->regenerate();

            $user = Auth::user()->load('rol');

            return response()->json([
                'status' => 'ok',
                'user' => Auth::user()
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Las credenciales no coinciden con nuestros registros.'
        ], 401);
    }

    // 2. Cerrar Sesión
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['status' => 'ok']);
    }

    // 3. Obtener el usuario actual (Para que Vue sepa si hay sesión activa)
    public function me(Request $request)
    {
        // Cargamos al usuario junto con su sede y su caja asignada
        $user = $request->user()->load(['sede', 'caja', 'rol']);

        return response()->json([
            'user' => $user
        ]);
    }
}
