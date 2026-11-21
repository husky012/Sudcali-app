@extends('layouts.app')

@section('titulo', 'Sudcali - Tu Carrito de Compras')

@section('contenido')
<section class="seccion-carrito">
    <header class="encabezado-seccion">
        <h2>Tu Carrito de Compras</h2>
        <p>Revisa tus artículos antes de proceder al pago</p>
    </header>

    @if ($renglones->isEmpty())
        <div class="mensaje-catalogo-vacio">
            <p>Tu carrito está vacío.</p>
            <a href="{{ route('catalogo') }}" class="boton-filtro">Explorar el catálogo</a>
        </div>
    @else
        <div class="contenedor-grid-carrito">
            <!-- Lista de Productos en Carrito -->
            <div class="lista-items-carrito">
                @foreach ($renglones as $renglon)
                    @php($variante = $renglon['variante'])
                    <article class="tarjeta-item-carrito">
                        <figure class="imagen-item-carrito">
                            @if ($variante->producto->imagen)
                                <img src="{{ $variante->producto->imagen }}" alt="{{ $variante->producto->titulo }}">
                            @endif
                        </figure>
                        <div class="detalles-item-carrito">
                            <h3><a href="{{ route('producto.detalle', $variante->producto) }}">{{ $variante->producto->titulo }}</a></h3>
                            <p class="meta-item">
                                Talla: <span>{{ $variante->talla }}</span>
                                @if ($variante->color !== 'Único')
                                    · Color: <span>{{ $variante->color }}</span>
                                @endif
                            </p>
                            <p class="precio-unitario">${{ number_format($variante->producto->precio, 2) }} MXN c/u</p>
                            @unless ($renglon['disponible'])
                                <p class="aviso-existencias">Solo quedan {{ $variante->stock }}. Ajusta la cantidad.</p>
                            @endunless
                        </div>
                        <div class="acciones-item-carrito">
                            <div class="control-cantidad">
                                <form method="POST" action="{{ route('carrito.actualizar', $variante) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="cantidad" value="{{ $renglon['cantidad'] - 1 }}">
                                    <button type="submit" aria-label="Quitar una pieza de {{ $variante->producto->titulo }}">−</button>
                                </form>
                                <span aria-label="Cantidad">{{ $renglon['cantidad'] }}</span>
                                <form method="POST" action="{{ route('carrito.actualizar', $variante) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="cantidad" value="{{ $renglon['cantidad'] + 1 }}">
                                    <button type="submit" aria-label="Agregar una pieza de {{ $variante->producto->titulo }}" @disabled($renglon['cantidad'] >= $variante->stock)>+</button>
                                </form>
                            </div>
                            <p class="subtotal-item">${{ number_format($renglon['importe'], 2) }} MXN</p>
                            <form method="POST" action="{{ route('carrito.quitar', $variante->id) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="boton-eliminar-item">Quitar</button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>

            <!-- Resumen de Pedido -->
            <aside class="resumen-pedido">
                <h3>Resumen de Compra</h3>
                <div class="desglose-resumen">
                    <div class="fila-resumen">
                        <span>Subtotal</span>
                        <span>${{ number_format($totales['subtotal'], 2) }} MXN</span>
                    </div>
                    <div class="fila-resumen">
                        <span>Envío (B.C.S.)</span>
                        <span>{{ $totales['envio'] > 0 ? '$'.number_format($totales['envio'], 2).' MXN' : 'Gratis' }}</span>
                    </div>
                    @if ($totales['envio'] > 0)
                        <p class="nota-envio">Te faltan ${{ number_format($envioGratisDesde - $totales['subtotal'], 2) }} para el envío gratis.</p>
                    @endif
                    <hr class="divisor-resumen">
                    <div class="fila-resumen fila-total">
                        <span>Total</span>
                        <span class="precio-total">${{ number_format($totales['total'], 2) }} MXN</span>
                    </div>
                </div>

                @if ($renglones->every('disponible'))
                    <a href="{{ route('checkout.create') }}" class="boton-finalizar-compra boton-enlace-pos">Proceder al Pago</a>
                @else
                    <button type="button" class="boton-finalizar-compra" disabled>Ajusta las cantidades para continuar</button>
                @endif
                <a href="{{ route('catalogo') }}" class="enlace-seguir-comprando">← Seguir comprando</a>
            </aside>
        </div>
    @endif
</section>
@endsection
