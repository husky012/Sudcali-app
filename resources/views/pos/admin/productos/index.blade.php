@extends('layouts.pos')

@section('titulo', 'Productos')

@section('contenido')
<section class="seccion-pos-ancha seccion-admin-pos">
    <header class="encabezado-admin-pos">
        <div class="encabezado-pagina-pos">
            <h2>Productos</h2>
            <p>{{ $productos->total() }} {{ $productos->total() === 1 ? 'producto' : 'productos' }} en el catálogo</p>
        </div>
        <a href="{{ route('pos.admin.productos.create') }}" class="boton-login boton-enlace-pos boton-compacto-pos">+ Nuevo producto</a>
    </header>

    <form method="GET" class="filtros-admin-pos" role="search">
        <label class="solo-lector" for="buscar">Buscar</label>
        <input type="search" id="buscar" name="buscar" value="{{ $filtros['buscar'] ?? '' }}" placeholder="Nombre del producto">

        <label class="solo-lector" for="categoria">Categoría</label>
        <select id="categoria" name="categoria">
            <option value="">Todas las categorías</option>
            @foreach ($categorias as $categoria)
                <option value="{{ $categoria->id }}" @selected((int) ($filtros['categoria'] ?? 0) === $categoria->id)>{{ $categoria->nombre }}</option>
            @endforeach
        </select>

        <label class="solo-lector" for="estado">Estado</label>
        <select id="estado" name="estado">
            <option value="">Visibles y ocultos</option>
            <option value="activos" @selected(($filtros['estado'] ?? '') === 'activos')>Solo visibles</option>
            <option value="inactivos" @selected(($filtros['estado'] ?? '') === 'inactivos')>Solo ocultos</option>
        </select>

        <button type="submit" class="boton-secundario-pos">Filtrar</button>
        @if (array_filter($filtros))
            <a href="{{ route('pos.admin.productos.index') }}" class="enlace-limpiar-pos">Limpiar</a>
        @endif
    </form>

    @if ($productos->isEmpty())
        <div class="mensaje-catalogo-vacio"><p>No hay productos con esos filtros.</p></div>
    @else
        <div class="tabla-desplazable-pos">
            <table class="tabla-pos">
                <thead>
                    <tr>
                        <th scope="col">Producto</th>
                        <th scope="col">Categoría</th>
                        <th scope="col" class="numero">Precio</th>
                        <th scope="col" class="numero">Tallas</th>
                        <th scope="col" class="numero">Existencias</th>
                        <th scope="col">Estado</th>
                        <th scope="col"><span class="solo-lector">Acciones</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($productos as $producto)
                        <tr @class(['fila-inactiva-pos' => ! $producto->activo])>
                            <td>
                                <div class="celda-persona-pos">
                                    @if ($producto->imagen)
                                        <img src="{{ $producto->imagen }}" alt="" class="miniatura-producto-pos" loading="lazy">
                                    @endif
                                    <strong>{{ $producto->titulo }}</strong>
                                </div>
                            </td>
                            <td>{{ $producto->categoria->nombre }}</td>
                            <td class="numero">${{ number_format($producto->precio, 2) }}</td>
                            <td class="numero">{{ $producto->inventario_count }}</td>
                            <td class="numero">{{ (int) $producto->inventario_sum_stock }}</td>
                            <td>
                                <span @class(['insignia-pago', 'insignia-activa-pos' => $producto->activo, 'insignia-inactiva-pos' => ! $producto->activo])>
                                    {{ $producto->activo ? 'Visible' : 'Oculto' }}
                                </span>
                            </td>
                            <td class="acciones-tabla-pos">
                                <a href="{{ route('pos.admin.productos.edit', $producto) }}">Editar</a>
                                <form method="POST" action="{{ route('pos.admin.productos.destroy', $producto) }}"
                                      onsubmit="return confirm('¿Eliminar {{ addslashes($producto->titulo) }}? Si ya se vendió, solo se ocultará.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="boton-peligro-texto-pos">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @include('pos.partials.paginacion', ['paginador' => $productos])
    @endif
</section>
@endsection
