<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Bitácora de eventos de seguridad (OWASP A09) en storage/logs/seguridad-*.log.
 *
 * Nunca registra contraseñas, tokens ni respuestas del captcha.
 */
class BitacoraSeguridad
{
    /**
     * Registra un evento con los datos de la petición actual.
     *
     * @param  array<string, mixed>  $contexto
     */
    public static function registrar(string $evento, array $contexto = [], string $nivel = 'warning', ?Request $request = null): void
    {
        $request ??= request();

        Log::channel('seguridad')->log($nivel, $evento, [
            ...$contexto,
            'ip' => $request->ip(),
            'metodo' => $request->method(),
            'ruta' => $request->path(),
            'agente' => mb_substr((string) $request->userAgent(), 0, 200),
        ]);
    }
}
