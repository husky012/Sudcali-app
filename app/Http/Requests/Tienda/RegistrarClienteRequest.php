<?php

namespace App\Http\Requests\Tienda;

use App\Actions\Tienda\Captcha;
use App\Support\BitacoraSeguridad;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class RegistrarClienteRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'correo' => Str::lower(trim((string) $this->input('correo'))),
            'telefono' => preg_replace('/\D/', '', (string) $this->input('telefono')) ?: null,
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
            'nombre' => ['required', 'string', 'max:100'],
            'apellido_paterno' => ['required', 'string', 'max:100'],
            'apellido_materno' => ['nullable', 'string', 'max:100'],
            'telefono' => ['nullable', 'digits:10'],
            'correo' => ['required', 'email', 'max:255', 'unique:clientes,correo'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'terminos' => ['accepted'],
            'captcha' => ['required', function (string $atributo, mixed $valor, Closure $fallar): void {
                if (! app(Captcha::class)->esValido($valor)) {
                    BitacoraSeguridad::registrar('captcha_fallido', ['correo' => $this->input('correo')]);
                    $fallar('La respuesta de la verificación no es correcta. Resuelve la nueva pregunta.');
                }
            }],
            // Campo trampa: invisible para personas, los bots suelen llenarlo.
            'sitio_web' => ['prohibited'],
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
            'correo.unique' => 'Ya hay una cuenta con ese correo. Inicia sesión.',
            'terminos.accepted' => 'Debes aceptar los términos y condiciones.',
            'captcha.required' => 'Responde la pregunta de verificación.',
            'sitio_web.prohibited' => 'No pudimos crear la cuenta. Inténtalo de nuevo.',
        ];
    }
}
