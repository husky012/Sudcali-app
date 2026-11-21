<?php

namespace App\Http\Requests\PuntoVenta;

use App\Enums\MetodoPago;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegistrarVentaRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'articulos' => ['required', 'array', 'min:1', 'max:100'],
            'articulos.*.inventario_id' => ['required', 'integer', 'distinct', Rule::exists('inventario', 'id')],
            'articulos.*.cantidad' => ['required', 'integer', 'min:1', 'max:999'],
            'metodo_pago' => ['required', Rule::in([MetodoPago::Efectivo->value, MetodoPago::Tarjeta->value])],
            'monto_recibido' => ['nullable', 'required_if:metodo_pago,'.MetodoPago::Efectivo->value, 'numeric', 'min:0', 'max:1000000'],
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
            'articulos.required' => 'Agrega al menos un artículo a la venta.',
            'monto_recibido.required_if' => 'Captura cuánto efectivo entregó el cliente.',
        ];
    }
}
