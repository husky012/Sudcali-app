<?php

namespace App\Actions\Tienda;

use Illuminate\Contracts\Session\Session;

/**
 * Captcha propio del registro (OWASP A07): una suma sencilla cuya respuesta
 * solo vive en la sesión del servidor y sirve para un único intento.
 */
class Captcha
{
    public const CLAVE_SESION = 'captcha_registro';

    public function __construct(protected Session $session) {}

    /**
     * Genera una pregunta nueva y guarda la respuesta en la sesión.
     */
    public function generar(): string
    {
        $primero = random_int(2, 9);
        $segundo = random_int(1, 9);

        $this->session->put(self::CLAVE_SESION, $primero + $segundo);

        return "¿Cuánto es {$primero} + {$segundo}?";
    }

    /**
     * Comprueba la respuesta y la invalida para que no se pueda reutilizar.
     */
    public function esValido(mixed $respuesta): bool
    {
        $esperada = $this->session->pull(self::CLAVE_SESION);

        return $esperada !== null
            && is_numeric($respuesta)
            && (int) $respuesta === (int) $esperada;
    }
}
