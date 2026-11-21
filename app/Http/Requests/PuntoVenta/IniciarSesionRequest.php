<?php

namespace App\Http\Requests\PuntoVenta;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class IniciarSesionRequest extends FormRequest
{
    /**
     * Intentos fallidos permitidos por correo e IP antes de bloquear.
     */
    public const INTENTOS_MAXIMOS = 5;

    /**
     * Cada pestaña del login muestra sus propios errores.
     *
     * @var string
     */
    protected $errorBag = 'empleado';

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'correo' => Str::lower(trim((string) $this->input('correo'))),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'correo' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'correo.required' => 'Escribe tu correo de empleado.',
            'correo.email' => 'Escribe un correo válido, por ejemplo nombre@sudcali.test.',
            'correo.max' => 'El correo no puede tener más de 255 caracteres.',
            'password.required' => 'Escribe tu contraseña.',
            'password.max' => 'La contraseña no puede tener más de 255 caracteres.',
        ];
    }

    /**
     * Detiene el intento si ya se agotaron los intentos permitidos.
     *
     * @throws ValidationException
     */
    public function asegurarQueNoEsteBloqueado(): void
    {
        if (! RateLimiter::tooManyAttempts($this->claveLimite(), self::INTENTOS_MAXIMOS)) {
            return;
        }

        event(new Lockout($this));

        $segundos = RateLimiter::availableIn($this->claveLimite());

        throw ValidationException::withMessages([
            'correo' => "Demasiados intentos fallidos. Intenta de nuevo en {$segundos} segundos.",
        ])->errorBag($this->errorBag);
    }

    /**
     * Registra un intento fallido.
     */
    public function registrarIntentoFallido(): void
    {
        RateLimiter::hit($this->claveLimite(), 60);
    }

    /**
     * Limpia los intentos fallidos tras un acceso correcto.
     */
    public function limpiarIntentos(): void
    {
        RateLimiter::clear($this->claveLimite());
    }

    /**
     * Clave del limitador: correo + IP.
     */
    protected function claveLimite(): string
    {
        return $this->errorBag.'-login|'.$this->input('correo').'|'.$this->ip();
    }
}
