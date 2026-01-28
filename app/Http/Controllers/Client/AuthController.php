<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('client.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Verificar si el usuario está suspendido
            if (Auth::user()->isSuspended()) {
                Auth::logout();
                return back()->withErrors(['email' => 'Su cuenta está suspendida']);
            }

            return redirect()->intended(route('client.dashboard'));
        }

        return back()->withErrors(['email' => 'Credenciales incorrectas']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('client.login');
    }

    public function showForgotPassword()
    {
        return view('client.auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        // Implementar lógica de recuperación de contraseña
        return back()->with('status', 'Se ha enviado un enlace de recuperación a su correo');
    }
}
