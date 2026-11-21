@extends('layouts.pos')

@section('titulo', 'Inventario')

@section('contenido')
<section class="seccion-pos-ancha seccion-admin-pos">
    <header class="encabezado-admin-pos">
        <div class="encabezado-pagina-pos">
            <h2>Inventario</h2>
            <p>Registra entradas de mercancía, salidas (merma, devolución a proveedor) o corrige con un conteo físico.</p>
        </div>
    </header>

    <div class="tarjetas-resumen-pos">
        <div class="tarjeta-resumen-pos">
            <span>Piezas en existencia</span>
            <strong>{{ number_format($resumen['piezas']) }}</strong>
        </div>
        <a href="{{ route('pos.admin.inventario.index', ['existencias' => 'bajo']) }}" class="tarjeta-resumen-pos tarjeta-aviso-pos">
            <span>Existencia baja (1–{{ $stockBajo }})</span>
            <strong>{{ $resumen['bajo'] }}</strong>
        </a>
        <a href="{{ route('pos.admin.inventario.index', ['existencias' => 'agotado']) }}" class="tarjeta-resumen-pos tarjeta-peligro-pos">
            <span>Agotados</span>
            <strong>{{ $resumen['agotado'] }}</strong>
        </a>
    </div>

    <form method="GET" class="filtros-admin-pos" role="search">
        <label class="solo-lector" for="buscar">Buscar</label>
        <input type="search" id="buscar" name="buscar" value="{{ $filtros['buscar'] ?? '' }}" placeholder="Producto o código de barras" autofocus>

        <label class="solo-lector" for="categoria">Categoría</label>
        <select id="categoria" name="categoria">
            <option value="">Todas las categorías</option>
            @foreach ($categorias as $categoria)
                <option value="{{ $categoria->id }}" @selected((int) ($filtros['categoria'] ?? 0) === $categoria->id)>{{ $categoria->nombre }}</option>
            @endforeach
        </select>

        <label class="solo-lector" for="existencias">Existencias</label>
        <select id="existencias" name="existencias">
            <option value="">Todas las existencias</option>
            <option value="bajo" @selected(($filtros['existencias'] ?? '') === 'bajo')>Existencia baja</option>
            <option value="agotado" @selected(($filtros['existencias'] ?? '') === 'agotado')>Agotados</option>
        </select>

        <button type="submit" class="boton-secundario-pos">Filtrar</button>
        @if (array_filter($filtros))
            <a href="{{ route('pos.admin.inventario.index') }}" class="enlace-limpiar-pos">Limpiar</a>
        @endif
    </form>

    @if ($variantes->isEmpty())
        <div class="mensaje-catalogo-vacio"><p>No hay existencias con esos filtros.</p></div>
    @else
        <div class="tabla-desplazable-pos">
            <table class="tabla-pos">
                <thead>
                    <tr>
                        <th scope="col">Producto</th>
                        <th scope="col">Talla / color</th>
                        <th scope="col">Código</th>
                        <th scope="col" class="numero">Existencias</th>
                        <th scope="col">Ajustar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($variantes as $variante)
                        @php($bolsa = 'ajuste'.$variante->id)
                        <tr @class(['fila-inactiva-pos' => ! $variante->producto->activo])>
                            <td>
                                <strong>{{ $variante->producto->titulo }}</strong><br>
                                <small>{{ $variante->producto->categoria->nombre }}{{ $variante->producto->activo ? '' : ' · oculto' }}</small>
                            </td>
                            <td>{{ $variante->talla }} · {{ $variante->color }}</td>
                            <td><code>{{ $variante->codigo_barras }}</code></td>
                            <td class="numero">
                                <span @class([
                                    'existencia-pos',
                                    'existencia-agotada-pos' => $variante->stock === 0,
                                    'existencia-baja-pos' => $variante->stock > 0 && $variante->stock <= $stockBajo,
                                ])>{{ $variante->stock }}</span>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('pos.admin.inventario.update', $variante) }}" class="formulario-ajuste-pos">
                                    @csrf
                                    @method('PATCH')
                                    <label class="solo-lector" for="operacion{{ $variante->id }}">Operación</label>
                                    <select id="operacion{{ $variante->id }}" name="operacion">
                                        <option value="entrada">+ Entrada</option>
                                        <option value="salida">− Salida</option>
                                        <option value="conteo">= Conteo físico</option>
                                    </select>
                                    <label class="solo-lector" for="cantidad{{ $variante->id }}">Cantidad</label>
                                    <input type="number" id="cantidad{{ $variante->id }}" name="cantidad" min="0" step="1" required placeholder="0">
                                    <button type="submit" class="boton-secundario-pos">Aplicar</button>
                                </form>
                                @include('pos.partials.campo-error', ['campo' => 'cantidad', 'bolsa' => $bolsa])
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @include('pos.partials.paginacion', ['paginador' => $variantes])
    @endif
</section>
@endsection
