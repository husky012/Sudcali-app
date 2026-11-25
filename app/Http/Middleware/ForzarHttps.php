<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * En producción toda petición http:// se redirige de forma permanente a https:// (OWASP A04).
 *
 * El balanceador ya redirige; esta es la segunda barrera si alguien llega directo a un nodo.
 */
class ForzarHttps
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->isProduction() || $request->isSecure() || $request->is('up', 'salud')) {
            return $next($request);
        }

        $urlPublica = (string) config('app.url');

        // Con APP_URL definida no se usa el encabezado Host, que el visitante puede alterar.
        $sitio = str_starts_with($urlPublica, 'https://')
            ? rtrim($urlPublica, '/')
            : 'https://'.$request->getHost();

        // 308 conserva el método en los envíos de formularios.
        return redirect()->away($sitio.$request->getRequestUri(), $request->isMethodSafe() ? 301 : 308);
    }
}
