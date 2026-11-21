<?php

namespace App\Http\Controllers\Tienda;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tienda\IniciarSesionClienteRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SesionClienteController extends Controller
{
    /**
     * Pantalla de acceso con la pestaña de cliente seleccionada.
     */
    public function create(): View|RedirectResponse
    {
        if (Auth::guard('cliente')->check()) {
            return to_route('home');
        }

        return view('login', ['pestanaInicial' => 'cliente']);
    }

    /**
     * Inicia sesión como cliente conservando el carrito de la visita.
     */
    public function store(IniciarSesionClienteRequest $request): RedirectResponse
    {
        $request->asegurarQueNoEsteBloqueado();

        if (! Auth::guard('cliente')->attempt($request->validated(), $request->boolean('recordar'))) {
            $request->registrarIntentoFallido();

            return back()
                ->withErrors(['correo' => 'Correo o contraseña incorrectos.'], 'cliente')
                ->onlyInput('correo');
        }

        $request->limpiarIntentos();
        $request->session()->regenerate();

        return redirect()->intended(route('home'))->with('exito', '¡Hola de nuevo, '.Auth::guard('cliente')->user()->nombre.'!');
    }

    /**
     * Cierra la sesión del cliente.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('cliente')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('home');
    }
}
