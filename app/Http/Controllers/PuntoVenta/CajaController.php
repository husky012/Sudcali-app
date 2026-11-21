<?php

namespace App\Http\Controllers\PuntoVenta;

use App\Actions\PuntoVenta\CerrarCorteCaja;
use App\Enums\EstadoCorte;
use App\Enums\MetodoPago;
use App\Http\Controllers\Controller;
use App\Http\Requests\PuntoVenta\AbrirCajaRequest;
use App\Http\Requests\PuntoVenta\CerrarCajaRequest;
use App\Models\CorteCaja;
use App\Models\Empleado;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CajaController extends Controller
{
    /**
     * Formulario para abrir caja con el fondo inicial.
     */
    public function create(Request $request): View|RedirectResponse
    {
        if ($this->empleado($request)->corteAbierto !== null) {
            return to_route('pos.ventas.create');
        }

        return view('pos.caja.abrir');
    }

    /**
     * Abre un nuevo corte de caja.
     */
    public function store(AbrirCajaRequest $request): RedirectResponse
    {
        $empleado = $this->empleado($request);

        if ($empleado->corteAbierto === null) {
            $corte = $empleado->cortesCaja()->create([
                'sucursal_id' => $empleado->sucursal_id,
                'fecha_apertura' => now(),
                'monto_inicial' => $request->validated('monto_inicial'),
                'estado' => EstadoCorte::Abierto,
            ]);

            $empleado->setRelation('corteAbierto', $corte);
        }

        return to_route('pos.ventas.create')->with('exito', 'Caja abierta. ¡Buenas ventas!');
    }

    /**
     * Resumen del turno antes de cerrar caja.
     */
    public function edit(Request $request): View
    {
        $corte = $this->empleado($request)->corteAbierto;

        return view('pos.caja.cerrar', [
            'corte' => $corte,
            'totalVentas' => $corte->ventas()->count(),
            'efectivoEsperado' => (float) $corte->monto_inicial + (float) $corte->ventas_efectivo,
        ]);
    }

    /**
     * Cierra el corte con el efectivo contado.
     */
    public function update(CerrarCajaRequest $request, CerrarCorteCaja $cerrarCorteCaja): RedirectResponse
    {
        $corte = $cerrarCorteCaja->handle(
            $this->empleado($request)->corteAbierto,
            (float) $request->validated('monto_final_real'),
        );

        return to_route('pos.caja.show', $corte)->with('exito', 'Caja cerrada correctamente.');
    }

    /**
     * Muestra el reporte de un corte de caja.
     */
    public function show(Request $request, CorteCaja $corte): View
    {
        abort_unless($corte->sucursal_id === $this->empleado($request)->sucursal_id, 403);

        $corte->load(['empleado', 'sucursal']);

        return view('pos.caja.resumen', [
            'corte' => $corte,
            'ventasPorMetodo' => $corte->ventas()
                ->selectRaw('metodo_pago, count(*) as cantidad, sum(total) as importe')
                ->groupBy('metodo_pago')
                ->get()
                ->keyBy(fn ($fila) => $fila->metodo_pago->value),
            'metodos' => [MetodoPago::Efectivo, MetodoPago::Tarjeta],
        ]);
    }

    /**
     * Empleado autenticado en el punto de venta.
     */
    private function empleado(Request $request): Empleado
    {
        return $request->user('empleado');
    }
}
