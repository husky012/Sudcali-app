@extends('layouts.app')

@section('titulo', 'Sudcali - Tu Carrito de Compras')

@section('contenido')
<section class="seccion-carrito">
    <header class="encabezado-seccion">
        <h2>Tu Carrito de Compras</h2>
        <p>Revisa tus artículos antes de proceder al pago</p>
    </header>

    @php
        // Arreglo de simulación para los productos dentro del carrito
        $itemsCarrito = [
            [
                'nombre' => 'Playera Sudcali Clásica',
                'talla' => 'M',
                'precio' => 250,
                'cantidad' => 2,
                'imagen' => 'https://scontent-qro1-1.xx.fbcdn.net/v/t51.82787-15/681050819_18346264966246782_7528669526424101271_n.jpg?stp=dst-jpg_tt6&cstp=mx899x1198&ctp=s899x1198&_nc_cat=105&ccb=1-7&_nc_sid=127cfc&_nc_ohc=hkBACdLiWTkQ7kNvwGZzmI1&_nc_oc=AdpphfrJVRhUtJzA5ZY7D4LYbN_rLBUb6JMHUf25XsSdZi3Gn7eTmPZFo6DRGZyFUlgjKNQnT5m-OuYzd103YZDv&_nc_zt=23&_nc_ht=scontent-qro1-1.xx&_nc_gid=OEKP16hKgV9ssCyQorGQFA&_nc_ss=7b289&oh=00_AQKyyAV_opHPOqpnvHRxsV1hXNXTeiF4N5KvRF7CR0TSKg&oe=6AB20ED4'
            ],
            [
                'nombre' => 'Gorra Sudcali Delta',
                'talla' => 'Unica',
                'precio' => 650,
                'cantidad' => 1,
                'imagen' => 'https://scontent-qro1-2.xx.fbcdn.net/v/t39.30808-6/807285053_1074182325319857_1790081506254451619_n.jpg?stp=dst-jpg_tt6&cstp=mx1280x960&ctp=s1280x960&_nc_cat=101&ccb=1-7&_nc_sid=833d8c&_nc_ohc=h7MxK0TNrIMQ7kNvwEH3Gbl&_nc_oc=AdrSv8RldLWak5E0U4l80RBV-BpWbUx6xg2YsqOY7-eX9cs7IjmH40GB1Ei4XdP5fb8QYWRKchr9ZM8ezwDvOq8w&_nc_zt=23&_nc_ht=scontent-qro1-2.xx&_nc_gid=UvO3iASt6kIhzafoatiiyw&_nc_ss=7b289&oh=00_AQLRxaCsqlBiLkmvJ8NqYuBZpHsgfMO4KLocuWMJUMKk8Q&oe=6AB21D76'
            ]
        ];

        // Cálculo dinámico del subtotal
        $subtotal = array_reduce($itemsCarrito, function($acc, $item) {
            return $acc + ($item['precio'] * $item['cantidad']);
        }, 0);
        $envioLocal = 50; // Envío fijo en La Paz, B.C.S.
        $total = $subtotal + $envioLocal;
    @endphp

    <div class="contenedor-grid-carrito">
        <!-- Lista de Productos en Carrito -->
        <div class="lista-items-carrito">
            @foreach($itemsCarrito as $item)
                <article class="tarjeta-item-carrito">
                    <figure class="imagen-item-carrito">
                        <img src="{{ $item['imagen'] }}" alt="{{ $item['nombre'] }}">
                    </figure>
                    <div class="detalles-item-carrito">
                        <h3>{{ $item['nombre'] }}</h3>
                        <p class="meta-item">Talla: <span>{{ $item['talla'] }}</span></p>
                        <p class="precio-unitario">${{ number_format($item['precio'], 2) }} MXN c/u</p>
                    </div>
                    <div class="acciones-item-carrito">
                        <div class="control-cantidad">
                            <button type="button" aria-label="Disminuir cantidad">-</button>
                            <span>{{ $item['cantidad'] }}</span>
                            <button type="button" aria-label="Aumentar cantidad">+</button>
                        </div>
                        <p class="subtotal-item">${{ number_format($item['precio'] * $item['cantidad'], 2) }} MXN</p>
                        <button type="button" class="boton-eliminar-item">Quitar</button>
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
                    <span>${{ number_format($subtotal, 2) }} MXN</span>
                </div>
                <div class="fila-resumen">
                    <span>Envío local (La Paz)</span>
                    <span>${{ number_format($envioLocal, 2) }} MXN</span>
                </div>
                <hr class="divisor-resumen">
                <div class="fila-resumen fila-total">
                    <span>Total</span>
                    <span class="precio-total">${{ number_format($total, 2) }} MXN</span>
                </div>
            </div>

            <button type="button" class="boton-finalizar-compra">Proceder al Pago</button>
            <a href="/" class="enlace-seguir-comprando">← Seguir comprando</a>
        </aside>
    </div>
</section>
@endsection