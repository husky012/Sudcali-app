@extends('layouts.pos')

@section('titulo', 'Pedidos en línea')

@php
    $siguientes = [
        'pendiente' => ['preparando' => 'Marcar en preparación', 'cancelado' => 'Cancelar'],
        'preparando' => ['enviado' => 'Marcar como enviado', 'cancelado' => 'Cancelar'],
        'enviado' => ['entregado' => 'Marcar como entregado'],
        'entregado' => [],
        'cancelado' => [],
    ];
@endphp

@section('contenido')
<section class="seccion-pos-ancha seccion-admin-pos">
    <header class="encabezado-admin-pos">
        <div class="encabezado-pagina-pos">
            <h2>Pedidos en línea</h2>
            <p>{{ $porAtender }} {{ $porAtender === 1 ? 'pedido pendiente' : 'pedidos pendientes' }} de preparar. Al cancelar, las piezas regresan al inventario.</p>
        </div>
    </header>

    <nav class="barra-categorias-catalogo" aria-label="Filtrar por estado">
        <a href="{{ route('pos.admin.pedidos.index') }}" @class(['boton-filtro', 'activo' => empty($filtros['estado'])])>Todos</a>
        @foreach ($estados as $estado)
            <a href="{{ route('pos.admin.pedidos.index', ['estado' => $estado->value]) }}" @class(['boton-filtro', 'activo' => ($filtros['estado'] ?? null) === $estado->value])>
                {{ ucfirst($estado->value) }}
            </a>
        @endforeach
    </nav>

    @if ($pedidos->isEmpty())
        <div class="mensaje-catalogo-vacio"><p>No hay pedidos con ese estado.</p></div>
    @else
        <div class="lista-pedidos-pos">
            @foreach ($pedidos as $pedido)
                <article class="tarjeta-pedido-pos">
                    <header>
                        <div>
                            <strong>#{{ str_pad($pedido->id, 6, '0', STR_PAD_LEFT) }}</strong>
                            <small>{{ $pedido->created_at->format('d/m/Y H:i') }}</small>
                        </div>
                        @include('pedidos.partials.estado', ['estado' => $pedido->estado_envio])
                    </header>

                    <div class="cuerpo-pedido-pos">
                        <div>
                            <h3>Cliente</h3>
                            <p>{{ $pedido->cliente?->nombre }} {{ $pedido->cliente?->apellido_paterno }}</p>
                            <p><small>{{ $pedido->cliente?->correo }} · {{ $pedido->cliente?->telefono ?? 'sin teléfono' }}</small></p>
                            <h3>Envío</h3>
                            <p>{{ $pedido->direccionEnvio?->calle_numero }}, Col. {{ $pedido->direccionEnvio?->colonia }}, C.P. {{ $pedido->direccionEnvio?->codigo_postal }}</p>
                            @if ($pedido->direccionEnvio?->referencias)
                                <p><small>{{ $pedido->direccionEnvio->referencias }}</small></p>
                            @endif
                        </div>
                        <div>
                            <h3>Artículos</h3>
                            <ul class="articulos-pedido-pos">
                                @foreach ($pedido->detalles as $detalle)
                                    @php($producto = $detalle->inventario->producto)
                                    <li>
                                        @if ($producto->imagen)
                                            <button type="button" class="boton-ampliar-imagen"
                                                    data-ampliar="{{ $producto->imagen }}"
                                                    data-titulo="{{ $detalle->cantidad }} × {{ $producto->titulo }} · Talla {{ $detalle->inventario->talla }}{{ $detalle->inventario->color !== 'Único' ? ' · '.$detalle->inventario->color : '' }}"
                                                    aria-label="Ver en grande: {{ $producto->titulo }}">
                                                <img src="{{ $producto->imagen }}" alt="" class="miniatura-producto-pos" loading="lazy">
                                                <span class="icono-ampliar" aria-hidden="true">⤢</span>
                                            </button>
                                        @else
                                            <span class="miniatura-producto-pos miniatura-vacia-pos" aria-hidden="true"></span>
                                        @endif
                                        <span>
                                            <strong>{{ $detalle->cantidad }} × {{ $producto->titulo }}</strong>
                                            <small>Talla {{ $detalle->inventario->talla }}@if ($detalle->inventario->color !== 'Único') · {{ $detalle->inventario->color }}@endif · ${{ number_format($detalle->subtotal, 2) }}</small>
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                            <p class="total-pedido-pos">
                                Total ${{ number_format($pedido->total, 2) }} ·
                                {{ $pedido->metodo_pago->value === 'efectivo' ? 'Efectivo contra entrega' : ucfirst($pedido->metodo_pago->value) }}
                            </p>
                        </div>
                    </div>

                    @if ($siguientes[$pedido->estado_envio->value] !== [])
                        <footer class="acciones-pedido-pos">
                            @foreach ($siguientes[$pedido->estado_envio->value] as $estado => $etiqueta)
                                <form method="POST" action="{{ route('pos.admin.pedidos.update', $pedido) }}"
                                      @if ($estado === 'cancelado') data-confirmar="¿Cancelar el pedido #{{ $pedido->id }}? Las piezas regresarán al inventario." @endif>
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="estado_envio" value="{{ $estado }}">
                                    <button type="submit" @class(['boton-secundario-pos', 'boton-peligro-pos' => $estado === 'cancelado'])>{{ $etiqueta }}</button>
                                </form>
                            @endforeach
                        </footer>
                    @endif
                </article>
            @endforeach
        </div>

        @include('pos.partials.paginacion', ['paginador' => $pedidos])
    @endif
</section>

<!-- Visor de imagen en grande -->
<dialog id="visorImagen" class="visor-imagen-pos" aria-labelledby="tituloVisor">
    <figure>
        <img id="imagenVisor" src="" alt="">
        <figcaption id="tituloVisor"></figcaption>
    </figure>
    <form method="dialog">
        <button type="submit" class="cerrar-visor-pos" aria-label="Cerrar">&times;</button>
    </form>
</dialog>
@endsection

@push('scripts')
<script src="{{ asset('js/visor-pedidos.js') }}" defer></script>
@endpush
