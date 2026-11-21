@extends('layouts.app')

@section('titulo', 'Sudcali - Confirmar Pedido')

@section('contenido')
<section class="seccion-carrito">
    <header class="encabezado-seccion">
        <h2>Confirmar Pedido</h2>
        <p>Dinos a dónde enviarlo y cómo quieres pagar</p>
    </header>

    @if ($errors->has('carrito'))
        <p class="alerta-pos alerta-error alerta-tienda" role="alert">
            {{ $errors->first('carrito') }} <a href="{{ route('carrito') }}">Ir al carrito</a>
        </p>
    @endif

    <form method="POST" action="{{ route('checkout.store') }}" class="contenedor-grid-carrito" id="formCheckout">
        @csrf

        <div class="lista-items-carrito">
            <!-- Dirección de envío -->
            <fieldset class="bloque-checkout">
                <legend>1. Dirección de envío</legend>

                @php($direccionElegida = old('direccion_id', $direcciones->first()?->id ?? 'nueva'))

                @foreach ($direcciones as $direccion)
                    <label class="opcion-checkout">
                        <input type="radio" name="direccion_id" value="{{ $direccion->id }}" @checked((string) $direccionElegida === (string) $direccion->id)>
                        <span>
                            <strong>{{ $direccion->calle_numero }}</strong><br>
                            Col. {{ $direccion->colonia }}, C.P. {{ $direccion->codigo_postal }}, {{ $direccion->ciudad }}
                            @if ($direccion->referencias)
                                <small>{{ $direccion->referencias }}</small>
                            @endif
                        </span>
                    </label>
                @endforeach

                <label class="opcion-checkout">
                    <input type="radio" name="direccion_id" value="nueva" @checked($direccionElegida === 'nueva')>
                    <span><strong>{{ $direcciones->isEmpty() ? 'Captura tu dirección' : 'Usar una dirección nueva' }}</strong></span>
                </label>
                @include('pos.partials.campo-error', ['campo' => 'direccion_id'])

                <div id="direccionNueva" class="rejilla-campos-pos" @if ($direccionElegida !== 'nueva') hidden @endif>
                    <div class="grupo-campo campo-ancho-pos">
                        <label for="calle_numero">Calle y número *</label>
                        <input type="text" id="calle_numero" name="calle_numero" value="{{ old('calle_numero') }}" maxlength="150" autocomplete="street-address">
                        @include('pos.partials.campo-error', ['campo' => 'calle_numero'])
                    </div>
                    <div class="grupo-campo">
                        <label for="colonia">Colonia *</label>
                        <input type="text" id="colonia" name="colonia" value="{{ old('colonia') }}" maxlength="100">
                        @include('pos.partials.campo-error', ['campo' => 'colonia'])
                    </div>
                    <div class="grupo-campo">
                        <label for="codigo_postal">Código postal *</label>
                        <input type="text" id="codigo_postal" name="codigo_postal" value="{{ old('codigo_postal') }}" inputmode="numeric" maxlength="5" placeholder="23000" autocomplete="postal-code">
                        @include('pos.partials.campo-error', ['campo' => 'codigo_postal'])
                    </div>
                    <div class="grupo-campo campo-ancho-pos">
                        <label for="referencias">Referencias</label>
                        <input type="text" id="referencias" name="referencias" value="{{ old('referencias') }}" maxlength="255" placeholder="Casa azul, frente al parque…">
                    </div>
                </div>
            </fieldset>

            <!-- Forma de pago -->
            <fieldset class="bloque-checkout">
                <legend>2. Forma de pago</legend>
                <label class="opcion-checkout">
                    <input type="radio" name="metodo_pago" value="transferencia" @checked(old('metodo_pago', 'transferencia') === 'transferencia')>
                    <span><strong>Transferencia bancaria</strong><br><small>Te enviaremos los datos para transferir; preparamos tu pedido al confirmar el pago.</small></span>
                </label>
                <label class="opcion-checkout">
                    <input type="radio" name="metodo_pago" value="efectivo" @checked(old('metodo_pago') === 'efectivo')>
                    <span><strong>Efectivo contra entrega</strong><br><small>Pagas al recibir tu pedido.</small></span>
                </label>
                @include('pos.partials.campo-error', ['campo' => 'metodo_pago'])
            </fieldset>
        </div>

        <!-- Resumen -->
        <aside class="resumen-pedido">
            <h3>Tu pedido</h3>
            <ul class="lista-resumen-checkout">
                @foreach ($renglones as $renglon)
                    <li>
                        <span>{{ $renglon['cantidad'] }} × {{ $renglon['variante']->producto->titulo }} <small>({{ $renglon['variante']->talla }})</small></span>
                        <span>${{ number_format($renglon['importe'], 2) }}</span>
                    </li>
                @endforeach
            </ul>
            <div class="desglose-resumen">
                <div class="fila-resumen">
                    <span>Subtotal</span>
                    <span>${{ number_format($totales['subtotal'], 2) }} MXN</span>
                </div>
                <div class="fila-resumen">
                    <span>Envío</span>
                    <span>{{ $totales['envio'] > 0 ? '$'.number_format($totales['envio'], 2).' MXN' : 'Gratis' }}</span>
                </div>
                <hr class="divisor-resumen">
                <div class="fila-resumen fila-total">
                    <span>Total</span>
                    <span class="precio-total">${{ number_format($totales['total'], 2) }} MXN</span>
                </div>
            </div>

            <button type="submit" class="boton-finalizar-compra" id="botonConfirmar">Confirmar pedido</button>
            <a href="{{ route('carrito') }}" class="enlace-seguir-comprando">← Volver al carrito</a>
        </aside>
    </form>
</section>
@endsection

@push('scripts')
<script>
    (() => {
        const formulario = document.getElementById('formCheckout');
        const bloqueNueva = document.getElementById('direccionNueva');

        formulario.addEventListener('change', (evento) => {
            if (evento.target.name === 'direccion_id') {
                bloqueNueva.hidden = evento.target.value !== 'nueva';
            }
        });

        formulario.addEventListener('submit', () => {
            const boton = document.getElementById('botonConfirmar');
            boton.disabled = true;
            boton.textContent = 'Registrando pedido…';
        });
    })();
</script>
@endpush
