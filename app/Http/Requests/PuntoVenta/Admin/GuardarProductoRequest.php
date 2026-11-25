<?php

namespace App\Http\Requests\PuntoVenta\Admin;

use App\Models\Producto;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class GuardarProductoRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $titulo = trim((string) $this->input('titulo'));

        $this->merge([
            'titulo' => $titulo,
            'slug' => Str::slug($titulo),
            'activo' => $this->boolean('activo'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Producto|null $producto */
        $producto = $this->route('producto');

        return [
            'categoria_id' => ['required', 'integer', Rule::exists('categorias', 'id')],
            'titulo' => ['required', 'string', 'max:120'],
            'slug' => ['required', Rule::unique('productos', 'slug')->ignore($producto)],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'precio' => ['required', 'numeric', 'min:1', 'max:99999.99', 'decimal:0,2'],
            'imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'extensions:jpg,jpeg,png,webp', 'max:4096'],
            'activo' => ['boolean'],
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
            'categoria_id' => 'categoría',
            'titulo' => 'nombre del producto',
            'descripcion' => 'descripción',
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
            'slug.unique' => 'Ya existe un producto con ese nombre.',
        ];
    }
}
