@extends('layouts.pos')

@section('titulo', 'Vender')

@section('contenido')
<section
    class="pantalla-venta-pos"
    id="puntoVenta"
    data-url-busqueda="{{ route('pos.inventario.buscar') }}"
>
    <!-- Columna izquierda: búsqueda y productos -->
    <div class="columna-catalogo-pos">
        <form id="formBusqueda" class="barra-busqueda-pos" role="search">
            <label for="campoBusqueda" class="solo-lector">Escanea el código de barras o busca un producto</label>
            <input
                type="search"
                id="campoBusqueda"
                placeholder="Escanea el código o escribe el nombre…"
                autocomplete="off"
                autofocus
            >
            <label for="filtroCategoria" class="solo-lector">Categoría</label>
            <select id="filtroCategoria">
                <option value="">Todas</option>
                @foreach ($categorias as $categoria)
                    <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                @endforeach
            </select>
        </form>

        <p id="estadoBusqueda" class="estado-busqueda-pos" aria-live="polite"></p>
        <div id="resultadosBusqueda" class="cuadricula-variantes-pos"></div>
    </div>

    <!-- Columna derecha: ticket en curso y cobro -->
    <aside class="columna-ticket-pos" aria-labelledby="tituloTicket">
        <h2 id="tituloTicket">Venta actual</h2>

        @if ($errors->any())
            <div class="alerta-pos alerta-error" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <p id="ticketVacio" class="ticket-vacio-pos">Escanea o selecciona productos para agregarlos.</p>
        <ul id="listaTicket" class="lista-ticket-pos"></ul>

        <form id="formCobro" action="{{ route('pos.ventas.store') }}" method="POST" class="formulario-cobro-pos">
            @csrf
            <div id="camposArticulos"></div>

            <div class="fila-resumen">
                <span>Artículos</span>
                <span id="totalArticulos">0</span>
            </div>
            <div class="fila-resumen fila-total">
                <span>Total</span>
                <span id="totalVenta" class="precio-total">$0.00</span>
            </div>

            <fieldset class="metodos-pago-pos">
                <legend>Método de pago</legend>
                <label class="opcion-pago-pos">
                    <input type="radio" name="metodo_pago" value="efectivo" @checked(old('metodo_pago', 'efectivo') === 'efectivo')>
                    <span>Efectivo</span>
                </label>
                <label class="opcion-pago-pos">
                    <input type="radio" name="metodo_pago" value="tarjeta" @checked(old('metodo_pago') === 'tarjeta')>
                    <span>Tarjeta</span>
                </label>
            </fieldset>

            <div id="grupoEfectivo" class="grupo-campo">
                <label for="montoRecibido">Efectivo recibido</label>
                <input
                    type="number"
                    id="montoRecibido"
                    name="monto_recibido"
                    class="campo-numero-pos"
                    value="{{ old('monto_recibido') }}"
                    min="0"
                    step="0.01"
                    inputmode="decimal"
                >
                <p class="fila-resumen cambio-pos">
                    <span>Cambio</span>
                    <span id="cambioVenta">$0.00</span>
                </p>
            </div>

            <button type="submit" id="botonCobrar" class="boton-finalizar-compra" disabled>Cobrar</button>
            <button type="button" id="botonCancelar" class="boton-secundario-pos boton-ancho-pos">Cancelar venta</button>
        </form>
    </aside>
</section>
@endsection

@push('scripts')
<script src="{{ asset('js/punto-venta.js') }}" defer></script>
@endpush
