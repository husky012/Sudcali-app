<?php

namespace Database\Seeders;

use App\Actions\Tienda\Carrito;
use App\Enums\EstadoEnvio;
use App\Enums\MetodoPago;
use App\Enums\OrigenVenta;
use App\Models\Cliente;
use App\Models\DireccionCliente;
use App\Models\Empleado;
use App\Models\Inventario;
use App\Models\Venta;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Historial de ventas de los últimos 6 meses para probar las estadísticas.
 *
 * Solo para desarrollo: NO descuenta existencias (es historial ficticio).
 * Ejecutar: php artisan db:seed --class=VentasDemoSeeder
 */
class VentasDemoSeeder extends Seeder
{
    private const DIAS = 180;

    public function run(): void
    {
        $variantes = Inventario::with('producto')
            ->whereHas('producto', fn ($producto) => $producto->where('activo', true))
            ->get();
        $empleados = Empleado::where('activo', true)->get();

        if ($variantes->isEmpty() || $empleados->isEmpty()) {
            $this->command?->warn('Primero corre CatalogoSeeder y SucursalEmpleadoSeeder.');

            return;
        }

        // Algunos productos se venden mucho más que otros para que el ranking tenga sentido.
        $populares = $variantes->shuffle()->take(12);

        $clientes = Cliente::factory()->count(8)->create(['password' => Str::password(32)])
            ->each(fn (Cliente $cliente) => DireccionCliente::factory()->principal()->for($cliente)->create());

        DB::transaction(function () use ($variantes, $populares, $empleados, $clientes) {
            $hoy = CarbonImmutable::today();

            for ($dia = self::DIAS; $dia >= 0; $dia--) {
                $fecha = $hoy->subDays($dia);
                $esFinDeSemana = $fecha->isWeekend();
                // Ventas crecientes en el tiempo + más movimiento en fin de semana.
                $ventasDelDia = random_int(0, 3) + ($esFinDeSemana ? 3 : 0) + intdiv(self::DIAS - $dia, 60);

                for ($i = 0; $i < $ventasDelDia; $i++) {
                    $enLinea = random_int(1, 100) <= 35;
                    $momento = $fecha->setTime(random_int(10, 20), random_int(0, 59));

                    $renglones = collect(range(1, random_int(1, 3)))->map(function () use ($variantes, $populares) {
                        $variante = random_int(1, 100) <= 60 ? $populares->random() : $variantes->random();
                        $cantidad = random_int(1, 2);

                        return [
                            'inventario_id' => $variante->id,
                            'cantidad' => $cantidad,
                            'precio_unitario' => (float) $variante->producto->precio,
                            'subtotal' => round((float) $variante->producto->precio * $cantidad, 2),
                        ];
                    })->unique('inventario_id');

                    $subtotal = round($renglones->sum('subtotal'), 2);

                    if ($enLinea) {
                        $cliente = $clientes->random();
                        $envio = $subtotal >= Carrito::ENVIO_GRATIS_DESDE ? 0 : Carrito::COSTO_ENVIO;
                        $estado = match (true) {
                            $dia > 7 => random_int(1, 100) <= 92 ? EstadoEnvio::Entregado : EstadoEnvio::Cancelado,
                            $dia > 2 => EstadoEnvio::Enviado,
                            default => EstadoEnvio::Pendiente,
                        };

                        $venta = Venta::create([
                            'origen' => OrigenVenta::EnLinea,
                            'cliente_id' => $cliente->id,
                            'direccion_envio_id' => $cliente->direcciones->first()->id,
                            'subtotal' => $subtotal,
                            'costo_envio' => $envio,
                            'total' => $subtotal + $envio,
                            'metodo_pago' => random_int(1, 100) <= 60 ? MetodoPago::Transferencia : MetodoPago::Efectivo,
                            'estado_envio' => $estado,
                        ]);
                    } else {
                        $venta = Venta::create([
                            'origen' => OrigenVenta::Tienda,
                            'empleado_id' => $empleados->random()->id,
                            'subtotal' => $subtotal,
                            'costo_envio' => 0,
                            'total' => $subtotal,
                            'metodo_pago' => random_int(1, 100) <= 55 ? MetodoPago::Efectivo : MetodoPago::Tarjeta,
                        ]);
                    }

                    $venta->detalles()->createMany($renglones->all());

                    $venta->forceFill(['created_at' => $momento, 'updated_at' => $momento])->saveQuietly();
                    $venta->detalles()->update(['created_at' => $momento, 'updated_at' => $momento]);
                }
            }
        });
    }
}
