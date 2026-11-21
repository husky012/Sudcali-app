<?php

namespace Database\Factories;

use App\Models\DetalleVenta;
use App\Models\Inventario;
use App\Models\Venta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DetalleVenta>
 */
class DetalleVentaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $cantidad = fake()->numberBetween(1, 3);
        $precioUnitario = fake()->randomElement([250, 650]);

        return [
            'venta_id' => Venta::factory(),
            'inventario_id' => Inventario::factory(),
            'cantidad' => $cantidad,
            'precio_unitario' => $precioUnitario,
            'subtotal' => $cantidad * $precioUnitario,
        ];
    }
}
