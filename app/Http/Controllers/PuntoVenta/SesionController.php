<?php

namespace App\Http\Controllers\PuntoVenta;

use App\Http\Controllers\Controller;
use App\Http\Requests\PuntoVenta\IniciarSesionRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SesionController extends Controller
{
    /**
     * Muestra el inicio de sesión del punto de venta.
     */
    public function create(): View|RedirectResponse
    {
        if (Auth::guard('empleado')->check()) {
            return to_route('pos.inicio');
        }

        return view('login', ['pestanaInicial' => 'empleado']);
    }

    /**
     * Autentica a un empleado activo.
     */
    public function store(IniciarSesionRequest $request): RedirectResponse
    {
        $request->asegurarQueNoEsteBloqueado();

        $credenciales = [
            ...$request->validated(),
            'activo' => true,
        ];

        if (! Auth::guard('empleado')->attempt($credenciales, $request->boolean('recordar'))) {
            $request->registrarIntentoFallido();

            return back()
                ->withErrors(['correo' => 'Correo o contraseña incorrectos, o el empleado está inactivo.'], 'empleado')
                ->onlyInput('correo');
        }

        $request->limpiarIntentos();
        $request->session()->regenerate();

        return redirect()->intended(route('pos.inicio'));
    }

    /**
     * Cierra la sesión del empleado.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('empleado')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Borra en el navegador la caché, el service worker y la IndexedDB de /empleados.
        return to_route('pos.login')->header('Clear-Site-Data', '"cache", "storage"');
    }
}
