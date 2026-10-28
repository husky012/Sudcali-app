@extends('layouts.app')

@section('titulo', 'Sudcali - Inicio | La Paz, B.C.S.')

@section('contenido')
    @php
        // Arreglo de productos para alimentar las tarjetas dinámicamente
        $productos = [
            [
                'id' => 1,
                'nombre' => 'Playera Sudcali Clásica',
                'precio' => 250,
                'imagen' => '/imagenes/camisas/playera_sudcali_1.jpeg',
                'descripcion' => 'Algodón 100% premium. Diseñada localmente en La Paz. Disponible en todas las tallas.',
                'etiqueta' => '¡Destacado!'
            ],
            [
                'id' => 2,
                'nombre' => 'Playera Sudcali Clásica',
                'precio' => 250,
                'imagen' => '/imagenes/camisas/playera_sudcali_2.jpeg',
                'descripcion' => 'Algodón 100% premium. Diseñada localmente en La Paz. Disponible en todas las tallas.',
                'etiqueta' => '¡Destacado!'
            ],
            [
                'id' => 3,
                'nombre' => 'Playera Sudcali Clásica',
                'precio' => 250,
                'imagen' => '/imagenes/camisas/playera_sudcali_3.jpeg',
                'descripcion' => 'Algodón 100% premium. Diseñada localmente en La Paz. Disponible en todas las tallas.',
                'etiqueta' => '¡Destacado!'
            ],
            [
                'id' => 100,
                'nombre' => 'Playera Sudcali Clásica',
                'precio' => 250,
                'imagen' => '/imagenes/gorras/gorra_sudcali_1.jpeg',
                'descripcion' => 'Algodón 100% premium. Diseñada localmente en La Paz. Disponible en todas las tallas.',
                'etiqueta' => '¡Destacado!'
            ],
            [
                'id' => 101,
                'nombre' => 'Playera Sudcali Clásica',
                'precio' => 250,
                'imagen' => '/imagenes/gorras/gorra_sudcali_2.jpeg',
                'descripcion' => 'Algodón 100% premium. Diseñada localmente en La Paz. Disponible en todas las tallas.',
                'etiqueta' => '¡Destacado!'
            ],
            [
                'id' => 102,
                'nombre' => 'Playera Sudcali Clásica',
                'precio' => 250,
                'imagen' => '/imagenes/gorras/gorra_sudcali_3.jpeg',
                'descripcion' => 'Algodón 100% premium. Diseñada localmente en La Paz. Disponible en todas las tallas.',
                'etiqueta' => '¡Destacado!'
            ],
            [
                'id' => 200,
                'nombre' => 'Playera Sudcali Clásica',
                'precio' => 250,
                'imagen' => '/imagenes/buff/sudcali_buff_1.jpeg',
                'descripcion' => 'Algodón 100% premium. Diseñada localmente en La Paz. Disponible en todas las tallas.',
                'etiqueta' => '¡Destacado!'
            ],
            [
                'id' => 201,
                'nombre' => 'Playera Sudcali Clásica',
                'precio' => 250,
                'imagen' => '/imagenes/buff/sudcali_buff_2.jpeg',
                'descripcion' => 'Algodón 100% premium. Diseñada localmente en La Paz. Disponible en todas las tallas.',
                'etiqueta' => '¡Destacado!'
            ],
            [
                'id' => 202,
                'nombre' => 'Playera Sudcali Clásica',
                'precio' => 250,
                'imagen' => '/imagenes/buff/sudcali_buff_3.jpeg',
                'descripcion' => 'Algodón 100% premium. Diseñada localmente en La Paz. Disponible en todas las tallas.',
                'etiqueta' => '¡Destacado!'
            ],
        ];
    @endphp

    <!-- Sección Principal / Banner -->
    <section class="seccion-bienvenida">
        <div class="contenido-bienvenida">
            <h2>Moda & Estilo en La Paz, B.C.S.</h2>
            <p>Explora nuestro catálogo exclusivo de ropa y accesorios con entregas locales directas a tu domicilio.</p>
            <a href="/catalogo" class="boton-banner">Ver Catálogo Completo</a>
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
                <a href="/producto/1" style="text-decoration: none; color: inherit;">
                    @include('components.tarjeta-producto', ['producto' => $producto])
                </a>
            @endforeach
        </div>
    </section>
@endsection