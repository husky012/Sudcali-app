@extends('layouts.app')

@section('titulo', 'Sudcali - Playera Sudcali Clásica')

@section('contenido')
<section class="seccion-detalle-producto">
    @php
        // Datos del producto principal
        $producto = [
            'id' => 1,
            'nombre' => 'Playera Sudcali Clásica',
            'precio' => 250,
            'categoria' => 'Playeras',
            'etiqueta' => '¡Nuevo!',
            'descripcion' => 'Confeccionada con 100% algodón premium transpirable, ideal para el clima de La Paz, B.C.S. Presenta estampado serigráfico de alta durabilidad con el imagotipo oficial de Sudcali en el pecho.',
            'tallas' => ['S', 'M', 'L', 'XL'],
            'imagenes' => [
                'https://scontent-qro1-1.xx.fbcdn.net/v/t51.82787-15/681050819_18346264966246782_7528669526424101271_n.jpg?stp=dst-jpg_tt6&cstp=mx899x1198&ctp=s899x1198&_nc_cat=105&ccb=1-7&_nc_sid=127cfc&_nc_ohc=hkBACdLiWTkQ7kNvwGZzmI1&_nc_oc=AdpphfrJVRhUtJzA5ZY7D4LYbN_rLBUb6JMHUf25XsSdZi3Gn7eTmPZFo6DRGZyFUlgjKNQnT5m-OuYzd103YZDv&_nc_zt=23&_nc_ht=scontent-qro1-1.xx&_nc_gid=OEKP16hKgV9ssCyQorGQFA&_nc_ss=7b289&oh=00_AQKyyAV_opHPOqpnvHRxsV1hXNXTeiF4N5KvRF7CR0TSKg&oe=6AB20ED4',
                'https://scontent-qro1-2.xx.fbcdn.net/v/t39.30808-6/807285053_1074182325319857_1790081506254451619_n.jpg?stp=dst-jpg_tt6&cstp=mx1280x960&ctp=s1280x960&_nc_cat=101&ccb=1-7&_nc_sid=833d8c&_nc_ohc=h7MxK0TNrIMQ7kNvwEH3Gbl&_nc_oc=AdrSv8RldLWak5E0U4l80RBV-BpWbUx6xg2YsqOY7-eX9cs7IjmH40GB1Ei4XdP5fb8QYWRKchr9ZM8ezwDvOq8w&_nc_zt=23&_nc_ht=scontent-qro1-2.xx&_nc_gid=UvO3iASt6kIhzafoatiiyw&_nc_ss=7b289&oh=00_AQLRxaCsqlBiLkmvJ8NqYuBZpHsgfMO4KLocuWMJUMKk8Q&oe=6AB21D76'
            ]
        ];

        // Productos relacionados para usar tu componente reutilizable
        $relacionados = [
            [
                'nombre' => 'Gorra Sudcali Delta',
                'precio' => 650,
                'imagen' => 'https://scontent-qro1-2.xx.fbcdn.net/v/t39.30808-6/807285053_1074182325319857_1790081506254451619_n.jpg?stp=dst-jpg_tt6&cstp=mx1280x960&ctp=s1280x960&_nc_cat=101&ccb=1-7&_nc_sid=833d8c&_nc_ohc=h7MxK0TNrIMQ7kNvwEH3Gbl&_nc_oc=AdrSv8RldLWak5E0U4l80RBV-BpWbUx6xg2YsqOY7-eX9cs7IjmH40GB1Ei4XdP5fb8QYWRKchr9ZM8ezwDvOq8w&_nc_zt=23&_nc_ht=scontent-qro1-2.xx&_nc_gid=UvO3iASt6kIhzafoatiiyw&_nc_ss=7b289&oh=00_AQLRxaCsqlBiLkmvJ8NqYuBZpHsgfMO4KLocuWMJUMKk8Q&oe=6AB21D76',
                'descripcion' => 'Ideal para el día de malecón. Corte unisex súper cómodo.',
                'etiqueta' => '¡Popular!'
            ]
        ];
    @endphp

    <!-- Migas de pan / Navegación rápida -->
    <nav class="migas-pan" aria-label="Ruta de navegación">
        <a href="/">Inicio</a> / <a href="#catalogo">Catálogo</a> / <span>{{ $producto['nombre'] }}</span>
    </nav>

    <div class="contenedor-grid-detalle">
        <!-- Galería de Imágenes -->
        <div class="galeria-producto">
            <figure class="imagen-principal-detalle">
                <img src="{{ $producto['imagenes'][0] }}" alt="{{ $producto['nombre'] }}" id="imagenPrincipal">
                @if(isset($producto['etiqueta']))
                    <span class="etiqueta-pulso">{{ $producto['etiqueta'] }}</span>
                @endif
            </figure>
            <div class="miniaturas-galeria">
                @foreach($producto['imagenes'] as $idx => $img)
                    <button type="button" class="boton-miniatura {{ $idx === 0 ? 'activa' : '' }}" onclick="document.getElementById('imagenPrincipal').src='{{ $img }}'">
                        <img src="{{ $img }}" alt="Vista {{ $idx + 1 }}">
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Información y Controles de Compra -->
        <div class="info-producto-detalle">
            <span class="categoria-badge">{{ $producto['categoria'] }}</span>
            <h2>{{ $producto['nombre'] }}</h2>
            <p class="precio-detalle">${{ number_format($producto['precio'], 2) }} MXN</p>
            
            <p class="descripcion-corta">{{ $producto['descripcion'] }}</p>

            <form action="/carrito" method="GET" class="form-seleccion-producto">
                <!-- Seleccionar Talla -->
                <div class="grupo-seleccion">
                    <label>Selecciona tu Talla:</label>
                    <div class="opciones-tallas">
                        @foreach($producto['tallas'] as $talla)
                            <label class="radio-talla">
                                <input type="radio" name="talla" value="{{ $talla }}" {{ $loop->first ? 'checked' : '' }}>
                                <span>{{ $talla }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Seleccionar Cantidad -->
                <div class="grupo-seleccion">
                    <label for="cantidad">Cantidad:</label>
                    <div class="selector-cantidad-detalle">
                        <input type="number" id="cantidad" name="cantidad" value="1" min="1" max="10">
                    </div>
                </div>

                <!-- Acciones -->
                <div class="acciones-compra-detalle">
                    <button type="submit" class="boton-agregar-grande">Añadir al Carrito</button>
                    <a href="/carrito" class="boton-comprar-ahora">Comprar Ahora</a>
                </div>
            </form>

            <!-- Ventajas / Garantías Locales -->
            <ul class="lista-ventajas-locales">
                <li>🚚 <strong>Entrega Local:</strong> Recibe el mismo día en La Paz, B.C.S.</li>
                <li>🔄 <strong>Cambios:</strong> Sin costo en puntos de entrega oficiales.</li>
            </ul>
        </div>
    </div>

    <!-- Sección de Productos Relacionados -->
    <section class="seccion-relacionados">
        <header class="encabezado-seccion">
            <h3>También te podría gustar</h3>
        </header>
        <div class="cuadricula-productos">
            @foreach($relacionados as $item)
                @include('components.tarjeta-producto', ['producto' => $item])
            @endforeach
        </div>
    </section>
</section>
@endsection