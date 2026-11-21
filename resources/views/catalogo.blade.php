@extends('layouts.app')

@section('titulo', 'Sudcali - Catálogo Completo')

@section('contenido')
<section class="seccion-catalogo-pagina">
    <header class="encabezado-seccion">
        <h2>Explora nuestro Catálogo</h2>
        <p>Encuentra ropa y accesorios con identidad sudcaliforniana</p>
    </header>

    <!-- Barra de Filtros / Categorías -->
    <nav class="barra-categorias-catalogo" aria-label="Filtro de categorías">
        <a href="{{ route('catalogo', array_filter(['buscar' => $buscar])) }}" @class(['boton-filtro', 'activo' => $categoriaActual === 'todos'])>
            Todos
        </a>
        @foreach ($categorias as $categoria)
            <a href="{{ route('catalogo', array_filter(['categoria' => $categoria->slug, 'buscar' => $buscar])) }}" @class(['boton-filtro', 'activo' => $categoriaActual === $categoria->slug])>
                {{ $categoria->nombre }}
            </a>
        @endforeach
    </nav>

    <form method="GET" action="{{ route('catalogo') }}" class="busqueda-catalogo" role="search">
        @if ($categoriaActual !== 'todos')
            <input type="hidden" name="categoria" value="{{ $categoriaActual }}">
        @endif
        <label for="buscarCatalogo" class="solo-lector">Buscar productos</label>
        <input type="search" id="buscarCatalogo" name="buscar" value="{{ $buscar }}" placeholder="Buscar playeras, gorras, buffs…">
        <button type="submit" class="boton-filtro">Buscar</button>
    </form>

    <!-- Rejilla de Productos con Componente Reutilizable -->
    @if ($productos->isNotEmpty())
        <div class="cuadricula-productos">
            @foreach ($productos as $producto)
                <x-tarjeta-producto :producto="$producto" />
            @endforeach
        </div>

        @include('pos.partials.paginacion', ['paginador' => $productos])
    @else
        <div class="mensaje-catalogo-vacio">
            <p>No encontramos productos con esos filtros.</p>
            <a href="{{ route('catalogo') }}" class="boton-filtro">Ver todo el catálogo</a>
        </div>
    @endif
</section>
@endsection
