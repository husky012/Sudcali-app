@extends('layouts.pos')

@section('titulo', 'Ticket #'.$venta->id)

@section('contenido')
<section class="seccion-pos-angosta">
    <article class="ticket-pos" aria-label="Ticket de venta">
        <header class="encabezado-ticket">
            <h2>SUDCALI</h2>
            <p>{{ $venta->empleado?->sucursal->nombre }}</p>
            <p>{{ $venta->empleado?->sucursal->direccion }}</p>
            <p>Folio #{{ str_pad($venta->id, 6, '0', STR_PAD_LEFT) }} · {{ $venta->created_at->format('d/m/Y H:i') }}</p>
            <p>Le atendió: {{ $venta->empleado?->nombreCompleto() }}</p>
        </header>

        <table class="renglones-ticket">
            <thead>
                <tr>
                    <th scope="col">Cant.</th>
                    <th scope="col">Artículo</th>
                    <th scope="col" class="numero">Importe</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($venta->detalles as $detalle)
                    <tr>
                        <td>{{ $detalle->cantidad }}</td>
                        <td>
                            {{ $detalle->inventario->producto->titulo }}
                            <small>Talla {{ $detalle->inventario->talla }} · ${{ number_format($detalle->precio_unitario, 2) }} c/u</small>
                        </td>
                        <td class="numero">${{ number_format($detalle->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <dl class="totales-ticket">
            <div class="fila-resumen fila-total">
                <dt>Total</dt>
                <dd>${{ number_format($venta->total, 2) }}</dd>
            </div>
            <div class="fila-resumen">
                <dt>Pago</dt>
                <dd>{{ ucfirst($venta->metodo_pago->value) }}</dd>
            </div>
            @if (session()->has('cambio') && session('cambio') !== null)
                <div class="fila-resumen">
                    <dt>Recibido</dt>
                    <dd>${{ number_format(session('monto_recibido'), 2) }}</dd>
                </div>
                <div class="fila-resumen cambio-ticket">
                    <dt>Cambio</dt>
                    <dd>${{ number_format(session('cambio'), 2) }}</dd>
                </div>
            @endif
        </dl>

        <footer class="pie-ticket">
            <p>¡Gracias por apoyar lo local!</p>
            <p>Hecho en La Paz, B.C.S.</p>
        </footer>
    </article>

    <div class="acciones-pos no-imprimir">
        <a href="{{ route('pos.ventas.create') }}" class="boton-login boton-enlace-pos" autofocus>Nueva venta</a>
        <button type="button" class="boton-secundario-pos boton-ancho-pos" onclick="window.print()">Imprimir ticket</button>
    </div>
</section>
@endsection

@if (session('exito'))
    @push('scripts')
    <script>
        try { sessionStorage.removeItem('sudcali-pos-ticket'); } catch (e) {}
    </script>
    @endpush
@endif
