<?php

namespace App\Http\Requests\PuntoVenta\Admin;

use App\Enums\PuestoEmpleado;
use App\Models\Empleado;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;

class GuardarEmpleadoRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'correo' => Str::lower(trim((string) $this->input('correo'))),
            'telefono' => preg_replace('/\D/', '', (string) $this->input('telefono')),
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
        /** @var Empleado|null $empleado */
        $empleado = $this->route('empleado');

        return [
            'nombre' => ['required', 'string', 'max:100'],
            'apellido_paterno' => ['required', 'string', 'max:100'],
            'apellido_materno' => ['nullable', 'string', 'max:100'],
            'puesto' => ['required', new Enum(PuestoEmpleado::class)],
            'sucursal_id' => ['required', 'integer', Rule::exists('sucursales', 'id')],
            'fecha_contratacion' => ['required', 'date', 'before_or_equal:today'],
            'telefono' => ['required', 'digits:10', Rule::unique('empleados', 'telefono')->ignore($empleado)],
            'correo' => ['required', 'email', 'max:255', Rule::unique('empleados', 'correo')->ignore($empleado)],
            'password' => [$empleado ? 'nullable' : 'required', 'confirmed', Password::min(8)],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'extensions:jpg,jpeg,png,webp', 'max:2048'],
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
            'apellido_paterno' => 'apellido paterno',
            'apellido_materno' => 'apellido materno',
            'sucursal_id' => 'sucursal',
            'fecha_contratacion' => 'fecha de contratación',
            'telefono' => 'teléfono',
            'password' => 'contraseña',
        ];
    }
}
