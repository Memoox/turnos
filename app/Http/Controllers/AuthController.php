<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
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

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['status' => 'ok']);
    }

    public function me(Request $request)
    {
        $user = $request->user()->load(['sede', 'caja', 'rol']);

        return response()->json([
            'user' => $user
        ]);
    }
}
