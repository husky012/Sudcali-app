<?php

namespace App\Http\Requests\PuntoVenta;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AbrirCajaRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'monto_inicial' => ['required', 'numeric', 'min:0', 'max:100000'],
        ];
    }
}
