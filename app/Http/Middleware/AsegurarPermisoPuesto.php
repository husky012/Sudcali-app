<?php

namespace App\Http\Middleware;

use App\Models\Empleado;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AsegurarPermisoPuesto
{
    /**
     * Restringe secciones del POS según el puesto del empleado.
     *
     * Uso: AsegurarPermisoPuesto::class.':administrar' o ':inventario'.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permiso): Response
    {
        /** @var Empleado $empleado */
        $empleado = $request->user('empleado');

        $autorizado = match ($permiso) {
            'administrar' => $empleado->puesto->puedeAdministrar(),
            'inventario' => $empleado->puesto->puedeGestionarInventario(),
            default => false,
        };

        abort_unless($autorizado, 403, 'Tu puesto no tiene acceso a esta sección.');

        return $next($request);
    }
}
