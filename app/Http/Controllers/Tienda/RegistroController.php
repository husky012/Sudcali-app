<?php

namespace App\Http\Controllers\Tienda;

use App\Actions\Tienda\Captcha;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tienda\RegistrarClienteRequest;
use App\Models\Cliente;
use App\Support\BitacoraSeguridad;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegistroController extends Controller
{
    /**
     * Formulario para crear una cuenta de cliente.
     */
    public function create(Captcha $captcha): View|RedirectResponse
    {
        if (Auth::guard('cliente')->check()) {
            return to_route('home');
        }

        return view('registro', ['preguntaCaptcha' => $captcha->generar()]);
    }

    /**
     * Crea la cuenta e inicia sesión.
     */
    public function store(RegistrarClienteRequest $request): RedirectResponse
    {
        $cliente = Cliente::create($request->safe()->except(['terminos', 'captcha', 'sitio_web']));

        BitacoraSeguridad::registrar('registro_cliente', ['cliente_id' => $cliente->id], 'info');

        Auth::guard('cliente')->login($cliente);
        $request->session()->regenerate();

        return redirect()->intended(route('home'))
            ->with('exito', "¡Bienvenido a Sudcali, {$cliente->nombre}! Tu cuenta está lista.");
    }
}
