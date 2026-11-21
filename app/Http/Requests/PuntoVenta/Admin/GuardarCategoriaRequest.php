<?php

namespace App\Http\Requests\PuntoVenta\Admin;

use App\Models\Categoria;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class GuardarCategoriaRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $nombre = trim((string) $this->input('nombre'));

        $this->merge([
            'nombre' => $nombre,
            'slug' => Str::slug($nombre),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Categoria|null $categoria */
        $categoria = $this->route('categoria');

        return [
            'nombre' => ['required', 'string', 'max:60', Rule::unique('categorias', 'nombre')->ignore($categoria)],
            'slug' => ['required', Rule::unique('categorias', 'slug')->ignore($categoria)],
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
            'slug.unique' => 'Ya existe una categoría con un nombre equivalente.',
            'slug.required' => 'El nombre debe contener letras o números.',
        ];
    }
}
