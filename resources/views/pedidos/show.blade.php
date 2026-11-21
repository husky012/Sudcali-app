@extends('layouts.app')

@section('titulo', 'Sudcali - Pedido #'.$pedido->id)

@php
    $pasos = ['pendiente' => 'Recibido', 'preparando' => 'Preparando', 'enviado' => 'En camino', 'entregado' => 'Entregado'];
    $estadoActual = $pedido->estado_envio->value;
    $indiceActual = array_search($estadoActual, array_keys($pasos), true);
@endphp

@section('contenido')
<section class="seccion-carrito">
    <a href="{{ route('pedidos.index') }}" class="enlace-volver-pos">← Mis pedidos</a>

    <header class="encabezado-seccion">
        <h2>Pedido #{{ str_pad($pedido->id, 6, '0', STR_PAD_LEFT) }}</h2>
        <p>Realizado el {{ $pedido->created_at->format('d/m/Y \a \l\a\s H:i') }}</p>
    </header>

    @if ($estadoActual === 'cancelado')
        <p class="alerta-pos alerta-error alerta-tienda">Este pedido fue cancelado.</p>
    @else
        <ol class="seguimiento-pedido" aria-label="Seguimiento del pedido">
            @foreach ($pasos as $clave => $nombre)
                @php($indice = $loop->index)
                <li @class(['completado' => $indice < $indiceActual, 'actual' => $indice === $indiceActual])
                    @if ($indice === $indiceActual) aria-current="step" @endif>
                    <span class="punto-seguimiento" aria-hidden="true"></span>
                    {{ $nombre }}
                </li>
            @endforeach
        </ol>
    @endif

    <div class="contenedor-grid-carrito">
        <div class="lista-items-carrito">
            @foreach ($pedido->detalles as $detalle)
                <article class="tarjeta-item-carrito">
                    <figure class="imagen-item-carrito">
                        @if ($detalle->inventario->producto->imagen)
                            <img src="{{ $detalle->inventario->producto->imagen }}" alt="{{ $detalle->inventario->producto->titulo }}">
                        @endif
                    </figure>
                    <div class="detalles-item-carrito">
                        <h3>{{ $detalle->inventario->producto->titulo }}</h3>
                        <p class="meta-item">Talla: <span>{{ $detalle->inventario->talla }}</span> · {{ $detalle->cantidad }} × ${{ number_format($detalle->precio_unitario, 2) }}</p>
                    </div>
                    <p class="subtotal-item">${{ number_format($detalle->subtotal, 2) }} MXN</p>
                </article>
            @endforeach
        </div>

        <aside class="resumen-pedido">
            <h3>Resumen</h3>
            <div class="desglose-resumen">
                <div class="fila-resumen"><span>Subtotal</span><span>${{ number_format($pedido->subtotal, 2) }}</span></div>
                <div class="fila-resumen"><span>Envío</span><span>{{ (float) $pedido->costo_envio > 0 ? '$'.number_format($pedido->costo_envio, 2) : 'Gratis' }}</span></div>
                <hr class="divisor-resumen">
                <div class="fila-resumen fila-total"><span>Total</span><span class="precio-total">${{ number_format($pedido->total, 2) }} MXN</span></div>
                <div class="fila-resumen"><span>Pago</span><span>{{ $pedido->metodo_pago->value === 'efectivo' ? 'Efectivo contra entrega' : ucfirst($pedido->metodo_pago->value) }}</span></div>
            </div>

            <h3>Se envía a</h3>
            <p class="direccion-resumen">
                {{ $pedido->direccionEnvio?->calle_numero }}<br>
                Col. {{ $pedido->direccionEnvio?->colonia }}, C.P. {{ $pedido->direccionEnvio?->codigo_postal }}<br>
                {{ $pedido->direccionEnvio?->ciudad }}, {{ $pedido->direccionEnvio?->estado }}
            </p>
        </aside>
    </div>
</section>
@endsection
