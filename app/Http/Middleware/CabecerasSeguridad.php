<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cabeceras de seguridad para todas las respuestas web (OWASP A02).
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

        // Con el servidor de desarrollo de Vite los scripts vienen de otro puerto;
        // la política solo se aplica con los archivos compilados (producción).
        if (! Vite::isRunningHot()) {
            $response->headers->set('Content-Security-Policy', $this->politicaDeContenido($request));
        }

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }

    /**
     * Content-Security-Policy: solo recursos propios, sin iframes ni plugins.
     */
    protected function politicaDeContenido(Request $request): string
    {
        $websocket = config('services.websocket.url');

        $directivas = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline'",
            "style-src 'self' 'unsafe-inline' https://fonts.bunny.net",
            "font-src 'self' data: https://fonts.bunny.net",
            "img-src 'self' data: blob: https:",
            trim("connect-src 'self' ".($websocket ?? '')),
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
