<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\DireccionCliente;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Str::password(32),
        ]);

        $this->call([
            CatalogoSeeder::class,
            SucursalEmpleadoSeeder::class,
        ]);

        $cliente = Cliente::factory()->create([
            'nombre' => 'Cliente',
            'apellido_paterno' => 'Demo',
            'correo' => 'cliente@sudcali.test',
            'password' => config('services.demo.password') ?: Str::password(16),
        ]);

        DireccionCliente::factory()->principal()->for($cliente)->create();
    }
}
