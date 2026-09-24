@props(['producto'])

<article class="contenedor-tarjeta-producto" tabindex="0" aria-label="Tarjeta de producto: {{ $producto['nombre'] }}">
    <div class="envoltorio-tarjeta-3d">
        <!-- Cara Frontal -->
        <div class="cara-tarjeta cara-frontal">
            <figure class="contenedor-imagen-tarjeta">
                <img src="{{ $producto['imagen'] }}" alt="{{ $producto['nombre'] }}" class="imagen-tarjeta" loading="lazy">
                @if(isset($producto['etiqueta']))
                    <span class="etiqueta-pulso">{{ $producto['etiqueta'] }}</span>
                @endif
            </figure>
            <div class="cuerpo-tarjeta">
                <h3>{{ $producto['nombre'] }}</h3>
                <p class="precio">${{ number_format($producto['precio'], 2) }} MXN</p>
                <p class="indicacion">Pasa el cursor o usa Tab para girar</p>
            </div>
        </div>

        <!-- Cara Trasera (Giro 3D) -->
        <div class="cara-tarjeta cara-trasera">
            <h3>{{ $producto['nombre'] }}</h3>
            <p class="descripcion">{{ $producto['descripcion'] }}</p>
            <div class="accion-pie-tarjeta">
                <button type="button" class="boton-agregar-carrito">Añadir al Carrito</button>
            </div>
        </div>
    </div>
</article>