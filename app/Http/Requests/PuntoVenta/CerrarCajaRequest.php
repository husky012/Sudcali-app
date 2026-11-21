<?php

namespace App\Http\Requests\PuntoVenta;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CerrarCajaRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'monto_final_real' => ['required', 'numeric', 'min:0', 'max:1000000'],
        ];
    }
}
