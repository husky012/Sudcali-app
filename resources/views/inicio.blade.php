@extends('layouts.app')

@section('titulo', 'Sudcali - Inicio | La Paz, B.C.S.')

@section('contenido')
    @php
        // Arreglo de productos para alimentar las tarjetas dinámicamente
        $productos = [
            [
                'nombre' => 'Playera Sudcali Clásica',
                'precio' => 250,
                'imagen' => 'https://scontent-qro1-1.xx.fbcdn.net/v/t51.82787-15/681050819_18346264966246782_7528669526424101271_n.jpg?stp=dst-jpg_tt6&cstp=mx899x1198&ctp=s899x1198&_nc_cat=105&ccb=1-7&_nc_sid=127cfc&_nc_ohc=hkBACdLiWTkQ7kNvwGZzmI1&_nc_oc=AdpphfrJVRhUtJzA5ZY7D4LYbN_rLBUb6JMHUf25XsSdZi3Gn7eTmPZFo6DRGZyFUlgjKNQnT5m-OuYzd103YZDv&_nc_zt=23&_nc_ht=scontent-qro1-1.xx&_nc_gid=OEKP16hKgV9ssCyQorGQFA&_nc_ss=7b289&oh=00_AQKyyAV_opHPOqpnvHRxsV1hXNXTeiF4N5KvRF7CR0TSKg&oe=6AB20ED4',
                'descripcion' => 'Algodón 100% premium. Diseñada localmente en La Paz. Disponible en todas las tallas.',
                'etiqueta' => '¡Nuevo!'
            ],
            [
                'nombre' => 'Gorra Sudcali Delta',
                'precio' => 650,
                'imagen' => 'https://scontent-qro1-2.xx.fbcdn.net/v/t39.30808-6/807285053_1074182325319857_1790081506254451619_n.jpg?stp=dst-jpg_tt6&cstp=mx1280x960&ctp=s1280x960&_nc_cat=101&ccb=1-7&_nc_sid=833d8c&_nc_ohc=h7MxK0TNrIMQ7kNvwEH3Gbl&_nc_oc=AdrSv8RldLWak5E0U4l80RBV-BpWbUx6xg2YsqOY7-eX9cs7IjmH40GB1Ei4XdP5fb8QYWRKchr9ZM8ezwDvOq8w&_nc_zt=23&_nc_ht=scontent-qro1-2.xx&_nc_gid=UvO3iASt6kIhzafoatiiyw&_nc_ss=7b289&oh=00_AQLRxaCsqlBiLkmvJ8NqYuBZpHsgfMO4KLocuWMJUMKk8Q&oe=6AB21D76',
                'descripcion' => 'Ideal para el día de malecón. Corte unisex súper cómodo.',
                'etiqueta' => '¡Popular!'
            ]
        ];
    @endphp

    <!-- Sección Principal / Banner -->
    <section class="seccion-bienvenida">
        <div class="contenido-bienvenida">
            <h2>Moda & Estilo en La Paz, B.C.S.</h2>
            <p>Explora nuestro catálogo exclusivo de ropa y accesorios con entregas locales directas a tu domicilio.</p>
            <a href="#catalogo" class="boton-banner">Ver Catálogo</a>
        </div>
    </section>

    <!-- Sección de Catálogo dinámico -->
    <section class="seccion-catalogo" id="catalogo">
        <header class="encabezado-seccion">
            <h2>Catálogo Destacado</h2>
            <p>Nuestros artículos populares disponibles en tienda</p>
        </header>

        <div class="cuadricula-productos">
            @foreach($productos as $producto)
                @include('components.tarjeta-producto', ['producto' => $producto])
            @endforeach
        </div>
    </section>
@endsection