<?php

namespace App\Http\Controllers\PuntoVenta\Admin;

use App\Actions\Tienda\CalcularRuta;
use App\Actions\Tienda\RegistrarEntrega;
use App\Enums\EstadoEnvio;
use App\Enums\OrigenVenta;
use App\Http\Controllers\Controller;
use App\Http\Requests\PuntoVenta\Admin\GuardarUbicacionRequest;
use App\Http\Requests\PuntoVenta\Admin\RegistrarEntregaRequest;
use App\Http\Requests\Tienda\GuardarDomicilioRequest;
use App\Models\Venta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EntregaController extends Controller
{
    /**
     * Pantalla de reparto: compartir la ubicación y tomar la foto de la entrega.
     */
    public function show(Venta $pedido): View|RedirectResponse
    {
        abort_unless($pedido->origen === OrigenVenta::EnLinea, 404);

        if ($pedido->estado_envio !== EstadoEnvio::Enviado) {
            return to_route('pos.admin.pedidos.index')
                ->with('aviso', "El pedido #{$pedido->id} no va en camino.");
        }

        $pedido->load(['cliente', 'direccionEnvio']);

        return view('pos.admin.pedidos.reparto', ['pedido' => $pedido]);
    }

    /**
     * Guarda la última ubicación de quien lleva el pedido y responde el camino al domicilio.
     */
    public function ubicacion(GuardarUbicacionRequest $request, Venta $pedido, CalcularRuta $calcularRuta): JsonResponse
    {
        abort_unless($pedido->origen === OrigenVenta::EnLinea, 404);
        abort_unless($pedido->estado_envio === EstadoEnvio::Enviado, 409);

        $precision = $request->validated('precision');

        $pedido->entrega()->updateOrCreate([], [
            'empleado_id' => $request->user('empleado')->id,
            'latitud' => $request->validated('latitud'),
            'longitud' => $request->validated('longitud'),
            'precision_metros' => $precision === null ? null : (int) $precision,
            'ubicacion_en' => now(),
        ]);

        $pedido->load(['entrega', 'direccionEnvio']);

        return response()->json([
            'destino' => $pedido->direccionEnvio?->coordenadas(),
            'ruta' => $calcularRuta->handle($pedido),
        ]);
    }

    /**
     * Marca el domicilio en el mapa si el cliente no lo hizo.
     */
    public function domicilio(GuardarDomicilioRequest $request, Venta $pedido): Response
    {
        abort_unless($pedido->origen === OrigenVenta::EnLinea && $pedido->direccionEnvio !== null, 404);
        abort_unless($pedido->estado_envio === EstadoEnvio::Enviado && $pedido->direccionEnvio->coordenadas() === null, 409);

        $pedido->direccionEnvio->update($request->validated());
        CalcularRuta::olvidar($pedido->id);

        return response()->noContent();
    }

    /**
     * Deja de compartir la ubicación.
     */
    public function olvidarUbicacion(Venta $pedido): Response
    {
        $pedido->entrega?->olvidarUbicacion();

        return response()->noContent();
    }

    /**
     * Entrega el pedido con su foto.
     */
    public function store(RegistrarEntregaRequest $request, Venta $pedido, RegistrarEntrega $registrarEntrega): RedirectResponse
    {
        abort_unless($pedido->origen === OrigenVenta::EnLinea, 404);

        $registrarEntrega->handle($pedido, $request->user('empleado'), $request->file('foto'));

        return to_route('pos.admin.pedidos.index')
            ->with('exito', "Pedido #{$pedido->id}: entregado con foto.");
    }

    /**
     * Foto de la entrega, que vive fuera de la carpeta pública.
     */
    public function foto(Venta $pedido): StreamedResponse
    {
        $foto = $pedido->entrega?->foto;

        abort_unless($foto && Storage::disk('local')->exists($foto), 404);

        return Storage::disk('local')->response($foto, headers: ['Cache-Control' => 'private, no-store']);
    }
}
