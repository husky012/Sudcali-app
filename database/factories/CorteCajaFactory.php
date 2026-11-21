<?php

namespace Database\Factories;

use App\Enums\EstadoCorte;
use App\Models\CorteCaja;
use App\Models\Empleado;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CorteCaja>
 */
class CorteCajaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'empleado_id' => Empleado::factory(),
            'sucursal_id' => fn (array $attributes) => Empleado::find($attributes['empleado_id'])->sucursal_id,
            'fecha_apertura' => now()->startOfDay()->addHours(9),
            'fecha_cierre' => null,
            'monto_inicial' => 500,
            'ventas_efectivo' => 0,
            'ventas_tarjeta' => 0,
            'monto_final_esperado' => null,
            'monto_final_real' => null,
            'diferencia' => null,
            'estado' => EstadoCorte::Abierto,
        ];
    }

    /**
     * Indicate that the cash register session has been closed.
     */
    public function cerrado(): static
    {
        return $this->state(function (array $attributes) {
            $efectivo = fake()->numberBetween(1000, 5000);
            $esperado = $attributes['monto_inicial'] + $efectivo;
            $real = $esperado + fake()->randomElement([0, 0, -20, 10]);

            return [
                'fecha_cierre' => now()->startOfDay()->addHours(21),
                'ventas_efectivo' => $efectivo,
                'ventas_tarjeta' => fake()->numberBetween(500, 4000),
                'monto_final_esperado' => $esperado,
                'monto_final_real' => $real,
                'diferencia' => $real - $esperado,
                'estado' => EstadoCorte::Cerrado,
            ];
        });
    }
}
