<?php

namespace App\Http\Controllers\Tienda;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tienda\RegistrarClienteRequest;
use App\Models\Cliente;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegistroController extends Controller
{
    /**
     * Formulario para crear una cuenta de cliente.
     */
    public function create(): View|RedirectResponse
    {
        if (Auth::guard('cliente')->check()) {
            return to_route('home');
        }

        return view('registro');
    }

    /**
     * Crea la cuenta e inicia sesión.
     */
    public function store(RegistrarClienteRequest $request): RedirectResponse
    {
        $cliente = Cliente::create($request->safe()->except('terminos'));

        Auth::guard('cliente')->login($cliente);
        $request->session()->regenerate();

        return redirect()->intended(route('home'))
            ->with('exito', "¡Bienvenido a Sudcali, {$cliente->nombre}! Tu cuenta está lista.");
    }
}
