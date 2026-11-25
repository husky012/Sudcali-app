<?php

namespace App\Http\Requests\PuntoVenta\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class GuardarUbicacionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'latitud' => ['required', 'numeric', 'between:-85,85'],
            'longitud' => ['required', 'numeric', 'between:-180,180'],
            'precision' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
        ];
    }
}
