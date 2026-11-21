<?php

namespace App\Http\Middleware;

use App\Models\Empleado;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AsegurarCajaAbierta
{
    /**
     * Envía al empleado a abrir caja si todavía no tiene un corte abierto.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Empleado $empleado */
        $empleado = $request->user('empleado');

        if ($empleado->corteAbierto === null) {
            return to_route('pos.caja.create')
                ->with('aviso', 'Abre tu caja antes de registrar ventas.');
        }

        return $next($request);
    }
}
