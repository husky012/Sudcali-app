<?php

namespace App\Actions\PuntoVenta;

use App\Enums\EstadoCorte;
use App\Models\CorteCaja;

class CerrarCorteCaja
{
    /**
     * Cierra el corte comparando el efectivo contado contra el esperado.
     *
     * Esperado = fondo inicial + ventas en efectivo (las ventas con tarjeta no entran a caja).
     */
    public function handle(CorteCaja $corte, float $montoFinalReal): CorteCaja
    {
        $esperado = round((float) $corte->monto_inicial + (float) $corte->ventas_efectivo, 2);

        $corte->update([
            'fecha_cierre' => now(),
            'monto_final_esperado' => $esperado,
            'monto_final_real' => $montoFinalReal,
            'diferencia' => round($montoFinalReal - $esperado, 2),
            'estado' => EstadoCorte::Cerrado,
        ]);

        return $corte;
    }
}
