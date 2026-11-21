@props(['producto', 'etiqueta' => null])

@php($agotado = (int) ($producto->inventario_sum_stock ?? 1) === 0)

{{-- La tarjeta crece al pasar el cursor, al enfocarla con teclado o al tocarla, y despliega la información. --}}
<div class="contenedor-tarjeta-producto">
    <article class="tarjeta-producto" tabindex="0" aria-label="{{ $producto->titulo }}, ${{ number_format($producto->precio, 2) }} MXN">
        <figure class="contenedor-imagen-tarjeta">
            @if ($producto->imagen)
                <img
                    src="{{ $producto->imagen }}"
                    alt="{{ $producto->titulo }}"
                    class="imagen-tarjeta"
                    loading="lazy"
                >
            @endif
            @if ($agotado)
                <span class="etiqueta-pulso etiqueta-agotado">Agotado</span>
            @elseif ($etiqueta)
                <span class="etiqueta-pulso">{{ $etiqueta }}</span>
            @endif
        </figure>

        <div class="cuerpo-tarjeta">
            <h3>{{ $producto->titulo }}</h3>
            <p class="precio">${{ number_format($producto->precio, 2) }} MXN</p>
            <p class="indicacion" aria-hidden="true">Toca o pasa el cursor para ver más</p>

            <div class="info-extra-tarjeta">
                <div class="contenido-extra-tarjeta">
                    @if ($producto->categoria)
                        <span class="categoria-tarjeta">{{ $producto->categoria->nombre }}</span>
                    @endif
                    @if ($producto->descripcion)
                        <p class="descripcion">{{ $producto->descripcion }}</p>
                    @endif
                    <a href="{{ route('producto.detalle', $producto) }}" class="boton-agregar-carrito">
                        {{ $agotado ? 'Ver detalle' : 'Elegir talla y comprar' }}
                    </a>
                </div>
            </div>
        </div>
    </article>
</div>
