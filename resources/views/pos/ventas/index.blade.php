@extends('layouts.pos')

@section('titulo', 'Ventas del turno')

@section('contenido')
<section class="seccion-pos-ancha">
    <header class="encabezado-pagina-pos">
        <h2>Ventas del turno</h2>
        <p>
            Caja abierta desde {{ $corte->fecha_apertura->format('d/m/Y H:i') }} ·
            Efectivo ${{ number_format($corte->ventas_efectivo, 2) }} ·
            Tarjeta ${{ number_format($corte->ventas_tarjeta, 2) }}
        </p>
    </header>

    @if ($ventas->isEmpty())
        <div class="mensaje-catalogo-vacio">
            <p>Aún no hay ventas en este turno.</p>
            <a href="{{ route('pos.ventas.create') }}" class="boton-login boton-enlace-pos">Registrar la primera venta</a>
        </div>
    @else
        <div class="tabla-desplazable-pos">
            <table class="tabla-pos">
                <thead>
                    <tr>
                        <th scope="col">Folio</th>
                        <th scope="col">Hora</th>
                        <th scope="col">Artículos</th>
                        <th scope="col">Pago</th>
                        <th scope="col" class="numero">Total</th>
                        <th scope="col"><span class="solo-lector">Acciones</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($ventas as $venta)
                        <tr>
                            <td>#{{ str_pad($venta->id, 6, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ $venta->created_at->format('H:i') }}</td>
                            <td>{{ $venta->detalles_sum_cantidad }}</td>
                            <td><span class="insignia-pago">{{ ucfirst($venta->metodo_pago->value) }}</span></td>
                            <td class="numero">${{ number_format($venta->total, 2) }}</td>
                            <td><a href="{{ route('pos.ventas.show', $venta) }}">Ver ticket</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
@endsection
