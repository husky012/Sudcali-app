<?php

namespace App\Http\Controllers\PuntoVenta;

use App\Actions\PuntoVenta\RegistrarVenta;
use App\Enums\MetodoPago;
use App\Http\Controllers\Controller;
use App\Http\Requests\PuntoVenta\RegistrarVentaRequest;
use App\Models\Categoria;
use App\Models\Empleado;
use App\Models\Venta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VentaController extends Controller
{
    /**
     * Ventas registradas en el turno (corte) actual.
     */
    public function index(Request $request): View
    {
        $corte = $this->empleado($request)->corteAbierto;

        return view('pos.ventas.index', [
            'corte' => $corte,
            'ventas' => $corte->ventas()->withSum('detalles', 'cantidad')->latest()->get(),
        ]);
    }

    /**
     * Pantalla principal de cobro.
     */
    public function create(Request $request): View
    {
        return view('pos.ventas.create', [
            'corte' => $this->empleado($request)->corteAbierto,
            'categorias' => Categoria::orderBy('nombre')->get(),
        ]);
    }

    /**
     * Registra la venta y muestra el ticket.
     */
    public function store(RegistrarVentaRequest $request, RegistrarVenta $registrarVenta): RedirectResponse
    {
        $metodoPago = MetodoPago::from($request->validated('metodo_pago'));
        $montoRecibido = $request->validated('monto_recibido');

        $articulos = collect($request->validated('articulos'))
            ->map(fn (array $articulo) => [
                'inventario_id' => (int) $articulo['inventario_id'],
                'cantidad' => (int) $articulo['cantidad'],
            ])
            ->all();

        $venta = $registrarVenta->handle(
            $this->empleado($request)->corteAbierto,
            $articulos,
            $metodoPago,
            $montoRecibido === null ? null : (float) $montoRecibido,
        );

        $cambio = $metodoPago === MetodoPago::Efectivo
            ? round((float) $montoRecibido - (float) $venta->total, 2)
            : null;

        return to_route('pos.ventas.show', $venta)->with([
            'exito' => 'Venta registrada.',
            'monto_recibido' => $montoRecibido,
            'cambio' => $cambio,
        ]);
    }

    /**
     * Ticket de una venta de la misma sucursal.
     */
    public function show(Request $request, Venta $venta): View
    {
        $venta->load(['empleado.sucursal', 'detalles.inventario.producto']);

        abort_unless($venta->empleado?->sucursal_id === $this->empleado($request)->sucursal_id, 403);

        return view('pos.ventas.show', ['venta' => $venta]);
    }

    /**
     * Empleado autenticado en el punto de venta.
     */
    private function empleado(Request $request): Empleado
    {
        return $request->user('empleado');
    }
}
