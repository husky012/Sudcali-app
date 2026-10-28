@extends('layouts.app')

@section('titulo', 'Sudcali - Catálogo Completo')

@section('contenido')
<section class="seccion-catalogo-pagina">
    @php
        // Categoría seleccionada por URL (por defecto 'todos')
        $categoriaActual = request('categoria', 'todos');

        // Arreglo maestro de productos del catálogo
        //Playeras
        $todosLosProductos = [
            [
                'categoria_slug' => 'playeras',
                'nombre' => 'Playera Sudcali Clásica',
                'precio' => 250,
                'imagen' => '/imagenes/camisas/playera_sudcali_1.jpeg',
                'descripcion' => 'Algodón 100% premium. Diseñada localmente en La Paz. Disponible en todas las tallas.',
                'etiqueta' => '¡Nuevo!'
            ],
            [
                'categoria_slug' => 'playeras',
                'nombre' => 'Playera Sudcali Clásica',
                'precio' => 250,
                'imagen' => '/imagenes/camisas/playera_sudcali_2.jpeg',
                'descripcion' => 'Algodón 100% premium. Diseñada localmente en La Paz. Disponible en todas las tallas.',
                'etiqueta' => '¡Nuevo!'
            ],
            [
                'categoria_slug' => 'playeras',
                'nombre' => 'Playera Sudcali Clásica',
                'precio' => 250,
                'imagen' => '/imagenes/camisas/playera_sudcali_3.jpeg',
                'descripcion' => 'Algodón 100% premium. Diseñada localmente en La Paz. Disponible en todas las tallas.',
                'etiqueta' => '¡Nuevo!'
            ],
            [
                'categoria_slug' => 'playeras',
                'nombre' => 'Playera Sudcali Clásica',
                'precio' => 250,
                'imagen' => '/imagenes/camisas/playera_sudcali_4.jpeg',
                'descripcion' => 'Algodón 100% premium. Diseñada localmente en La Paz. Disponible en todas las tallas.',
                'etiqueta' => '¡Nuevo!'
            ],
            [
                'categoria_slug' => 'playeras',
                'nombre' => 'Playera Sudcali Clásica',
                'precio' => 250,
                'imagen' => '/imagenes/camisas/playera_sudcali_5.jpeg',
                'descripcion' => 'Algodón 100% premium. Diseñada localmente en La Paz. Disponible en todas las tallas.',
                'etiqueta' => '¡Nuevo!'
            ],
            [
                'categoria_slug' => 'playeras',
                'nombre' => 'Playera Sudcali Clásica',
                'precio' => 250,
                'imagen' => '/imagenes/camisas/playera_sudcali_6.jpeg',
                'descripcion' => 'Algodón 100% premium. Diseñada localmente en La Paz. Disponible en todas las tallas.',
                'etiqueta' => '¡Nuevo!'
            ],
            [
                'categoria_slug' => 'playeras',
                'nombre' => 'Playera Sudcali Clásica',
                'precio' => 250,
                'imagen' => '/imagenes/camisas/playera_sudcali_7.jpeg',
                'descripcion' => 'Algodón 100% premium. Diseñada localmente en La Paz. Disponible en todas las tallas.',
                'etiqueta' => '¡Nuevo!'
            ],
            [
                'categoria_slug' => 'playeras',
                'nombre' => 'Playera Sudcali Clásica',
                'precio' => 250,
                'imagen' => '/imagenes/camisas/playera_sudcali_8.jpeg',
                'descripcion' => 'Algodón 100% premium. Diseñada localmente en La Paz. Disponible en todas las tallas.',
                'etiqueta' => '¡Nuevo!'
            ],
            //Gorras
            [
                'categoria_slug' => 'gorras',
                'nombre' => 'Gorra Sudcali Delta',
                'precio' => 650,
                'imagen' => '/imagenes/gorras/gorra_sudcali_1.jpeg',
                'descripcion' => 'Ideal para el día de malecón. Corte unisex súper cómodo.',
                'etiqueta' => '¡Popular!'
            ],
            [
                'categoria_slug' => 'gorras',
                'nombre' => 'Gorra Sudcali Delta',
                'precio' => 650,
                'imagen' => '/imagenes/gorras/gorra_sudcali_2.jpeg',
                'descripcion' => 'Ideal para el día de malecón. Corte unisex súper cómodo.',
                'etiqueta' => '¡Popular!'
            ],
            [
                'categoria_slug' => 'gorras',
                'nombre' => 'Gorra Sudcali Delta',
                'precio' => 650,
                'imagen' => '/imagenes/gorras/gorra_sudcali_3.jpeg',
                'descripcion' => 'Ideal para el día de malecón. Corte unisex súper cómodo.',
                'etiqueta' => '¡Popular!'
            ],
            [
                'categoria_slug' => 'gorras',
                'nombre' => 'Gorra Sudcali Delta',
                'precio' => 650,
                'imagen' => '/imagenes/gorras/gorra_sudcali_4.jpeg',
                'descripcion' => 'Ideal para el día de malecón. Corte unisex súper cómodo.',
                'etiqueta' => '¡Popular!'
            ],
            [
                'categoria_slug' => 'gorras',
                'nombre' => 'Gorra Sudcali Delta',
                'precio' => 650,
                'imagen' => '/imagenes/gorras/gorra_sudcali_5.jpeg',
                'descripcion' => 'Ideal para el día de malecón. Corte unisex súper cómodo.',
                'etiqueta' => '¡Popular!'
            ],
            [
                'categoria_slug' => 'gorras',
                'nombre' => 'Gorra Sudcali Delta',
                'precio' => 650,
                'imagen' => '/imagenes/gorras/gorra_sudcali_6.jpeg',
                'descripcion' => 'Ideal para el día de malecón. Corte unisex súper cómodo.',
                'etiqueta' => '¡Popular!'
            ],
            [
                'categoria_slug' => 'gorras',
                'nombre' => 'Gorra Sudcali Delta',
                'precio' => 650,
                'imagen' => '/imagenes/gorras/gorra_sudcali_7.jpeg',
                'descripcion' => 'Ideal para el día de malecón. Corte unisex súper cómodo.',
                'etiqueta' => '¡Popular!'
            ],
            [
                'categoria_slug' => 'gorras',
                'nombre' => 'Gorra Sudcali Delta',
                'precio' => 650,
                'imagen' => '/imagenes/gorras/gorra_sudcali_8.jpeg',
                'descripcion' => 'Ideal para el día de malecón. Corte unisex súper cómodo.',
                'etiqueta' => '¡Popular!'
            ],
            [
                'categoria_slug' => 'gorras',
                'nombre' => 'Gorra Sudcali Delta',
                'precio' => 650,
                'imagen' => '/imagenes/gorras/gorra_sudcali_9.jpeg',
                'descripcion' => 'Ideal para el día de malecón. Corte unisex súper cómodo.',
                'etiqueta' => '¡Popular!'
            ],
            [
                'categoria_slug' => 'gorras',
                'nombre' => 'Gorra Sudcali Delta',
                'precio' => 650,
                'imagen' => '/imagenes/gorras/gorra_sudcali_10.jpeg',
                'descripcion' => 'Ideal para el día de malecón. Corte unisex súper cómodo.',
                'etiqueta' => '¡Popular!'
            ],
            [
                'categoria_slug' => 'gorras',
                'nombre' => 'Gorra Sudcali Delta',
                'precio' => 650,
                'imagen' => '/imagenes/gorras/gorra_sudcali_11.jpeg',
                'descripcion' => 'Ideal para el día de malecón. Corte unisex súper cómodo.',
                'etiqueta' => '¡Popular!'
            ],
            [
                'categoria_slug' => 'gorras',
                'nombre' => 'Gorra Sudcali Delta',
                'precio' => 650,
                'imagen' => '/imagenes/gorras/gorra_sudcali_12.jpeg',
                'descripcion' => 'Ideal para el día de malecón. Corte unisex súper cómodo.',
                'etiqueta' => '¡Popular!'
            ],
            [
                'categoria_slug' => 'gorras',
                'nombre' => 'Gorra Sudcali Delta',
                'precio' => 650,
                'imagen' => '/imagenes/gorras/gorra_sudcali_14.jpeg',
                'descripcion' => 'Ideal para el día de malecón. Corte unisex súper cómodo.',
                'etiqueta' => '¡Popular!'
            ],
            [
                'categoria_slug' => 'gorras',
                'nombre' => 'Gorra Sudcali Delta',
                'precio' => 650,
                'imagen' => '/imagenes/gorras/gorra_sudcali_15.jpeg',
                'descripcion' => 'Ideal para el día de malecón. Corte unisex súper cómodo.',
                'etiqueta' => '¡Popular!'
            ],
            [
                'categoria_slug' => 'gorras',
                'nombre' => 'Gorra Sudcali Delta',
                'precio' => 650,
                'imagen' => '/imagenes/gorras/gorra_sudcali_16.jpeg',
                'descripcion' => 'Ideal para el día de malecón. Corte unisex súper cómodo.',
                'etiqueta' => '¡Popular!'
            ],
            [
                'categoria_slug' => 'gorras',
                'nombre' => 'Gorra Sudcali Delta',
                'precio' => 650,
                'imagen' => '/imagenes/gorras/gorra_sudcali_17.jpeg',
                'descripcion' => 'Ideal para el día de malecón. Corte unisex súper cómodo.',
                'etiqueta' => '¡Popular!'
            ],
            [
                'categoria_slug' => 'gorras',
                'nombre' => 'Gorra Sudcali Delta',
                'precio' => 650,
                'imagen' => '/imagenes/gorras/gorra_sudcali_18.jpeg',
                'descripcion' => 'Ideal para el día de malecón. Corte unisex súper cómodo.',
                'etiqueta' => '¡Popular!'
            ],
            [
                'categoria_slug' => 'gorras',
                'nombre' => 'Gorra Sudcali Delta',
                'precio' => 650,
                'imagen' => '/imagenes/gorras/gorra_sudcali_19.jpeg',
                'descripcion' => 'Ideal para el día de malecón. Corte unisex súper cómodo.',
                'etiqueta' => '¡Popular!'
            ],
            [
                'categoria_slug' => 'gorras',
                'nombre' => 'Gorra Sudcali Delta',
                'precio' => 650,
                'imagen' => '/imagenes/gorras/gorra_sudcali_20.jpeg',
                'descripcion' => 'Ideal para el día de malecón. Corte unisex súper cómodo.',
                'etiqueta' => '¡Popular!'
            ],
            [
                'categoria_slug' => 'gorras',
                'nombre' => 'Gorra Sudcali Delta',
                'precio' => 650,
                'imagen' => '/imagenes/gorras/gorra_sudcali_21.jpeg',
                'descripcion' => 'Ideal para el día de malecón. Corte unisex súper cómodo.',
                'etiqueta' => '¡Popular!'
            ],
            [
                'categoria_slug' => 'gorras',
                'nombre' => 'Gorra Sudcali Delta',
                'precio' => 650,
                'imagen' => '/imagenes/gorras/gorra_sudcali_22.jpeg',
                'descripcion' => 'Ideal para el día de malecón. Corte unisex súper cómodo.',
                'etiqueta' => '¡Popular!'
            ],
            [
                'categoria_slug' => 'gorras',
                'nombre' => 'Gorra Sudcali Delta',
                'precio' => 650,
                'imagen' => '/imagenes/gorras/gorra_sudcali_23.jpeg',
                'descripcion' => 'Ideal para el día de malecón. Corte unisex súper cómodo.',
                'etiqueta' => '¡Popular!'
            ],
            [
                'categoria_slug' => 'gorras',
                'nombre' => 'Gorra Sudcali Delta',
                'precio' => 650,
                'imagen' => '/imagenes/gorras/gorra_sudcali_24.jpeg',
                'descripcion' => 'Ideal para el día de malecón. Corte unisex súper cómodo.',
                'etiqueta' => '¡Popular!'
            ],
            [
                'categoria_slug' => 'gorras',
                'nombre' => 'Gorra Sudcali Delta',
                'precio' => 650,
                'imagen' => '/imagenes/gorras/gorra_sudcali_25.jpeg',
                'descripcion' => 'Ideal para el día de malecón. Corte unisex súper cómodo.',
                'etiqueta' => '¡Popular!'
            ],
            [
                'categoria_slug' => 'gorras',
                'nombre' => 'Gorra Sudcali Delta',
                'precio' => 650,
                'imagen' => '/imagenes/gorras/gorra_sudcali_26.jpeg',
                'descripcion' => 'Ideal para el día de malecón. Corte unisex súper cómodo.',
                'etiqueta' => '¡Popular!'
            ],
            [
                'categoria_slug' => 'gorras',
                'nombre' => 'Gorra Sudcali Delta',
                'precio' => 650,
                'imagen' => '/imagenes/gorras/gorra_sudcali_27.jpeg',
                'descripcion' => 'Ideal para el día de malecón. Corte unisex súper cómodo.',
                'etiqueta' => '¡Popular!'
            ],
            [
                'categoria_slug' => 'gorras',
                'nombre' => 'Gorra Sudcali Delta',
                'precio' => 650,
                'imagen' => '/imagenes/gorras/gorra_sudcali_28.jpeg',
                'descripcion' => 'Ideal para el día de malecón. Corte unisex súper cómodo.',
                'etiqueta' => '¡Popular!'
            ],
            [
                'categoria_slug' => 'gorras',
                'nombre' => 'Gorra Sudcali Delta',
                'precio' => 650,
                'imagen' => '/imagenes/gorras/gorra_sudcali_29.jpeg',
                'descripcion' => 'Ideal para el día de malecón. Corte unisex súper cómodo.',
                'etiqueta' => '¡Popular!'
            ],
            [
                'categoria_slug' => 'gorras',
                'nombre' => 'Gorra Sudcali Delta',
                'precio' => 650,
                'imagen' => '/imagenes/gorras/gorra_sudcali_30.jpeg',
                'descripcion' => 'Ideal para el día de malecón. Corte unisex súper cómodo.',
                'etiqueta' => '¡Popular!'
            ],
            [
                'categoria_slug' => 'gorras',
                'nombre' => 'Gorra Sudcali Delta',
                'precio' => 650,
                'imagen' => '/imagenes/gorras/gorra_sudcali_31.jpeg',
                'descripcion' => 'Ideal para el día de malecón. Corte unisex súper cómodo.',
                'etiqueta' => '¡Popular!'
            ],
            [
                'categoria_slug' => 'buff',
                'nombre' => 'Sudadera Sudcali Urban',
                'precio' => 850,
                'imagen' => '/imagenes/buff/sudcali_buff_1.jpeg',
                'descripcion' => 'Corte amplio oversize, gorro térmico y bolsa frontal.',
                'etiqueta' => '¡Invierno!'
            ],
            [
                'categoria_slug' => 'buff',
                'nombre' => 'Sudadera Sudcali Urban',
                'precio' => 850,
                'imagen' => '/imagenes/buff/sudcali_buff_2.jpeg',
                'descripcion' => 'Corte amplio oversize, gorro térmico y bolsa frontal.',
                'etiqueta' => '¡Invierno!'
            ],
            [
                'categoria_slug' => 'buff',
                'nombre' => 'Sudadera Sudcali Urban',
                'precio' => 850,
                'imagen' => '/imagenes/buff/sudcali_buff_3.jpeg',
                'descripcion' => 'Corte amplio oversize, gorro térmico y bolsa frontal.',
                'etiqueta' => '¡Invierno!'
            ],
            [
                'categoria_slug' => 'buff',
                'nombre' => 'Sudadera Sudcali Urban',
                'precio' => 850,
                'imagen' => '/imagenes/buff/sudcali_buff_4.jpeg',
                'descripcion' => 'Corte amplio oversize, gorro térmico y bolsa frontal.',
                'etiqueta' => '¡Invierno!'
            ],
            [
                'categoria_slug' => 'buff',
                'nombre' => 'Sudadera Sudcali Urban',
                'precio' => 850,
                'imagen' => '/imagenes/buff/sudcali_buff_5.jpeg',
                'descripcion' => 'Corte amplio oversize, gorro térmico y bolsa frontal.',
                'etiqueta' => '¡Invierno!'
            ],
            [
                'categoria_slug' => 'buff',
                'nombre' => 'Sudadera Sudcali Urban',
                'precio' => 850,
                'imagen' => '/imagenes/buff/sudcali_buff_6.jpeg',
                'descripcion' => 'Corte amplio oversize, gorro térmico y bolsa frontal.',
                'etiqueta' => '¡Invierno!'
            ],
            [
                'categoria_slug' => 'buff',
                'nombre' => 'Sudadera Sudcali Urban',
                'precio' => 850,
                'imagen' => '/imagenes/buff/sudcali_buff_7.jpeg',
                'descripcion' => 'Corte amplio oversize, gorro térmico y bolsa frontal.',
                'etiqueta' => '¡Invierno!'
            ],
            [
                'categoria_slug' => 'buff',
                'nombre' => 'Sudadera Sudcali Urban',
                'precio' => 850,
                'imagen' => '/imagenes/buff/sudcali_buff_8.jpeg',
                'descripcion' => 'Corte amplio oversize, gorro térmico y bolsa frontal.',
                'etiqueta' => '¡Invierno!'
            ],
            [
                'categoria_slug' => 'buff',
                'nombre' => 'Sudadera Sudcali Urban',
                'precio' => 850,
                'imagen' => '/imagenes/buff/sudcali_buff_9.jpeg',
                'descripcion' => 'Corte amplio oversize, gorro térmico y bolsa frontal.',
                'etiqueta' => '¡Invierno!'
            ],
            [
                'categoria_slug' => 'buff',
                'nombre' => 'Sudadera Sudcali Urban',
                'precio' => 850,
                'imagen' => '/imagenes/buff/sudcali_buff_10.jpeg',
                'descripcion' => 'Corte amplio oversize, gorro térmico y bolsa frontal.',
                'etiqueta' => '¡Invierno!'
            ],
            [
                'categoria_slug' => 'buff',
                'nombre' => 'Sudadera Sudcali Urban',
                'precio' => 850,
                'imagen' => '/imagenes/buff/sudcali_buff_11.jpeg',
                'descripcion' => 'Corte amplio oversize, gorro térmico y bolsa frontal.',
                'etiqueta' => '¡Invierno!'
            ],
            [
                'categoria_slug' => 'buff',
                'nombre' => 'Sudadera Sudcali Urban',
                'precio' => 850,
                'imagen' => '/imagenes/buff/sudcali_buff_12.jpeg',
                'descripcion' => 'Corte amplio oversize, gorro térmico y bolsa frontal.',
                'etiqueta' => '¡Invierno!'
            ],
            [
                'categoria_slug' => 'buff',
                'nombre' => 'Sudadera Sudcali Urban',
                'precio' => 850,
                'imagen' => '/imagenes/buff/sudcali_buff_13.jpeg',
                'descripcion' => 'Corte amplio oversize, gorro térmico y bolsa frontal.',
                'etiqueta' => '¡Invierno!'
            ],
            [
                'categoria_slug' => 'buff',
                'nombre' => 'Sudadera Sudcali Urban',
                'precio' => 850,
                'imagen' => '/imagenes/buff/sudcali_buff_14.jpeg',
                'descripcion' => 'Corte amplio oversize, gorro térmico y bolsa frontal.',
                'etiqueta' => '¡Invierno!'
            ],
            [
                'categoria_slug' => 'buff',
                'nombre' => 'Sudadera Sudcali Urban',
                'precio' => 850,
                'imagen' => '/imagenes/buff/sudcali_buff_15.jpeg',
                'descripcion' => 'Corte amplio oversize, gorro térmico y bolsa frontal.',
                'etiqueta' => '¡Invierno!'
            ],
            [
                'categoria_slug' => 'buff',
                'nombre' => 'Sudadera Sudcali Urban',
                'precio' => 850,
                'imagen' => '/imagenes/buff/sudcali_buff_16.jpeg',
                'descripcion' => 'Corte amplio oversize, gorro térmico y bolsa frontal.',
                'etiqueta' => '¡Invierno!'
            ],
            [
                'categoria_slug' => 'buff',
                'nombre' => 'Sudadera Sudcali Urban',
                'precio' => 850,
                'imagen' => '/imagenes/buff/sudcali_buff_17.jpeg',
                'descripcion' => 'Corte amplio oversize, gorro térmico y bolsa frontal.',
                'etiqueta' => '¡Invierno!'
            ],
            [
                'categoria_slug' => 'buff',
                'nombre' => 'Sudadera Sudcali Urban',
                'precio' => 850,
                'imagen' => '/imagenes/buff/sudcali_buff_18.jpeg',
                'descripcion' => 'Corte amplio oversize, gorro térmico y bolsa frontal.',
                'etiqueta' => '¡Invierno!'
            ],
            [
                'categoria_slug' => 'buff',
                'nombre' => 'Sudadera Sudcali Urban',
                'precio' => 850,
                'imagen' => '/imagenes/buff/sudcali_buff_19.jpeg',
                'descripcion' => 'Corte amplio oversize, gorro térmico y bolsa frontal.',
                'etiqueta' => '¡Invierno!'
            ],
            [
                'categoria_slug' => 'buff',
                'nombre' => 'Sudadera Sudcali Urban',
                'precio' => 850,
                'imagen' => '/imagenes/buff/sudcali_buff_20.jpeg',
                'descripcion' => 'Corte amplio oversize, gorro térmico y bolsa frontal.',
                'etiqueta' => '¡Invierno!'
            ],
            [
                'categoria_slug' => 'buff',
                'nombre' => 'Sudadera Sudcali Urban',
                'precio' => 850,
                'imagen' => '/imagenes/buff/sudcali_buff_21.jpeg',
                'descripcion' => 'Corte amplio oversize, gorro térmico y bolsa frontal.',
                'etiqueta' => '¡Invierno!'
            ],
            [
                'categoria_slug' => 'buff',
                'nombre' => 'Sudadera Sudcali Urban',
                'precio' => 850,
                'imagen' => '/imagenes/buff/sudcali_buff_22.jpeg',
                'descripcion' => 'Corte amplio oversize, gorro térmico y bolsa frontal.',
                'etiqueta' => '¡Invierno!'
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
        <a href="/catalogo?categoria=buff" class="boton-filtro {{ $categoriaActual === 'buff' ? 'activo' : '' }}">
            Buff
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