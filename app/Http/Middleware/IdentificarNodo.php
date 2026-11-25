<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Agrega X-Servidor con el nombre del nodo que atendió, para comprobar el balanceo (Práctica 5).
 */
class IdentificarNodo
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (filled(config('services.nodo'))) {
            $response->headers->set('X-Servidor', (string) config('services.nodo'));
        }

        return $response;
    }
}
