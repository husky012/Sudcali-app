<?php

namespace Database\Factories;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Producto>
 */
class ProductoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $titulo = 'Playera '.ucfirst(fake()->unique()->words(2, true));

        return [
            'categoria_id' => Categoria::factory(),
            'titulo' => $titulo,
            'slug' => Str::slug($titulo),
            'descripcion' => fake()->sentence(),
            'precio' => fake()->randomElement([250, 350, 450, 650]),
            'imagen' => null,
            'activo' => true,
        ];
    }

    /**
     * Indicate that the product is hidden from the catalog.
     */
    public function inactivo(): static
    {
        return $this->state(fn (array $attributes) => [
            'activo' => false,
        ]);
    }
}
