<?php

namespace App\Http\Controllers\PuntoVenta\Admin;

use App\Actions\Tienda\ActualizarEstadoPedido;
use App\Enums\EstadoEnvio;
use App\Enums\OrigenVenta;
use App\Http\Controllers\Controller;
use App\Models\Venta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PedidoEnLineaController extends Controller
{
    /**
     * Pedidos de la tienda en línea, primero los que requieren atención.
     */
    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'estado' => ['nullable', Rule::enum(EstadoEnvio::class)],
        ]);

        $pedidos = Venta::query()
            ->where('origen', OrigenVenta::EnLinea)
            ->with(['cliente', 'direccionEnvio', 'detalles.inventario.producto'])
            ->when($filtros['estado'] ?? null, fn (Builder $consulta, string $estado) => $consulta->where('estado_envio', $estado))
            ->orderByRaw("case when estado_envio in ('pendiente', 'preparando', 'enviado') then 0 else 1 end")
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pos.admin.pedidos.index', [
            'pedidos' => $pedidos,
            'filtros' => $filtros,
            'estados' => EstadoEnvio::cases(),
            'porAtender' => Venta::where('origen', OrigenVenta::EnLinea)->where('estado_envio', EstadoEnvio::Pendiente)->count(),
        ]);
    }

    /**
     * Cambia el estado de envío de un pedido.
     */
    public function update(Request $request, Venta $pedido, ActualizarEstadoPedido $actualizarEstado): RedirectResponse
    {
        abort_unless($pedido->origen === OrigenVenta::EnLinea, 404);

        $datos = $request->validate([
            'estado_envio' => ['required', Rule::enum(EstadoEnvio::class)],
        ]);

        $actualizarEstado->handle($pedido, EstadoEnvio::from($datos['estado_envio']));

        return back()->with('exito', "Pedido #{$pedido->id}: {$pedido->estado_envio->value}.");
    }
}
