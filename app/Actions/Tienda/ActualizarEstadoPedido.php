<?php

namespace App\Actions\Tienda;

use App\Enums\EstadoEnvio;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ActualizarEstadoPedido
{
    /**
     * Avanza o cancela un pedido en línea. Al cancelar, regresa las piezas al inventario.
     *
     * @throws ValidationException
     */
    public function handle(Venta $pedido, EstadoEnvio $nuevoEstado): Venta
    {
        if (in_array($pedido->estado_envio, [EstadoEnvio::Entregado, EstadoEnvio::Cancelado], true)) {
            throw ValidationException::withMessages([
                'estado_envio' => "El pedido ya está {$pedido->estado_envio->value} y no se puede modificar.",
            ]);
        }

        return DB::transaction(function () use ($pedido, $nuevoEstado) {
            if ($nuevoEstado === EstadoEnvio::Cancelado) {
                $pedido->load('detalles.inventario');

                foreach ($pedido->detalles as $detalle) {
                    $detalle->inventario->increment('stock', $detalle->cantidad);
                }
            }

            $pedido->update(['estado_envio' => $nuevoEstado]);

            return $pedido;
        });
    }
}
