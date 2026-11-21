<?php

namespace Database\Factories;

use App\Enums\EstadoEnvio;
use App\Enums\MetodoPago;
use App\Enums\OrigenVenta;
use App\Models\CorteCaja;
use App\Models\DireccionCliente;
use App\Models\Venta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Venta>
 */
class VentaFactory extends Factory
{
    /**
     * Define the model's default state (venta en tienda).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomElement([250, 500, 650, 900]);

        return [
            'origen' => OrigenVenta::Tienda,
            'corte_caja_id' => CorteCaja::factory(),
            'empleado_id' => fn (array $attributes) => CorteCaja::find($attributes['corte_caja_id'])->empleado_id,
            'direccion_envio_id' => null,
            'cliente_id' => null,
            'subtotal' => $subtotal,
            'costo_envio' => 0,
            'total' => $subtotal,
            'metodo_pago' => fake()->randomElement([MetodoPago::Efectivo, MetodoPago::Tarjeta]),
            'estado_envio' => null,
        ];
    }

    /**
     * Indicate that the sale was made through the online store.
     */
    public function enLinea(): static
    {
        return $this->state(function (array $attributes) {
            $costoEnvio = 99;

            return [
                'origen' => OrigenVenta::EnLinea,
                'corte_caja_id' => null,
                'empleado_id' => null,
                'direccion_envio_id' => DireccionCliente::factory(),
                'cliente_id' => fn (array $attributes) => DireccionCliente::find($attributes['direccion_envio_id'])->cliente_id,
                'costo_envio' => $costoEnvio,
                'total' => $attributes['subtotal'] + $costoEnvio,
                'metodo_pago' => MetodoPago::Tarjeta,
                'estado_envio' => EstadoEnvio::Pendiente,
            ];
        });
    }
}
