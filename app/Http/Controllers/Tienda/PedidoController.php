<?php

namespace App\Http\Controllers\Tienda;

use App\Actions\Tienda\CalcularRuta;
use App\Enums\EstadoEnvio;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tienda\GuardarDomicilioRequest;
use App\Models\Cliente;
use App\Models\Venta;
use App\Support\BitacoraSeguridad;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
        $this->asegurarPropio($request, $pedido);

        $pedido->load(['detalles.inventario.producto', 'direccionEnvio', 'entrega']);

        return view('pedidos.show', ['pedido' => $pedido]);
    }

    /**
     * Estado, ubicación y camino para el mapa: la página lo consulta cada pocos segundos.
     */
    public function seguimiento(Request $request, Venta $pedido, CalcularRuta $calcularRuta): JsonResponse
    {
        $this->asegurarPropio($request, $pedido);

        $enCamino = $pedido->estado_envio === EstadoEnvio::Enviado;

        return response()->json([
            'estado' => $pedido->estado_envio,
            'ubicacion' => $enCamino ? $pedido->entrega?->ubicacionVigente() : null,
            'destino' => $enCamino ? $pedido->direccionEnvio?->coordenadas() : null,
            'ruta' => $enCamino ? $calcularRuta->handle($pedido) : null,
        ])->header('Cache-Control', 'private, no-store');
    }

    /**
     * Guarda el punto del domicilio que el cliente marcó en el mapa.
     */
    public function domicilio(GuardarDomicilioRequest $request, Venta $pedido): Response
    {
        $this->asegurarPropio($request, $pedido);

        abort_if(in_array($pedido->estado_envio, [EstadoEnvio::Entregado, EstadoEnvio::Cancelado], true), 409);
        abort_unless($pedido->direccionEnvio?->cliente_id === $pedido->cliente_id, 404);

        $pedido->direccionEnvio->update($request->validated());
        CalcularRuta::olvidar($pedido->id);

        return response()->noContent();
    }

    /**
     * Foto de la entrega de un pedido propio.
     */
    public function foto(Request $request, Venta $pedido): StreamedResponse
    {
        $this->asegurarPropio($request, $pedido);

        $foto = $pedido->entrega?->foto;

        abort_unless($foto && Storage::disk('local')->exists($foto), 404);

        return Storage::disk('local')->response($foto, headers: ['Cache-Control' => 'private, no-store']);
    }

    /**
     * Responde 404 y lo anota en la bitácora si el pedido es de otra persona.
     */
    private function asegurarPropio(Request $request, Venta $pedido): void
    {
        if ($pedido->cliente_id !== $request->user('cliente')->id) {
            BitacoraSeguridad::registrar('pedido_ajeno', [
                'cliente_id' => $request->user('cliente')->id,
                'pedido_id' => $pedido->id,
            ]);

            abort(404);
        }
    }
}
