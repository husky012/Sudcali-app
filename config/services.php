<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'websocket' => [
        'url' => env('WEBSOCKET_URL'),
        'secret' => env('WEBSOCKET_SECRET'),
    ],

    // Contraseña de las cuentas de demostración que crean los seeders. Sin valor se genera una al azar.
    'demo' => [
        'password' => env('DEMO_PASSWORD'),
    ],

    // Servicio OSRM que traza el camino al domicilio. Vacío: se dibuja una línea recta.
    'rutas' => [
        'url' => env('RUTAS_URL', 'https://router.project-osrm.org'),
    ],

    // Nombre del nodo detrás del balanceador (Práctica 5). Vacío: no se anuncia.
    'nodo' => env('NODO'),

    'hsts' => [
        'max_age' => (int) env('HSTS_MAX_AGE', 31536000),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
