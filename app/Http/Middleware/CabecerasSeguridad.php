<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cabeceras de seguridad para todas las respuestas, incluidas las de error (OWASP A02).
 */
class CabecerasSeguridad
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! headers_sent()) {
            header_remove('X-Powered-By');
        }

        $response->headers->remove('X-Powered-By');
        $response->headers->add([
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=()',
            'Cross-Origin-Opener-Policy' => 'same-origin',
        ]);

        $response->headers->set('Content-Security-Policy', $this->politicaDeContenido($request));

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age='.config('services.hsts.max_age').'; includeSubDomains');
        }

        return $response;
    }

    /**
     * Content-Security-Policy: solo recursos propios, sin iframes ni plugins.
     *
     * Los scripts escritos dentro del HTML no se ejecutan: script-src no lleva 'unsafe-inline'.
     * Con el servidor de desarrollo de Vite (npm run dev) se permite además su puerto.
     */
    protected function politicaDeContenido(Request $request): string
    {
        $websocket = config('services.websocket.url');

        $vite = ! app()->isProduction() && is_file(public_path('hot')) ? ' http:' : '';
        $viteSocket = $vite === '' ? '' : ' http: ws:';

        $directivas = [
            "default-src 'self'",
            "script-src 'self'{$vite}",
            "style-src 'self' 'unsafe-inline'{$vite}",
            "font-src 'self' data:{$vite}",
            "img-src 'self' data: blob:{$vite}",
            rtrim("connect-src 'self' ".($websocket ?? '')).$viteSocket,
            "worker-src 'self'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'",
        ];

        if ($request->isSecure()) {
            $directivas[] = 'upgrade-insecure-requests';
        }

        return implode('; ', $directivas);
    }
}
