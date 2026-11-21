<?php

namespace App\Http\Requests\PuntoVenta\Admin;

use App\Models\Inventario;
use App\Models\Producto;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardarVarianteRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'talla' => trim((string) $this->input('talla')),
            'color' => trim((string) $this->input('color')),
            'codigo_barras' => trim((string) $this->input('codigo_barras')) ?: null,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Producto $producto */
        $producto = $this->route('producto');
        /** @var Inventario|null $variante */
        $variante = $this->route('variante');

        return [
            'talla' => [
                'required', 'string', 'max:20',
                Rule::unique('inventario', 'talla')
                    ->where('producto_id', $producto->id)
                    ->where('color', $this->input('color'))
                    ->ignore($variante),
            ],
            'color' => ['required', 'string', 'max:50'],
            'codigo_barras' => ['nullable', 'string', 'max:50', 'alpha_num', Rule::unique('inventario', 'codigo_barras')->ignore($variante)],
            'stock' => [$variante ? 'prohibited' : 'required', 'integer', 'min:0', 'max:100000'],
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
            'talla.unique' => 'Este producto ya tiene esa talla en ese color.',
            'codigo_barras.unique' => 'Ese código de barras ya está asignado a otra variante.',
            'stock.prohibited' => 'Ajusta las existencias desde la sección de Inventario.',
        ];
    }
}
