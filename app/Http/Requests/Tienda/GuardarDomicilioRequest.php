<?php

namespace App\Http\Requests\Tienda;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class GuardarDomicilioRequest extends FormRequest
{
    /**
     * El domicilio debe estar en Baja California Sur, donde se hacen las entregas.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'latitud' => ['required', 'numeric', 'between:22.5,28.5'],
            'longitud' => ['required', 'numeric', 'between:-115.5,-109'],
        ];
    }
}
