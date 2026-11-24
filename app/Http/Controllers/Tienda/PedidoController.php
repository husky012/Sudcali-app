<?php

namespace App\Http\Controllers\Tienda;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Venta;
use App\Support\BitacoraSeguridad;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PedidoController extends Controller
{
    /**
     * Historial de pedidos del cliente.
     */
    public function index(Request $request): View
    {
        /** @var Cliente $cliente */
        $cliente = $request->user('cliente');

        return view('pedidos.index', [
            'pedidos' => $cliente->ventas()->withSum('detalles', 'cantidad')->latest()->paginate(10),
        ]);
    }

    /**
     * Detalle y seguimiento de un pedido propio.
     */
    public function show(Request $request, Venta $pedido): View
    {
        if ($pedido->cliente_id !== $request->user('cliente')->id) {
            BitacoraSeguridad::registrar('pedido_ajeno', [
                'cliente_id' => $request->user('cliente')->id,
                'pedido_id' => $pedido->id,
            ]);

            abort(404);
        }

        $pedido->load(['detalles.inventario.producto', 'direccionEnvio']);

        return view('pedidos.show', ['pedido' => $pedido]);
    }
}
