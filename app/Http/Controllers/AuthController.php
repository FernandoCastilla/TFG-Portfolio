<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Función para PROCESAR el formulario de login
    public function login(Request $request)
    {
        // 1. Validamos que el usuario ha rellenado los campos
        $credenciales = $request->validate([
            'email' => ['required', 'string'], // Lo llamamos 'email' en el HTML, pero guarda tu 'admin'
            'password' => ['required', 'string'],
        ]);

        // 2. Intentamos hacer "match" en la base de datos
        if (Auth::attempt($credenciales)) {
            // Si acierta la contraseña, regeneramos la sesión por seguridad y lo mandamos al Panel
            $request->session()->regenerate();
            return redirect()->intended('/');
        }

        // 3. Si falla, lo devolvemos al formulario con el mensaje de error rojo
        return back()->withErrors([
            'email' => 'Las credenciales no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    // Función para CERRAR sesión
    public function logout(Request $request)
    {
        Auth::logout();
        
        // Destruimos la sesión y el token de seguridad
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
