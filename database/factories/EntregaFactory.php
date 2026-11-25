<?php

namespace Database\Factories;

use App\Enums\EstadoEnvio;
use App\Models\Empleado;
use App\Models\Entrega;
use App\Models\Venta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Entrega>
 */
class EntregaFactory extends Factory
{
    /**
     * Define the model's default state (pedido en camino, con ubicación reciente).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'venta_id' => Venta::factory()->enLinea()->state(['estado_envio' => EstadoEnvio::Enviado]),
            'empleado_id' => Empleado::factory(),
            'latitud' => fake()->latitude(24.08, 24.17),
            'longitud' => fake()->longitude(-110.36, -110.28),
            'precision_metros' => 15,
            'ubicacion_en' => now(),
            'foto' => null,
            'entregado_en' => null,
        ];
    }

    /**
     * Indicate that the order was delivered with a photo.
     */
    public function entregada(string $foto = 'entregas/foto.jpg'): static
    {
        return $this->state([
            'venta_id' => Venta::factory()->enLinea()->state(['estado_envio' => EstadoEnvio::Entregado]),
            'latitud' => null,
            'longitud' => null,
            'precision_metros' => null,
            'ubicacion_en' => null,
            'foto' => $foto,
            'entregado_en' => now(),
        ]);
    }
}
