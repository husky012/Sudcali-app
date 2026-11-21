<?php

namespace App\Http\Controllers\Tienda;

use App\Actions\Tienda\Carrito;
use App\Actions\Tienda\RealizarPedido;
use App\Enums\MetodoPago;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tienda\RealizarPedidoRequest;
use App\Models\Cliente;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(private Carrito $carrito) {}

    /**
     * Confirmación del pedido: dirección de envío y forma de pago.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $renglones = $this->carrito->renglones();

        if ($renglones->isEmpty()) {
            return to_route('carrito')->with('aviso', 'Tu carrito está vacío.');
        }

        /** @var Cliente $cliente */
        $cliente = $request->user('cliente');

        return view('checkout', [
            'renglones' => $renglones,
            'totales' => Carrito::totales($renglones),
            'direcciones' => $cliente->direcciones()->orderByDesc('es_principal')->latest()->get(),
        ]);
    }

    /**
     * Registra el pedido y vacía el carrito.
     */
    public function store(RealizarPedidoRequest $request, RealizarPedido $realizarPedido): RedirectResponse
    {
        /** @var Cliente $cliente */
        $cliente = $request->user('cliente');

        $direccion = $request->validated('direccion_id') === 'nueva'
            ? $cliente->direcciones()->create([
                ...$request->safe()->only(['calle_numero', 'colonia', 'codigo_postal', 'referencias']),
                'es_principal' => ! $cliente->direcciones()->exists(),
            ])
            : $cliente->direcciones()->findOrFail($request->validated('direccion_id'));

        $pedido = $realizarPedido->handle(
            $cliente,
            $direccion,
            $this->carrito->contenido(),
            MetodoPago::from($request->validated('metodo_pago')),
        );

        $this->carrito->vaciar();

        return to_route('pedidos.show', $pedido)
            ->with('exito', '¡Gracias por tu compra! Recibimos tu pedido.');
    }
}
