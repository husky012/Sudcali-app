<?php

namespace App\Http\Requests\PuntoVenta\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AjustarStockRequest extends FormRequest
{
    /**
     * Cada fila del inventario muestra sus propios errores.
     */
    protected function prepareForValidation(): void
    {
        $this->errorBag = 'ajuste'.$this->route('variante')->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'operacion' => ['required', 'in:entrada,salida,conteo'],
            'cantidad' => ['required', 'integer', 'min:0', 'max:100000'],
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
            'operacion.in' => 'Elige entrada, salida o conteo físico.',
        ];
    }
}
