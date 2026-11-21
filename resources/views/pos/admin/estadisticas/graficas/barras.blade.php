{{--
    Barras horizontales para rankings (una sola serie, sin leyenda).

    @param list<array{etiqueta: string, valor: float|int, detalle: string}> $datos
    @param string $formato 'moneda' | 'piezas'
    @param string $vacio Mensaje cuando no hay datos
--}}
@php
    $maximo = max(array_column($datos, 'valor') ?: [0]);
    $formatear = fn ($valor) => $formato === 'moneda' ? '$'.number_format($valor, 2) : number_format($valor).' pzas';
@endphp

@if ($datos === [])
    <p class="grafica-sin-datos">{{ $vacio }}</p>
@else
    <ol class="grafica-barras">
        @foreach ($datos as $dato)
            <li class="fila-barra" tabindex="0"
                data-tooltip-titulo="{{ $dato['etiqueta'] }}"
                data-tooltip-valor="{{ $dato['detalle'] }}">
                <span class="etiqueta-barra" title="{{ $dato['etiqueta'] }}">{{ $dato['etiqueta'] }}</span>
                <span class="pista-barra" aria-hidden="true">
                    <span class="barra-serie" style="width: {{ $maximo > 0 ? max($dato['valor'] / $maximo * 100, $dato['valor'] > 0 ? 1.5 : 0) : 0 }}%"></span>
                </span>
                <span class="valor-barra">{{ $formatear($dato['valor']) }}</span>
            </li>
        @endforeach
    </ol>
@endif
