<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\IpUtils;

/**
 * Bitácora de eventos de seguridad (OWASP A09) en storage/logs/seguridad-*.log.
 *
 * Nunca registra contraseñas, tokens, respuestas del captcha ni datos personales: el correo y
 * la IP se sustituyen por una huella que permite relacionar eventos sin revelar de quién son.
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

        if (array_key_exists('correo', $contexto)) {
            $contexto['cuenta'] = self::huella($contexto['correo']);
            unset($contexto['correo']);
        }

        Log::channel('seguridad')->log($nivel, $evento, [
            ...$contexto,
            'red' => IpUtils::anonymize((string) $request->ip()),
            'origen' => self::huella($request->ip()),
            'metodo' => $request->method(),
            'ruta' => $request->path(),
            'agente' => mb_substr((string) $request->userAgent(), 0, 200),
        ]);
    }

    /**
     * Huella irreversible de un dato personal (HMAC con la llave de la aplicación).
     */
    public static function huella(mixed $dato): ?string
    {
        if (! is_string($dato) || $dato === '') {
            return null;
        }

        return substr(hash_hmac('sha256', $dato, (string) config('app.key')), 0, 16);
    }
}
