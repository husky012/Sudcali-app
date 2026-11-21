{{--
    Gráfica de una serie en el tiempo, en SVG generado en el servidor.

    @param list<array{etiqueta: string, valor: float|int, detalle: string}> $datos
    @param string $tipo 'columnas' | 'linea'
    @param string $titulo Nombre accesible de la gráfica
--}}
@php
    $ancho = 640;
    $alto = 240;
    $margen = ['arriba' => 22, 'derecha' => 12, 'abajo' => 28, 'izquierda' => 52];
    $areaAncho = $ancho - $margen['izquierda'] - $margen['derecha'];
    $areaAlto = $alto - $margen['arriba'] - $margen['abajo'];
    $total = count($datos);
    $maximo = max(array_column($datos, 'valor') ?: [0]);

    // Escala "bonita": 4 divisiones con pasos redondos (1, 2, 2.5, 5 × 10^n).
    $paso = 1;
    if ($maximo > 0) {
        $bruto = $maximo / 4;
        $magnitud = 10 ** floor(log10($bruto));
        foreach ([1, 2, 2.5, 5, 10] as $factor) {
            if ($factor * $magnitud >= $bruto) {
                $paso = $factor * $magnitud;
                break;
            }
        }
    }
    $tope = $paso * 4;

    $formatoEje = fn (float $valor) => $valor >= 10000
        ? '$'.rtrim(rtrim(number_format($valor / 1000, 1), '0'), '.').'K'
        : '$'.number_format($valor);
    $formatoValor = fn (float $valor) => '$'.number_format($valor, 2);

    $banda = $total > 0 ? $areaAncho / $total : $areaAncho;
    $grosor = min(24, $banda * 0.62);
    $x = fn (int $indice) => $margen['izquierda'] + $banda * $indice + $banda / 2;
    $y = fn (float $valor) => $margen['arriba'] + $areaAlto - ($tope > 0 ? $valor / $tope * $areaAlto : 0);
    $base = $margen['arriba'] + $areaAlto;

    // Mostrar como máximo ~12 etiquetas en el eje X para que no se encimen.
    $cadaEtiqueta = max(1, (int) ceil($total / 12));
    $indiceMaximo = $maximo > 0 ? array_search($maximo, array_column($datos, 'valor')) : null;
@endphp

<div class="grafica-svg">
    <svg viewBox="0 0 {{ $ancho }} {{ $alto }}" role="img" aria-label="{{ $titulo }}" preserveAspectRatio="xMidYMid meet">
        {{-- Rejilla y eje Y --}}
        @for ($i = 0; $i <= 4; $i++)
            @php
                $valorLinea = $paso * $i;
            @endphp
            <line x1="{{ $margen['izquierda'] }}" x2="{{ $ancho - $margen['derecha'] }}"
                  y1="{{ $y($valorLinea) }}" y2="{{ $y($valorLinea) }}"
                  class="{{ $i === 0 ? 'eje-base' : 'linea-rejilla' }}"/>
            <text x="{{ $margen['izquierda'] - 8 }}" y="{{ $y($valorLinea) + 4 }}" class="texto-eje" text-anchor="end">{{ $formatoEje($valorLinea) }}</text>
        @endfor

        {{-- Eje X --}}
        @foreach ($datos as $indice => $dato)
            @if ($indice % $cadaEtiqueta === 0)
                <text x="{{ $x($indice) }}" y="{{ $alto - 8 }}" class="texto-eje" text-anchor="middle">{{ $dato['etiqueta'] }}</text>
            @endif
        @endforeach

        @if ($tipo === 'linea')
            @php
                $puntos = collect($datos)->map(fn ($dato, $indice) => round($x($indice), 1).','.round($y($dato['valor']), 1))->implode(' ');
                $primeraX = round($x(0), 1);
                $ultimaX = round($x(max($total - 1, 0)), 1);
            @endphp
            <polygon points="{{ $primeraX }},{{ $base }} {{ $puntos }} {{ $ultimaX }},{{ $base }}" class="area-serie"/>
            <polyline points="{{ $puntos }}" class="linea-serie"/>

            @if ($indiceMaximo !== null)
                <circle cx="{{ $x($indiceMaximo) }}" cy="{{ $y($maximo) }}" r="5" class="punto-serie"/>
                <text x="{{ $x($indiceMaximo) }}" y="{{ $y($maximo) - 10 }}" class="texto-valor" text-anchor="middle">{{ $formatoEje($maximo) }}</text>
            @endif

            {{-- Zonas invisibles para el tooltip, una por día --}}
            @foreach ($datos as $indice => $dato)
                <rect x="{{ $x($indice) - $banda / 2 }}" y="{{ $margen['arriba'] }}" width="{{ $banda }}" height="{{ $areaAlto }}"
                      class="zona-tooltip"
                      data-tooltip-titulo="{{ $dato['detalle'] }}"
                      data-tooltip-valor="{{ $formatoValor($dato['valor']) }}">
                    <title>{{ $dato['detalle'] }}: {{ $formatoValor($dato['valor']) }}</title>
                </rect>
            @endforeach
        @else
            @foreach ($datos as $indice => $dato)
                @php
                    $altura = $base - $y($dato['valor']);
                    $izquierda = $x($indice) - $grosor / 2;
                    $radio = min(4, $altura, $grosor / 2);
                @endphp
                <g class="marca-columna" tabindex="0"
                   data-tooltip-titulo="{{ $dato['detalle'] }}"
                   data-tooltip-valor="{{ $formatoValor($dato['valor']) }}">
                    <title>{{ $dato['detalle'] }}: {{ $formatoValor($dato['valor']) }}</title>
                    {{-- Zona de toque más grande que la columna --}}
                    <rect x="{{ $x($indice) - $banda / 2 }}" y="{{ $margen['arriba'] }}" width="{{ $banda }}" height="{{ $areaAlto }}" class="zona-tooltip"/>
                    @if ($altura > 0)
                        {{-- Esquinas superiores redondeadas, base recta --}}
                        <path class="columna-serie" d="M{{ $izquierda }},{{ $base }}
                            V{{ $base - $altura + $radio }}
                            Q{{ $izquierda }},{{ $base - $altura }} {{ $izquierda + $radio }},{{ $base - $altura }}
                            H{{ $izquierda + $grosor - $radio }}
                            Q{{ $izquierda + $grosor }},{{ $base - $altura }} {{ $izquierda + $grosor }},{{ $base - $altura + $radio }}
                            V{{ $base }} Z"/>
                    @endif
                </g>
            @endforeach

            @if ($indiceMaximo !== null)
                <text x="{{ $x($indiceMaximo) }}" y="{{ $y($maximo) - 6 }}" class="texto-valor" text-anchor="middle">{{ $formatoEje($maximo) }}</text>
            @endif
        @endif
    </svg>

    @if ($maximo == 0)
        <p class="grafica-sin-datos">Sin ventas en este periodo.</p>
    @endif
</div>

<details class="tabla-grafica">
    <summary>Ver tabla</summary>
    <div class="tabla-desplazable-pos">
        <table class="tabla-pos">
            <thead><tr><th scope="col">Periodo</th><th scope="col" class="numero">Ingresos</th></tr></thead>
            <tbody>
                @foreach ($datos as $dato)
                    <tr><td>{{ $dato['detalle'] }}</td><td class="numero">{{ $formatoValor($dato['valor']) }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
</details>
