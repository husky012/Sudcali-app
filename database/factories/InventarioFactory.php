<?php

namespace Database\Factories;

use App\Models\Inventario;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inventario>
 */
class InventarioFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'producto_id' => Producto::factory(),
            'talla' => fake()->randomElement(['CH', 'M', 'G', 'XG']),
            'color' => fake()->unique()->safeColorName(),
            'codigo_barras' => fake()->unique()->ean13(),
            'stock' => fake()->numberBetween(0, 50),
        ];
    }
}
