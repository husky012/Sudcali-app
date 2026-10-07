@extends('layouts.app')

@section('titulo', 'Sudcali - Catálogo Completo')

@section('contenido')
<section class="seccion-catalogo-pagina">
    @php
        // Categoría seleccionada por URL (por defecto 'todos')
        $categoriaActual = request('categoria', 'todos');

        // Arreglo maestro de productos del catálogo
        $todosLosProductos = [
            [
                'categoria_slug' => 'playeras',
                'nombre' => 'Playera Sudcali Clásica',
                'precio' => 250,
                'imagen' => 'https://scontent-qro1-1.xx.fbcdn.net/v/t51.82787-15/681050819_18346264966246782_7528669526424101271_n.jpg?stp=dst-jpg_tt6&cstp=mx899x1198&ctp=s899x1198&_nc_cat=105&ccb=1-7&_nc_sid=127cfc&_nc_ohc=hkBACdLiWTkQ7kNvwGZzmI1&_nc_oc=AdpphfrJVRhUtJzA5ZY7D4LYbN_rLBUb6JMHUf25XsSdZi3Gn7eTmPZFo6DRGZyFUlgjKNQnT5m-OuYzd103YZDv&_nc_zt=23&_nc_ht=scontent-qro1-1.xx&_nc_gid=OEKP16hKgV9ssCyQorGQFA&_nc_ss=7b289&oh=00_AQKyyAV_opHPOqpnvHRxsV1hXNXTeiF4N5KvRF7CR0TSKg&oe=6AB20ED4',
                'descripcion' => 'Algodón 100% premium. Diseñada localmente en La Paz. Disponible en todas las tallas.',
                'etiqueta' => '¡Nuevo!'
            ],
            [
                'categoria_slug' => 'gorras',
                'nombre' => 'Gorra Sudcali Delta',
                'precio' => 650,
                'imagen' => 'https://scontent-qro1-2.xx.fbcdn.net/v/t39.30808-6/807285053_1074182325319857_1790081506254451619_n.jpg?stp=dst-jpg_tt6&cstp=mx1280x960&ctp=s1280x960&_nc_cat=101&ccb=1-7&_nc_sid=833d8c&_nc_ohc=h7MxK0TNrIMQ7kNvwEH3Gbl&_nc_oc=AdrSv8RldLWak5E0U4l80RBV-BpWbUx6xg2YsqOY7-eX9cs7IjmH40GB1Ei4XdP5fb8QYWRKchr9ZM8ezwDvOq8w&_nc_zt=23&_nc_ht=scontent-qro1-2.xx&_nc_gid=UvO3iASt6kIhzafoatiiyw&_nc_ss=7b289&oh=00_AQLRxaCsqlBiLkmvJ8NqYuBZpHsgfMO4KLocuWMJUMKk8Q&oe=6AB21D76',
                'descripcion' => 'Ideal para el día de malecón. Corte unisex súper cómodo.',
                'etiqueta' => '¡Popular!'
            ],
            [
                'categoria_slug' => 'sudaderas',
                'nombre' => 'Sudadera Sudcali Urban',
                'precio' => 850,
                'imagen' => 'https://images.unsplash.com/photo-1556905055-8f358a7a47b2?w=500',
                'descripcion' => 'Corte amplio oversize, gorro térmico y bolsa frontal.',
                'etiqueta' => '¡Invierno!'
            ],
            [
                'categoria_slug' => 'accesorios',
                'nombre' => 'Llavero de Neopreno Sudcali',
                'precio' => 120,
                'imagen' => 'https://images.unsplash.com/photo-1588850561407-ed78c282e89b?w=500',
                'descripcion' => 'Llavero Flotante ideal para actividades acuáticas y marina.',
            ]
        ];

        // Filtrar productos según la categoría activa
        $productosFiltrados = array_filter($todosLosProductos, function($prod) use ($categoriaActual) {
            return $categoriaActual === 'todos' || $prod['categoria_slug'] === $categoriaActual;
        });
    @endphp

    <header class="encabezado-seccion">
        <h2>Explora nuestro Catálogo</h2>
        <p>Encuentra ropa y accesorios con identidad sudcaliforniana</p>
    </header>

    <!-- Barra de Filtros / Categorías -->
    <nav class="barra-categorias-catalogo" aria-label="Filtro de categorías">
        <a href="/catalogo?categoria=todos" class="boton-filtro {{ $categoriaActual === 'todos' ? 'activo' : '' }}">
            Todos
        </a>
        <a href="/catalogo?categoria=playeras" class="boton-filtro {{ $categoriaActual === 'playeras' ? 'activo' : '' }}">
            Playeras
        </a>
        <a href="/catalogo?categoria=gorras" class="boton-filtro {{ $categoriaActual === 'gorras' ? 'activo' : '' }}">
            Gorras
        </a>
        <a href="/catalogo?categoria=sudaderas" class="boton-filtro {{ $categoriaActual === 'sudaderas' ? 'activo' : '' }}">
            Sudaderas
        </a>
        <a href="/catalogo?categoria=accesorios" class="boton-filtro {{ $categoriaActual === 'accesorios' ? 'activo' : '' }}">
            Accesorios
        </a>
    </nav>

    <!-- Rejilla de Productos con Componente Reutilizable -->
    @if(count($productosFiltrados) > 0)
        <div class="cuadricula-productos">
            @foreach($productosFiltrados as $producto)
                @include('components.tarjeta-producto', ['producto' => $producto])
            @endforeach
        </div>
    @else
        <div class="mensaje-catalogo-vacio">
            <p>No hay productos disponibles en esta categoría por el momento.</p>
            <a href="/catalogo" class="boton-secundario">Ver todo el catálogo</a>
        </div>
    @endif
</section>
@endsection