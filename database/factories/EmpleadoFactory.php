<?php

namespace Database\Factories;

use App\Enums\PuestoEmpleado;
use App\Models\Empleado;
use App\Models\Sucursal;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Empleado>
 */
class EmpleadoFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sucursal_id' => Sucursal::factory(),
            'nombre' => fake()->firstName(),
            'apellido_paterno' => fake()->lastName(),
            'apellido_materno' => fake()->lastName(),
            'puesto' => fake()->randomElement(PuestoEmpleado::cases()),
            'fecha_contratacion' => fake()->dateTimeBetween('-3 years'),
            'foto' => null,
            'telefono' => fake()->unique()->numerify('612#######'),
            'correo' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'activo' => true,
        ];
    }

    /**
     * Indicate that the employee works as a salesperson.
     */
    public function vendedor(): static
    {
        return $this->state(fn (array $attributes) => [
            'puesto' => PuestoEmpleado::Vendedor,
        ]);
    }
}
