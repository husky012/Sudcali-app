<?php

namespace App\Actions\PuntoVenta;

use App\Models\Empleado;

/**
 * Token de corta duración para conectarse al servidor WebSocket (server-socket.js).
 *
 * Formato: "{empleado_id}.{expira_unix}.{hmac_sha256}". El servidor Node
 * comprueba la firma con el mismo WEBSOCKET_SECRET y rechaza tokens vencidos.
 */
class FirmarTokenWebSocket
{
    public const MINUTOS_VIGENCIA = 30;

    /**
     * Devuelve null si el WebSocket no está configurado.
     */
    public function handle(Empleado $empleado): ?string
    {
        $secreto = config('services.websocket.secret');

        if (blank($secreto) || blank(config('services.websocket.url'))) {
            return null;
        }

        $carga = $empleado->id.'.'.now()->addMinutes(self::MINUTOS_VIGENCIA)->getTimestamp();

        return $carga.'.'.hash_hmac('sha256', $carga, $secreto);
    }
}
