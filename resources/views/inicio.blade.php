@extends('layouts.app')

@section('titulo', 'Sudcali - Inicio | La Paz, B.C.S.')

@section('contenido')
    <!-- Sección Principal / Banner -->
    <section class="seccion-bienvenida">
        <div class="contenido-bienvenida">
            <h2>Moda & Estilo en La Paz, B.C.S.</h2>
            <p>Explora nuestro catálogo exclusivo de ropa y accesorios con entregas locales directas a tu domicilio.</p>
            <a href="{{ route('catalogo') }}" class="boton-banner">Ver Catálogo Completo</a>
        </div>
    </section>

    <!-- Productos destacados desde la base de datos -->
    <section class="seccion-catalogo" id="catalogo">
        <header class="encabezado-seccion">
            <h2>Catálogo Destacado</h2>
            <p>Nuestros artículos populares disponibles en tienda</p>
        </header>

        @if ($destacados->isEmpty())
            <div class="mensaje-catalogo-vacio">
                <p>Muy pronto tendremos productos disponibles.</p>
            </div>
        @else
            <div class="cuadricula-productos">
                @foreach ($destacados as $producto)
                    <x-tarjeta-producto :producto="$producto" etiqueta="¡Destacado!" />
                @endforeach
            </div>
        @endif
    </section>
@endsection
