@extends('layouts.pos')

@section('titulo', 'Estadísticas')

@php
    $indicadores = $estadisticas['indicadores'];
    $nombresIndicadores = [
        'ingresos' => 'Ingresos',
        'ventas' => 'Ventas',
        'ticketPromedio' => 'Ticket promedio',
        'piezas' => 'Piezas vendidas',
    ];
    $formatear = fn (array $indicador) => $indicador['formato'] === 'moneda'
        ? '$'.number_format($indicador['valor'], 2)
        : number_format($indicador['valor']);

    $filtrosActuales = array_filter([
        'periodo' => $periodo,
        'desde' => $periodo === 'personalizado' ? $desde->format('Y-m-d') : null,
        'hasta' => $periodo === 'personalizado' ? $hasta->format('Y-m-d') : null,
        'origen' => $origen?->value,
    ]);

    [$anteriorDesde, $anteriorHasta] = $estadisticas['periodoAnterior'];
@endphp

@section('contenido')
<section class="seccion-admin-pos panel-estadisticas">
    <header class="encabezado-admin-pos">
        <div class="encabezado-pagina-pos">
            <h2>Estadísticas</h2>
            <p>{{ $desde->format('d/m/Y') }} – {{ $hasta->format('d/m/Y') }}{{ $origen ? ' · '.($origen->value === 'tienda' ? 'Solo tienda física' : 'Solo en línea') : '' }}</p>
        </div>

        <details class="menu-exportar">
            <summary class="boton-login boton-compacto-pos">Descargar Excel</summary>
            <ul>
                <li>
                    <a href="{{ route('pos.admin.estadisticas.exportar', ['reporte' => 'ventas', ...$filtrosActuales]) }}">
                        <strong>Ventas del periodo</strong>
                        <small>Ventas, artículos vendidos y resumen por producto</small>
                    </a>
                </li>
                <li>
                    <a href="{{ route('pos.admin.estadisticas.exportar', 'catalogo') }}">
                        <strong>Catálogo e inventario</strong>
                        <small>Productos, tallas con existencias y categorías</small>
                    </a>
                </li>
                <li>
                    <a href="{{ route('pos.admin.estadisticas.exportar', 'empleados') }}">
                        <strong>Empleados</strong>
                        <small>Datos de contacto, puesto y sucursal</small>
                    </a>
                </li>
            </ul>
        </details>
    </header>

    {{-- Filtros en una sola fila --}}
    <form method="GET" class="filtros-admin-pos filtros-estadisticas" id="filtrosEstadisticas">
        <label class="solo-lector" for="periodo">Periodo</label>
        <select id="periodo" name="periodo">
            @foreach ($periodos as $clave => $nombre)
                <option value="{{ $clave }}" @selected($periodo === $clave)>{{ $nombre }}</option>
            @endforeach
        </select>

        <div id="rangoPersonalizado" class="rango-personalizado" @if ($periodo !== 'personalizado') hidden @endif>
            <label for="desde" class="solo-lector">Desde</label>
            <input type="date" id="desde" name="desde" value="{{ $desde->format('Y-m-d') }}" max="{{ today()->format('Y-m-d') }}">
            <span aria-hidden="true">–</span>
            <label for="hasta" class="solo-lector">Hasta</label>
            <input type="date" id="hasta" name="hasta" value="{{ $hasta->format('Y-m-d') }}" max="{{ today()->format('Y-m-d') }}">
        </div>

        <label class="solo-lector" for="origen">Canal</label>
        <select id="origen" name="origen">
            <option value="">Tienda física y en línea</option>
            <option value="tienda" @selected($origen?->value === 'tienda')>Solo tienda física</option>
            <option value="en_linea" @selected($origen?->value === 'en_linea')>Solo en línea</option>
        </select>

        <button type="submit" class="boton-secundario-pos">Aplicar</button>
    </form>

    @if ($errors->any())
        <p class="alerta-pos alerta-error" role="alert">{{ $errors->first() }}</p>
    @endif

    {{-- Indicadores --}}
    <div class="tarjetas-resumen-pos">
        @foreach ($nombresIndicadores as $clave => $nombre)
            @php
                $indicador = $indicadores[$clave];
                $cambio = $indicador['anterior'] ? ($indicador['valor'] - $indicador['anterior']) / $indicador['anterior'] * 100 : null;
            @endphp
            <div class="tarjeta-resumen-pos tarjeta-indicador">
                <span>{{ $nombre }}</span>
                <strong>{{ $formatear($indicador) }}</strong>
                @if ($cambio !== null)
                    <small @class(['cambio-indicador', 'sube' => $cambio > 0, 'baja' => $cambio < 0])
                           title="Comparado con {{ $anteriorDesde->format('d/m') }} – {{ $anteriorHasta->format('d/m') }}">
                        {{ $cambio > 0 ? '▲' : ($cambio < 0 ? '▼' : '=') }} {{ number_format(abs($cambio), 1) }}% vs. periodo anterior
                    </small>
                @elseif ($indicador['anterior'] === 0 || $indicador['anterior'] === 0.0)
                    <small class="cambio-indicador">Sin ventas en el periodo anterior</small>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Series de tiempo --}}
    <article class="tarjeta-grafica tarjeta-grafica-ancha">
        <header>
            <h3>Ventas por día</h3>
            <p>Ingresos diarios del periodo; se marca el mejor día.</p>
        </header>
        @include('pos.admin.estadisticas.graficas.serie', ['datos' => $estadisticas['porDia'], 'tipo' => count($estadisticas['porDia']) > 31 ? 'linea' : 'columnas', 'titulo' => 'Ingresos por día'])
    </article>

    <div class="rejilla-graficas">
        <article class="tarjeta-grafica">
            <header>
                <h3>Ventas por semana</h3>
                <p>Últimas 12 semanas (lunes a domingo).</p>
            </header>
            @include('pos.admin.estadisticas.graficas.serie', ['datos' => $estadisticas['porSemana'], 'tipo' => 'columnas', 'titulo' => 'Ingresos por semana'])
        </article>

        <article class="tarjeta-grafica">
            <header>
                <h3>Ventas por mes</h3>
                <p>Últimos 12 meses.</p>
            </header>
            @include('pos.admin.estadisticas.graficas.serie', ['datos' => $estadisticas['porMes'], 'tipo' => 'linea', 'titulo' => 'Ingresos por mes'])
        </article>
    </div>

    {{-- Productos --}}
    <div class="rejilla-graficas">
        <article class="tarjeta-grafica">
            <header>
                <h3>Lo más vendido</h3>
                <p>Top 10 por piezas vendidas en el periodo.</p>
            </header>
            @include('pos.admin.estadisticas.graficas.barras', ['datos' => $estadisticas['masVendidos'], 'formato' => 'piezas', 'vacio' => 'Aún no hay productos vendidos en este periodo.'])
        </article>

        <article class="tarjeta-grafica">
            <header>
                <h3>Lo menos vendido</h3>
                <p>Productos visibles con menos piezas vendidas (incluye los que no se vendieron).</p>
            </header>
            @include('pos.admin.estadisticas.graficas.barras', ['datos' => $estadisticas['menosVendidos'], 'formato' => 'piezas', 'vacio' => 'No hay productos visibles.'])
        </article>
    </div>

    <div class="rejilla-graficas">
        <article class="tarjeta-grafica">
            <header>
                <h3>Ingresos por categoría</h3>
                <p>Suma de los artículos vendidos.</p>
            </header>
            @include('pos.admin.estadisticas.graficas.barras', ['datos' => $estadisticas['porCategoria'], 'formato' => 'moneda', 'vacio' => 'Sin ventas en este periodo.'])
        </article>

        <article class="tarjeta-grafica">
            <header>
                <h3>Mejores vendedores</h3>
                <p>Ingresos de mostrador por empleado.</p>
            </header>
            @include('pos.admin.estadisticas.graficas.barras', ['datos' => $estadisticas['porEmpleado'], 'formato' => 'moneda', 'vacio' => 'Sin ventas de mostrador en este periodo.'])
        </article>
    </div>

    <div class="rejilla-graficas">
        <article class="tarjeta-grafica">
            <header>
                <h3>Canal de venta</h3>
                <p>Reparto de ingresos entre tienda física y en línea.</p>
            </header>
            @include('pos.admin.estadisticas.graficas.proporcion', ['datos' => $estadisticas['porOrigen'], 'colores' => ['Tienda física' => 1, 'Tienda en línea' => 2]])
        </article>

        <article class="tarjeta-grafica">
            <header>
                <h3>Forma de pago</h3>
                <p>Reparto de ingresos por método de pago.</p>
            </header>
            @include('pos.admin.estadisticas.graficas.proporcion', ['datos' => $estadisticas['porMetodoPago'], 'colores' => ['Efectivo' => 1, 'Tarjeta' => 2, 'Transferencia' => 3]])
        </article>
    </div>
</section>

<div id="tooltipGrafica" class="tooltip-grafica" role="status" hidden>
    <strong></strong>
    <span></span>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/estadisticas.js') }}" defer></script>
@endpush
