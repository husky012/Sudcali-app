@extends('layouts.pos')

@section('titulo', 'Categorías')

@section('contenido')
<section class="seccion-pos-ancha seccion-admin-pos">
    <header class="encabezado-admin-pos">
        <div class="encabezado-pagina-pos">
            <h2>Categorías</h2>
            <p>Agrupan los productos en la tienda y en el punto de venta.</p>
        </div>
        <a href="{{ route('pos.admin.categorias.create') }}" class="boton-login boton-enlace-pos boton-compacto-pos">+ Nueva categoría</a>
    </header>

    @if ($categorias->isEmpty())
        <div class="mensaje-catalogo-vacio"><p>Aún no hay categorías.</p></div>
    @else
        <div class="tabla-desplazable-pos">
            <table class="tabla-pos">
                <thead>
                    <tr>
                        <th scope="col">Nombre</th>
                        <th scope="col">Identificador</th>
                        <th scope="col" class="numero">Productos</th>
                        <th scope="col"><span class="solo-lector">Acciones</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($categorias as $categoria)
                        <tr>
                            <td><strong>{{ $categoria->nombre }}</strong></td>
                            <td><code>{{ $categoria->slug }}</code></td>
                            <td class="numero">
                                <a href="{{ route('pos.admin.productos.index', ['categoria' => $categoria->id]) }}">{{ $categoria->productos_count }}</a>
                            </td>
                            <td class="acciones-tabla-pos">
                                <a href="{{ route('pos.admin.categorias.edit', $categoria) }}">Editar</a>
                                @if ($categoria->productos_count === 0)
                                    <form method="POST" action="{{ route('pos.admin.categorias.destroy', $categoria) }}"
                                          data-confirmar="¿Eliminar la categoría {{ $categoria->nombre }}?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="boton-peligro-texto-pos">Eliminar</button>
                                    </form>
                                @else
                                    <span class="texto-deshabilitado-pos" title="Tiene productos asignados">Eliminar</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
@endsection
