<?php

namespace App\Actions\PuntoVenta;

use App\Enums\MetodoPago;
use App\Enums\OrigenVenta;
use App\Models\CorteCaja;
use App\Models\Inventario;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RegistrarVenta
{
    /**
     * Registra una venta de mostrador, descuenta el stock y la suma al corte de caja.
     *
     * Los precios se toman siempre de la base de datos, nunca del navegador.
     *
     * @param  array<int, array{inventario_id: int, cantidad: int}>  $articulos
     *
     * @throws ValidationException
     */
    public function handle(CorteCaja $corte, array $articulos, MetodoPago $metodoPago, ?float $montoRecibido = null): Venta
    {
        return DB::transaction(function () use ($corte, $articulos, $metodoPago, $montoRecibido) {
            $variantes = Inventario::with('producto')
                ->whereIn('id', array_column($articulos, 'inventario_id'))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $renglones = [];
            $subtotal = 0;

            foreach ($articulos as $indice => $articulo) {
                $variante = $variantes[$articulo['inventario_id']];

                if (! $variante->producto->activo) {
                    throw ValidationException::withMessages([
                        "articulos.{$indice}.inventario_id" => "{$variante->producto->titulo} ya no está a la venta.",
                    ]);
                }

                if ($variante->stock < $articulo['cantidad']) {
                    throw ValidationException::withMessages([
                        "articulos.{$indice}.cantidad" => "Solo quedan {$variante->stock} de {$variante->producto->titulo} ({$variante->talla}).",
                    ]);
                }

                $precioUnitario = (float) $variante->producto->precio;
                $importe = round($precioUnitario * $articulo['cantidad'], 2);
                $subtotal += $importe;

                $renglones[] = [
                    'variante' => $variante,
                    'cantidad' => $articulo['cantidad'],
                    'precio_unitario' => $precioUnitario,
                    'subtotal' => $importe,
                ];
            }

            if ($metodoPago === MetodoPago::Efectivo && $montoRecibido < $subtotal) {
                throw ValidationException::withMessages([
                    'monto_recibido' => 'El efectivo recibido no cubre el total de $'.number_format($subtotal, 2).'.',
                ]);
            }

            $venta = Venta::create([
                'origen' => OrigenVenta::Tienda,
                'empleado_id' => $corte->empleado_id,
                'corte_caja_id' => $corte->id,
                'subtotal' => $subtotal,
                'costo_envio' => 0,
                'total' => $subtotal,
                'metodo_pago' => $metodoPago,
            ]);

            foreach ($renglones as $renglon) {
                $venta->detalles()->create([
                    'inventario_id' => $renglon['variante']->id,
                    'cantidad' => $renglon['cantidad'],
                    'precio_unitario' => $renglon['precio_unitario'],
                    'subtotal' => $renglon['subtotal'],
                ]);

                $renglon['variante']->decrement('stock', $renglon['cantidad']);
            }

            $corte->increment(
                $metodoPago === MetodoPago::Efectivo ? 'ventas_efectivo' : 'ventas_tarjeta',
                $subtotal,
            );

            return $venta;
        });
    }
}
