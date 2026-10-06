<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        // 1. SUPERADMIN (rol_id 1) -> Va a la gestión de Socios
        if ($user->rol_id == 1) {
            return redirect()->intended(route('usuarios.index'));
        }

        // 2. ADMINISTRADOR / HACIENDA (rol_id 2) -> Va directo a Lecturas de Agua
        if ($user->rol_id == 2) {
            return redirect()->intended(route('lecturas.index'));
        }

        // 3. SOCIO COMÚN (rol_id 3) -> Va a su pantalla de consumo
        return redirect()->intended(route('socio.consumo'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}