<?php

namespace App\Http\Requests\Tienda;

use App\Enums\MetodoPago;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RealizarPedidoRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * Se elige una dirección guardada o se captura una nueva.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $usaNueva = $this->input('direccion_id', 'nueva') === 'nueva';

        return [
            'direccion_id' => [
                'required',
                $usaNueva ? 'in:nueva' : Rule::exists('direcciones_cliente', 'id')->where('cliente_id', $this->user('cliente')->id),
            ],
            'calle_numero' => [Rule::requiredIf($usaNueva), 'nullable', 'string', 'max:150'],
            'colonia' => [Rule::requiredIf($usaNueva), 'nullable', 'string', 'max:100'],
            'codigo_postal' => [Rule::requiredIf($usaNueva), 'nullable', 'digits:5', 'starts_with:23'],
            'referencias' => ['nullable', 'string', 'max:255'],
            'metodo_pago' => ['required', Rule::in([MetodoPago::Transferencia->value, MetodoPago::Efectivo->value])],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'direccion_id' => 'dirección de envío',
            'calle_numero' => 'calle y número',
            'codigo_postal' => 'código postal',
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
            'codigo_postal.starts_with' => 'Por ahora solo enviamos dentro de Baja California Sur (C.P. 23xxx).',
            'direccion_id.exists' => 'Elige una de tus direcciones guardadas.',
        ];
    }
}
