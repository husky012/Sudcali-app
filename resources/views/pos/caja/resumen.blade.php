@extends('layouts.pos')

@section('titulo', 'Corte de caja #'.$corte->id)

@section('contenido')
<section class="seccion-pos-angosta">
    <header class="encabezado-pagina-pos">
        <h2>Corte de caja #{{ $corte->id }}</h2>
        <p>
            {{ $corte->empleado->nombreCompleto() }} · {{ $corte->sucursal->nombre }}<br>
            {{ $corte->fecha_apertura->format('d/m/Y H:i') }}
            → {{ $corte->fecha_cierre?->format('d/m/Y H:i') ?? 'abierto' }}
        </p>
    </header>

    <dl class="resumen-corte-pos">
        @foreach ($metodos as $metodo)
            @php($fila = $ventasPorMetodo->get($metodo->value))
            <div class="fila-resumen">
                <dt>Ventas en {{ $metodo->value }} ({{ $fila->cantidad ?? 0 }})</dt>
                <dd>${{ number_format($fila->importe ?? 0, 2) }}</dd>
            </div>
        @endforeach
        <div class="fila-resumen">
            <dt>Fondo inicial</dt>
            <dd>${{ number_format($corte->monto_inicial, 2) }}</dd>
        </div>
        <div class="fila-resumen">
            <dt>Efectivo esperado</dt>
            <dd>${{ number_format($corte->monto_final_esperado ?? 0, 2) }}</dd>
        </div>
        <div class="fila-resumen">
            <dt>Efectivo contado</dt>
            <dd>${{ number_format($corte->monto_final_real ?? 0, 2) }}</dd>
        </div>
        <div class="fila-resumen fila-total">
            <dt>Diferencia</dt>
            <dd @class([
                'diferencia-pos',
                'faltante' => $corte->diferencia < 0,
                'sobrante' => $corte->diferencia > 0,
            ])>
                {{ $corte->diferencia < 0 ? '-' : '' }}${{ number_format(abs($corte->diferencia ?? 0), 2) }}
            </dd>
        </div>
    </dl>

    <div class="acciones-pos">
        <a href="{{ route('pos.caja.create') }}" class="boton-login boton-enlace-pos">Abrir nueva caja</a>
        <form method="POST" action="{{ route('pos.logout') }}">
            @csrf
            <button type="submit" class="boton-secundario-pos boton-ancho-pos">Terminar turno y salir</button>
        </form>
    </div>
</section>
@endsection
