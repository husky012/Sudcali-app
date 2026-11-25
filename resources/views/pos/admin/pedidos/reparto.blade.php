@extends('layouts.pos')

@section('titulo', 'Reparto del pedido #'.$pedido->id)

@section('contenido')
<section class="seccion-pos-ancha seccion-reparto" id="reparto"
         data-url="{{ route('pos.admin.pedidos.ubicacion', $pedido) }}"
         data-olvidar="{{ route('pos.admin.pedidos.ubicacion.olvidar', $pedido) }}"
         data-token="{{ csrf_token() }}">
    <a href="{{ route('pos.admin.pedidos.index') }}" class="enlace-volver-pos">← Pedidos en línea</a>

    <header class="encabezado-pagina-pos">
        <h2>Reparto del pedido #{{ str_pad($pedido->id, 6, '0', STR_PAD_LEFT) }}</h2>
        <p>
            {{ $pedido->cliente?->nombre }} {{ $pedido->cliente?->apellido_paterno }} · {{ $pedido->cliente?->telefono ?? 'sin teléfono' }}<br>
            {{ $pedido->direccionEnvio?->calle_numero }}, Col. {{ $pedido->direccionEnvio?->colonia }}, C.P. {{ $pedido->direccionEnvio?->codigo_postal }}
            @if ($pedido->direccionEnvio?->referencias)
                <br><small>{{ $pedido->direccionEnvio->referencias }}</small>
            @endif
        </p>
        @if ($pedido->metodo_pago->value === 'efectivo')
            <p><strong>Cobrar al entregar: {{ '$'.number_format($pedido->total, 2) }}</strong></p>
        @endif
    </header>

    @if ($pedido->direccionEnvio && ! $pedido->direccionEnvio->coordenadas())
        <section class="panel-mapa" data-domicilio="{{ route('pos.admin.pedidos.domicilio', $pedido) }}" data-token="{{ csrf_token() }}"
             data-latitud="{{ $pedido->direccionEnvio->latitud }}" data-longitud="{{ $pedido->direccionEnvio->longitud }}">
            <h3>Falta marcar el domicilio</h3>
            <div class="mapa-sudcali" role="application" aria-label="Mapa para marcar el domicilio" hidden></div>
            <p class="aviso-mapa" role="status">El cliente no lo ha marcado. Márcalo tú para trazar el camino.</p>
            <button type="button" class="boton-secundario-pos" data-abrir>{{ $pedido->direccionEnvio->coordenadas() ? 'Cambiar el punto' : 'Marcar en el mapa' }}</button>
            <button type="button" class="boton-secundario-pos" data-guardar hidden>Guardar domicilio</button>
        </section>
    @endif

    <div class="paneles-reparto">
        <section class="tarjeta-pedido-pos">
            <h3>1. Compartir mi ubicación</h3>
            <p class="aviso-mapa">El cliente la ve en un mapa. Solo se guarda la última y se borra al entregar o al detenerla.</p>
            <div id="mapaReparto" class="mapa-sudcali" role="img" aria-label="Mapa con tu ubicación" hidden></div>
            <p id="avisoUbicacion" class="aviso-mapa" role="status">No estás compartiendo tu ubicación.</p>
            <div class="acciones-pedido-pos">
                <button type="button" id="botonUbicacion" class="boton-secundario-pos">Compartir mi ubicación</button>
            </div>
        </section>

        <form method="POST" action="{{ route('pos.admin.pedidos.entrega', $pedido) }}" enctype="multipart/form-data" class="tarjeta-pedido-pos" id="formEntrega">
            @csrf
            <h3>2. Foto de la entrega</h3>
            <p class="aviso-mapa">Toma el paquete ya entregado. Evita fotografiar a personas.</p>

            <video id="videoCamara" class="foto-entrega" playsinline muted hidden></video>
            <img id="vistaFoto" class="foto-entrega" alt="Foto que se va a guardar" hidden>

            <div class="acciones-pedido-pos acciones-camara">
                <button type="button" id="botonCamara" class="boton-secundario-pos">Abrir cámara</button>
                <button type="button" id="botonTomar" class="boton-secundario-pos" hidden>Tomar foto</button>
            </div>

            <label for="foto">O elige una imagen</label>
            <input type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/webp" capture="environment" required>
            @include('pos.partials.campo-error', ['campo' => 'foto'])
            <p id="avisoCamara" class="aviso-mapa" role="status"></p>

            <button type="submit" id="botonEntregar" class="boton-login">Entregar pedido</button>
        </form>
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('js/mapa.js') }}" defer></script>
<script src="{{ asset('js/reparto.js') }}" defer></script>
@endpush
