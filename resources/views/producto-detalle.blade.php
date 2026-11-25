@extends('layouts.app')

@section('titulo', 'Sudcali - '.$producto->titulo)

@section('contenido')
<section class="seccion-detalle-producto">
    <!-- Migas de pan / Navegación rápida -->
    <nav class="migas-pan" aria-label="Ruta de navegación">
        <a href="{{ route('home') }}">Inicio</a> /
        <a href="{{ route('catalogo', ['categoria' => $producto->categoria->slug]) }}">{{ $producto->categoria->nombre }}</a> /
        <span>{{ $producto->titulo }}</span>
    </nav>

    @php($tallasDisponibles = $producto->inventario->where('stock', '>', 0))

    <div class="contenedor-grid-detalle">
        <!-- Imagen -->
        <div class="galeria-producto">
            <figure class="imagen-principal-detalle">
                @if ($producto->imagen)
                    <img src="{{ $producto->imagen }}" alt="{{ $producto->titulo }}">
                @endif
                @if ($tallasDisponibles->isEmpty())
                    <span class="etiqueta-pulso etiqueta-agotado">Agotado</span>
                @endif
            </figure>
        </div>

        <!-- Información y Controles de Compra -->
        <div class="info-producto-detalle">
            <span class="categoria-badge">{{ $producto->categoria->nombre }}</span>
            <h2>{{ $producto->titulo }}</h2>
            <p class="precio-detalle">${{ number_format($producto->precio, 2) }} MXN</p>

            @if ($producto->descripcion)
                <p class="descripcion-corta">{{ $producto->descripcion }}</p>
            @endif

            @if ($errors->any())
                <div class="alerta-pos alerta-error" role="alert">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            @if ($tallasDisponibles->isEmpty())
                <p class="alerta-pos alerta-aviso">Por ahora este producto está agotado en todas sus tallas.</p>
            @else
                <form action="{{ route('carrito.agregar') }}" method="POST" class="form-seleccion-producto">
                    @csrf

                    <fieldset class="grupo-seleccion">
                        <legend>Selecciona tu talla:</legend>
                        <div class="opciones-tallas">
                            @foreach ($producto->inventario as $variante)
                                <label class="radio-talla" @if ($variante->stock === 0) title="Agotada" @endif>
                                    <input type="radio" name="inventario_id" value="{{ $variante->id }}"
                                           data-stock="{{ $variante->stock }}"
                                           @disabled($variante->stock === 0)
                                           @checked((int) old('inventario_id', $tallasDisponibles->first()->id) === $variante->id)>
                                    <span>
                                        {{ $variante->talla }}
                                        @if ($variante->color !== 'Único')
                                            · {{ $variante->color }}
                                        @endif
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <p id="avisoExistencias" class="aviso-existencias" aria-live="polite"></p>
                    </fieldset>

                    <div class="grupo-seleccion">
                        <label for="cantidad">Cantidad:</label>
                        <div class="selector-cantidad-detalle">
                            <input type="number" id="cantidad" name="cantidad" value="{{ old('cantidad', 1) }}" min="1" max="{{ min(20, $tallasDisponibles->first()->stock) }}" required>
                        </div>
                    </div>

                    <div class="acciones-compra-detalle">
                        <button type="submit" class="boton-agregar-grande">Añadir al Carrito</button>
                        <button type="submit" name="comprar_ahora" value="1" class="boton-comprar-ahora">Comprar Ahora</button>
                    </div>
                </form>
            @endif

            <ul class="lista-ventajas-locales">
                <li>🚚 <strong>Entrega Local:</strong> envío a todo B.C.S.; gratis en compras desde $1,000.</li>
                <li>🔄 <strong>Cambios:</strong> Sin costo en puntos de entrega oficiales.</li>
            </ul>
        </div>
    </div>

    @if ($relacionados->isNotEmpty())
        <section class="seccion-relacionados">
            <header class="encabezado-seccion">
                <h3>También te podría gustar</h3>
            </header>
            <div class="cuadricula-productos">
                @foreach ($relacionados as $relacionado)
                    <x-tarjeta-producto :producto="$relacionado" />
                @endforeach
            </div>
        </section>
    @endif
</section>
@endsection

@push('scripts')
<script src="{{ asset('js/producto-detalle.js') }}" defer></script>
@endpush
