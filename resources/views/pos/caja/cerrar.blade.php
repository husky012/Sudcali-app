@extends('layouts.pos')

@section('titulo', 'Cerrar caja')

@section('contenido')
<section class="seccion-pos-angosta">
    <header class="encabezado-pagina-pos">
        <h2>Cerrar caja</h2>
        <p>Turno abierto desde {{ $corte->fecha_apertura->format('d/m/Y H:i') }}</p>
    </header>

    <dl class="resumen-corte-pos">
        <div class="fila-resumen">
            <dt>Ventas registradas</dt>
            <dd>{{ $totalVentas }}</dd>
        </div>
        <div class="fila-resumen">
            <dt>Fondo inicial</dt>
            <dd>${{ number_format($corte->monto_inicial, 2) }}</dd>
        </div>
        <div class="fila-resumen">
            <dt>Ventas en efectivo</dt>
            <dd>${{ number_format($corte->ventas_efectivo, 2) }}</dd>
        </div>
        <div class="fila-resumen">
            <dt>Ventas con tarjeta</dt>
            <dd>${{ number_format($corte->ventas_tarjeta, 2) }}</dd>
        </div>
        <div class="fila-resumen fila-total">
            <dt>Efectivo esperado en caja</dt>
            <dd>${{ number_format($efectivoEsperado, 2) }}</dd>
        </div>
    </dl>

    <form action="{{ route('pos.caja.update') }}" method="POST" class="formulario-login">
        @csrf
        @method('PUT')

        <div class="grupo-campo">
            <label for="monto_final_real">Efectivo contado en caja (MXN)</label>
            <input
                type="number"
                id="monto_final_real"
                name="monto_final_real"
                class="campo-numero-pos"
                value="{{ old('monto_final_real') }}"
                min="0"
                step="0.01"
                inputmode="decimal"
                required
                autofocus
                data-esperado="{{ $efectivoEsperado }}"
            >
            <p id="diferenciaCorte" class="ayuda-campo-pos" aria-live="polite"></p>
            @error('monto_final_real')
                <p class="error-campo-pos">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="boton-login">Cerrar caja</button>
        <a href="{{ route('pos.ventas.create') }}" class="enlace-seguir-comprando">Seguir vendiendo</a>
    </form>
</section>
@endsection

@push('scripts')
<script>
    (() => {
        const campo = document.getElementById('monto_final_real');
        const salida = document.getElementById('diferenciaCorte');
        const esperado = Number(campo.dataset.esperado);
        const moneda = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' });

        campo.addEventListener('input', () => {
            if (campo.value === '') {
                salida.textContent = '';
                return;
            }

            const diferencia = Number(campo.value) - esperado;
            salida.textContent = diferencia === 0
                ? 'La caja cuadra exactamente.'
                : (diferencia > 0 ? 'Sobrante de ' : 'Faltante de ') + moneda.format(Math.abs(diferencia));
            salida.dataset.estado = diferencia === 0 ? 'cuadra' : (diferencia > 0 ? 'sobrante' : 'faltante');
        });
    })();
</script>
@endpush
