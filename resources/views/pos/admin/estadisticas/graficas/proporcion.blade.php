{{--
    Barra de proporción (100 %) con leyenda; cada grupo usa un color categórico fijo.

    @param list<array{etiqueta: string, valor: float, detalle: string}> $datos
    @param array<string, int> $colores Etiqueta → número de serie (1..3), para que el color siga a la entidad
--}}
@php($total = array_sum(array_column($datos, 'valor')))

@if ($total <= 0)
    <p class="grafica-sin-datos">Sin ventas en este periodo.</p>
@else
    <div class="barra-proporcion" role="img" aria-label="{{ collect($datos)->map(fn ($d) => $d['etiqueta'].' '.round($d['valor'] / $total * 100).'%')->implode(', ') }}">
        @foreach ($datos as $dato)
            <span class="segmento-proporcion serie-{{ $colores[$dato['etiqueta']] ?? ($loop->index + 1) }}"
                  style="flex-grow: {{ $dato['valor'] }}"
                  tabindex="0"
                  data-tooltip-titulo="{{ $dato['etiqueta'] }} · {{ round($dato['valor'] / $total * 100, 1) }}%"
                  data-tooltip-valor="${{ number_format($dato['valor'], 2) }} · {{ $dato['detalle'] }}"></span>
        @endforeach
    </div>

    <ul class="leyenda-grafica">
        @foreach ($datos as $dato)
            <li>
                <span class="muestra-leyenda serie-{{ $colores[$dato['etiqueta']] ?? ($loop->index + 1) }}" aria-hidden="true"></span>
                <span>{{ $dato['etiqueta'] }}</span>
                <strong>{{ round($dato['valor'] / $total * 100) }}%</strong>
                <small>${{ number_format($dato['valor'], 2) }} · {{ $dato['detalle'] }}</small>
            </li>
        @endforeach
    </ul>
@endif
