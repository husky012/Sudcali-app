<?php

namespace App\Actions\Tienda;

use App\Enums\EstadoEnvio;
use App\Enums\MetodoPago;
use App\Enums\OrigenVenta;
use App\Models\Cliente;
use App\Models\DireccionCliente;
use App\Models\Inventario;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RealizarPedido
{
    /**
     * Convierte el contenido del carrito en un pedido en línea pendiente de envío.
     *
     * Vuelve a validar existencias y precios dentro de la transacción.
     *
     * @param  array<int, int>  $contenido  [inventario_id => cantidad]
     *
     * @throws ValidationException
     */
    public function handle(Cliente $cliente, DireccionCliente $direccion, array $contenido, MetodoPago $metodoPago): Venta
    {
        if ($contenido === []) {
            throw ValidationException::withMessages(['carrito' => 'Tu carrito está vacío.']);
        }

        return DB::transaction(function () use ($cliente, $direccion, $contenido, $metodoPago) {
            $variantes = Inventario::with('producto')
                ->whereIn('id', array_keys($contenido))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $renglones = [];

            foreach ($contenido as $inventarioId => $cantidad) {
                $variante = $variantes->get($inventarioId);

                if ($variante === null || ! $variante->producto->activo) {
                    throw ValidationException::withMessages([
                        'carrito' => 'Uno de los productos de tu carrito ya no está disponible. Revisa tu carrito.',
                    ]);
                }

                if ($variante->stock < $cantidad) {
                    throw ValidationException::withMessages([
                        'carrito' => "Solo quedan {$variante->stock} de {$variante->producto->titulo} (talla {$variante->talla}). Ajusta tu carrito.",
                    ]);
                }

                $precio = (float) $variante->producto->precio;

                $renglones[] = [
                    'variante' => $variante,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precio,
                    'importe' => round($precio * $cantidad, 2),
                ];
            }

            $totales = Carrito::totales(collect($renglones));

            $venta = Venta::create([
                'origen' => OrigenVenta::EnLinea,
                'cliente_id' => $cliente->id,
                'direccion_envio_id' => $direccion->id,
                'subtotal' => $totales['subtotal'],
                'costo_envio' => $totales['envio'],
                'total' => $totales['total'],
                'metodo_pago' => $metodoPago,
                'estado_envio' => EstadoEnvio::Pendiente,
            ]);

            foreach ($renglones as $renglon) {
                $venta->detalles()->create([
                    'inventario_id' => $renglon['variante']->id,
                    'cantidad' => $renglon['cantidad'],
                    'precio_unitario' => $renglon['precio_unitario'],
                    'subtotal' => $renglon['importe'],
                ]);

                $renglon['variante']->decrement('stock', $renglon['cantidad']);
            }

            return $venta;
        });
    }
}
