<?php

namespace App\Http\Requests\Tienda;

use App\Http\Requests\PuntoVenta\IniciarSesionRequest;

/**
 * Mismas reglas y bloqueo por intentos que el login de empleados,
 * con sus propios mensajes y su propia bolsa de errores.
 */
class IniciarSesionClienteRequest extends IniciarSesionRequest
{
    /**
     * @var string
     */
    protected $errorBag = 'cliente';

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            ...parent::messages(),
            'correo.required' => 'Escribe el correo con el que te registraste.',
        ];
    }
}
