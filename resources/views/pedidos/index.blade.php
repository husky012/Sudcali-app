@extends('layouts.app')

@section('titulo', 'Sudcali - Mis Pedidos')

@section('contenido')
<section class="seccion-carrito">
    <header class="encabezado-seccion">
        <h2>Mis Pedidos</h2>
        <p>Consulta el estado de tus compras</p>
    </header>

    @if ($pedidos->isEmpty())
        <div class="mensaje-catalogo-vacio">
            <p>Aún no has hecho ningún pedido.</p>
            <a href="{{ route('catalogo') }}" class="boton-filtro">Ir al catálogo</a>
        </div>
    @else
        <div class="tabla-desplazable-pos">
            <table class="tabla-pos">
                <thead>
                    <tr>
                        <th scope="col">Pedido</th>
                        <th scope="col">Fecha</th>
                        <th scope="col" class="numero">Piezas</th>
                        <th scope="col" class="numero">Total</th>
                        <th scope="col">Estado</th>
                        <th scope="col"><span class="solo-lector">Detalle</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pedidos as $pedido)
                        <tr>
                            <td>#{{ str_pad($pedido->id, 6, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ $pedido->created_at->format('d/m/Y') }}</td>
                            <td class="numero">{{ $pedido->detalles_sum_cantidad }}</td>
                            <td class="numero">${{ number_format($pedido->total, 2) }}</td>
                            <td>@include('pedidos.partials.estado', ['estado' => $pedido->estado_envio])</td>
                            <td><a href="{{ route('pedidos.show', $pedido) }}">Ver seguimiento</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @include('pos.partials.paginacion', ['paginador' => $pedidos])
    @endif
</section>
@endsection
