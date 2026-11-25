<?php

namespace Database\Seeders;

use App\Enums\PuestoEmpleado;
use App\Models\Empleado;
use App\Models\Sucursal;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SucursalEmpleadoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $centro = Sucursal::updateOrCreate(
            ['nombre' => 'Sudcali Centro'],
            ['direccion' => 'Paseo Álvaro Obregón, Centro, La Paz, B.C.S.', 'telefono' => '6120000001'],
        );

        $sucursales = collect([
            $centro,
            Sucursal::updateOrCreate(
                ['nombre' => 'Sudcali Malecón'],
                ['direccion' => 'Malecón de La Paz, Zona Centro, La Paz, B.C.S.', 'telefono' => '6120000002'],
            ),
        ]);

        // Las contraseñas no se escriben en el repositorio: salen de DEMO_PASSWORD o se generan.
        $password = config('services.demo.password') ?: Str::password(16);

        $gerente = Empleado::firstOrCreate(
            ['correo' => 'gerente@sudcali.test'],
            [
                'sucursal_id' => $centro->id,
                'nombre' => 'Gerente',
                'apellido_paterno' => 'Sudcali',
                'puesto' => PuestoEmpleado::Gerente,
                'fecha_contratacion' => now()->subYear(),
                'telefono' => '6120000100',
                'password' => $password,
            ],
        );

        if ($gerente->wasRecentlyCreated && ! config('services.demo.password')) {
            $this->command?->warn("Contraseña de gerente@sudcali.test: {$password}");
        }

        if (Empleado::count() === 1) {
            $sucursales->each(fn (Sucursal $sucursal) => Empleado::factory()
                ->count(3)
                ->for($sucursal)
                ->create(['password' => $password]));
        }
    }
}
