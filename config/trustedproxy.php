<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Proxies de confianza
    |--------------------------------------------------------------------------
    |
    | Solo de estas direcciones se aceptan X-Forwarded-Proto y X-Forwarded-For.
    | Deben ser las del balanceador. Por defecto, únicamente el propio equipo
    | (Nginx o cloudflared en la misma máquina). Nunca usar "*" en producción:
    | cualquiera podría fingir su IP y saltarse los límites de intentos.
    |
    */

    'proxies' => env('TRUSTED_PROXIES', '127.0.0.1,::1'),

];
