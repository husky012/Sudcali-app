@extends('layouts.pos')

@section('titulo', 'Abrir caja')

@section('contenido')
<section class="seccion-login">
    <div class="tarjeta-login">
        <header class="encabezado-login">
            <h2>Abrir caja</h2>
            <p>Cuenta el efectivo con el que inicias el turno.</p>
        </header>

        <form action="{{ route('pos.caja.store') }}" method="POST" class="formulario-login">
            @csrf

            <div class="grupo-campo">
                <label for="monto_inicial">Fondo inicial (MXN)</label>
                <input
                    type="number"
                    id="monto_inicial"
                    name="monto_inicial"
                    class="campo-numero-pos"
                    value="{{ old('monto_inicial', '500.00') }}"
                    min="0"
                    step="0.01"
                    inputmode="decimal"
                    required
                    autofocus
                >
                @error('monto_inicial')
                    <p class="error-campo-pos">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="boton-login">Abrir caja y comenzar a vender</button>
        </form>
    </div>
</section>
@endsection
