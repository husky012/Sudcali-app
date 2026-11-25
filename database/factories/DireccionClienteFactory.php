<?php

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\DireccionCliente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DireccionCliente>
 */
class DireccionClienteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cliente_id' => Cliente::factory(),
            'calle_numero' => fake()->streetAddress(),
            'colonia' => fake()->randomElement(['Centro', 'El Esterito', 'Pueblo Nuevo', 'Los Olivos', 'Chametla']),
            'codigo_postal' => fake()->numerify('230##'),
            'ciudad' => 'La Paz',
            'estado' => 'Baja California Sur',
            'referencias' => fake()->optional()->sentence(),
            'es_principal' => false,
        ];
    }

    /**
     * Indicate that the customer marked the address on the map.
     */
    public function ubicada(float $latitud = 24.1265, float $longitud = -110.3085): static
    {
        return $this->state(['latitud' => $latitud, 'longitud' => $longitud]);
    }

    /**
     * Indicate that this is the customer's default address.
     */
    public function principal(): static
    {
        return $this->state(fn (array $attributes) => [
            'es_principal' => true,
        ]);
    }
}
