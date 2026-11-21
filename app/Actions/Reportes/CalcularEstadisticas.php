<?php

namespace App\Actions\Reportes;

use App\Enums\EstadoEnvio;
use App\Enums\OrigenVenta;
use App\Models\DetalleVenta;
use App\Models\Producto;
use App\Models\Venta;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Calcula los indicadores y series del panel de estadísticas.
 *
 * Los pedidos en línea cancelados no cuentan como venta. El agrupado por día,
 * semana y mes se hace en PHP para funcionar igual en SQLite y MySQL.
 */
class CalcularEstadisticas
{
    /**
     * @return array<string, mixed>
     */
    public function handle(CarbonImmutable $desde, CarbonImmutable $hasta, ?OrigenVenta $origen = null): array
    {
        $desde = $desde->startOfDay();
        $hasta = $hasta->endOfDay();

        $ventas = $this->ventas($desde, $hasta, $origen)->get();

        $diasPeriodo = (int) $desde->diffInDays($hasta) + 1;
        $anteriorHasta = $desde->subDay()->endOfDay();
        $anteriorDesde = $anteriorHasta->subDays($diasPeriodo - 1)->startOfDay();
        $ventasAnteriores = $this->ventas($anteriorDesde, $anteriorHasta, $origen)->get();

        $detalles = $this->detalles($desde, $hasta, $origen);

        return [
            'indicadores' => $this->indicadores($ventas, $ventasAnteriores, $detalles),
            'porDia' => $this->porDia($ventas, $desde, $hasta),
            'porSemana' => $this->porSemana($hasta, $origen),
            'porMes' => $this->porMes($hasta, $origen),
            'masVendidos' => $this->rankingProductos($detalles, mas: true),
            'menosVendidos' => $this->rankingProductos($detalles, mas: false),
            'porCategoria' => $this->porCategoria($detalles),
            'porOrigen' => $this->reparto($ventas, fn (Venta $venta) => $venta->origen === OrigenVenta::Tienda ? 'Tienda física' : 'Tienda en línea'),
            'porMetodoPago' => $this->reparto($ventas, fn (Venta $venta) => ucfirst($venta->metodo_pago->value)),
            'porEmpleado' => $this->porEmpleado($ventas),
            'periodoAnterior' => [$anteriorDesde, $anteriorHasta],
        ];
    }

    /**
     * Ventas válidas dentro de un rango.
     *
     * @return Builder<Venta>
     */
    public function ventas(CarbonImmutable $desde, CarbonImmutable $hasta, ?OrigenVenta $origen = null): Builder
    {
        return Venta::query()
            ->whereBetween('created_at', [$desde, $hasta])
            ->where(fn (Builder $consulta) => $consulta
                ->whereNull('estado_envio')
                ->orWhere('estado_envio', '!=', EstadoEnvio::Cancelado->value))
            ->when($origen, fn (Builder $consulta) => $consulta->where('origen', $origen));
    }

    /**
     * Renglones vendidos con su producto y categoría.
     *
     * @return Collection<int, DetalleVenta>
     */
    private function detalles(CarbonImmutable $desde, CarbonImmutable $hasta, ?OrigenVenta $origen): Collection
    {
        return DetalleVenta::query()
            ->whereIn('venta_id', $this->ventas($desde, $hasta, $origen)->select('id'))
            ->with('inventario.producto.categoria')
            ->get();
    }

    /**
     * @param  Collection<int, Venta>  $ventas
     * @param  Collection<int, Venta>  $anteriores
     * @param  Collection<int, DetalleVenta>  $detalles
     * @return array<string, array{valor: float, anterior: float|null, formato: string}>
     */
    private function indicadores(Collection $ventas, Collection $anteriores, Collection $detalles): array
    {
        $ingresos = (float) $ventas->sum('total');
        $ingresosAnteriores = (float) $anteriores->sum('total');

        return [
            'ingresos' => ['valor' => $ingresos, 'anterior' => $ingresosAnteriores, 'formato' => 'moneda'],
            'ventas' => ['valor' => $ventas->count(), 'anterior' => $anteriores->count(), 'formato' => 'entero'],
            'ticketPromedio' => [
                'valor' => $ventas->isEmpty() ? 0 : round($ingresos / $ventas->count(), 2),
                'anterior' => $anteriores->isEmpty() ? 0 : round($ingresosAnteriores / $anteriores->count(), 2),
                'formato' => 'moneda',
            ],
            'piezas' => ['valor' => (int) $detalles->sum('cantidad'), 'anterior' => null, 'formato' => 'entero'],
        ];
    }

    /**
     * Ingresos por día del periodo (días sin ventas en 0).
     *
     * @param  Collection<int, Venta>  $ventas
     * @return list<array{etiqueta: string, valor: float, detalle: string}>
     */
    private function porDia(Collection $ventas, CarbonImmutable $desde, CarbonImmutable $hasta): array
    {
        $agrupadas = $ventas->groupBy(fn (Venta $venta) => $venta->created_at->toDateString());

        return collect(CarbonPeriod::create($desde, $hasta->startOfDay()))
            ->map(function ($dia) use ($agrupadas) {
                $delDia = $agrupadas->get($dia->toDateString(), collect());

                return [
                    'etiqueta' => $dia->format('d/m'),
                    'valor' => round((float) $delDia->sum('total'), 2),
                    'detalle' => $dia->locale('es')->isoFormat('ddd D [de] MMMM').' · '.$delDia->count().' '.($delDia->count() === 1 ? 'venta' : 'ventas'),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Ingresos de las últimas 12 semanas (lunes a domingo) que terminan en $hasta.
     *
     * @return list<array{etiqueta: string, valor: float, detalle: string}>
     */
    private function porSemana(CarbonImmutable $hasta, ?OrigenVenta $origen): array
    {
        $ultimaSemana = $hasta->startOfWeek(CarbonInterface::MONDAY);
        $inicio = $ultimaSemana->subWeeks(11);
        $ventas = $this->ventas($inicio, $hasta->endOfWeek(CarbonInterface::SUNDAY), $origen)->get(['id', 'total', 'created_at'])
            ->groupBy(fn (Venta $venta) => $venta->created_at->startOfWeek(CarbonInterface::MONDAY)->toDateString());

        return collect(range(0, 11))
            ->map(function (int $indice) use ($inicio, $ventas) {
                $lunes = $inicio->addWeeks($indice);
                $deLaSemana = $ventas->get($lunes->toDateString(), collect());

                return [
                    'etiqueta' => $lunes->format('d/m'),
                    'valor' => round((float) $deLaSemana->sum('total'), 2),
                    'detalle' => 'Semana del '.$lunes->format('d/m').' al '.$lunes->endOfWeek(CarbonInterface::SUNDAY)->format('d/m').' · '.$deLaSemana->count().' ventas',
                ];
            })
            ->all();
    }

    /**
     * Ingresos de los últimos 12 meses que terminan en el mes de $hasta.
     *
     * @return list<array{etiqueta: string, valor: float, detalle: string}>
     */
    private function porMes(CarbonImmutable $hasta, ?OrigenVenta $origen): array
    {
        $inicio = $hasta->startOfMonth()->subMonths(11);
        $ventas = $this->ventas($inicio, $hasta->endOfMonth(), $origen)->get(['id', 'total', 'created_at'])
            ->groupBy(fn (Venta $venta) => $venta->created_at->format('Y-m'));

        return collect(range(0, 11))
            ->map(function (int $indice) use ($inicio, $ventas) {
                $mes = $inicio->addMonths($indice);
                $delMes = $ventas->get($mes->format('Y-m'), collect());

                return [
                    'etiqueta' => ucfirst($mes->locale('es')->isoFormat('MMM')),
                    'valor' => round((float) $delMes->sum('total'), 2),
                    'detalle' => ucfirst($mes->locale('es')->isoFormat('MMMM YYYY')).' · '.$delMes->count().' ventas',
                ];
            })
            ->all();
    }

    /**
     * Productos ordenados por piezas vendidas. Los menos vendidos incluyen
     * productos visibles que no vendieron nada en el periodo.
     *
     * @param  Collection<int, DetalleVenta>  $detalles
     * @return list<array{etiqueta: string, valor: int, detalle: string}>
     */
    private function rankingProductos(Collection $detalles, bool $mas): array
    {
        $vendidos = $detalles->groupBy(fn (DetalleVenta $detalle) => $detalle->inventario->producto_id)
            ->map(fn (Collection $renglones) => [
                'piezas' => (int) $renglones->sum('cantidad'),
                'importe' => (float) $renglones->sum('subtotal'),
            ]);

        $productos = Producto::query()
            ->when($mas, fn (Builder $consulta) => $consulta->whereIn('id', $vendidos->keys()))
            ->when(! $mas, fn (Builder $consulta) => $consulta->where('activo', true))
            ->get(['id', 'titulo']);

        return $productos
            ->map(fn (Producto $producto) => [
                'etiqueta' => $producto->titulo,
                'valor' => $vendidos[$producto->id]['piezas'] ?? 0,
                'detalle' => ($vendidos[$producto->id]['piezas'] ?? 0).' piezas · $'.number_format($vendidos[$producto->id]['importe'] ?? 0, 2),
            ])
            ->sortBy([['valor', $mas ? 'desc' : 'asc'], ['etiqueta', 'asc']])
            ->take(10)
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, DetalleVenta>  $detalles
     * @return list<array{etiqueta: string, valor: float, detalle: string}>
     */
    private function porCategoria(Collection $detalles): array
    {
        return $detalles->groupBy(fn (DetalleVenta $detalle) => $detalle->inventario->producto->categoria->nombre)
            ->map(fn (Collection $renglones, string $categoria) => [
                'etiqueta' => $categoria,
                'valor' => round((float) $renglones->sum('subtotal'), 2),
                'detalle' => $renglones->sum('cantidad').' piezas',
            ])
            ->sortByDesc('valor')
            ->values()
            ->all();
    }

    /**
     * Reparto de ingresos entre grupos (para barras de proporción).
     *
     * @param  Collection<int, Venta>  $ventas
     * @param  callable(Venta): string  $grupo
     * @return list<array{etiqueta: string, valor: float, detalle: string}>
     */
    private function reparto(Collection $ventas, callable $grupo): array
    {
        return $ventas->groupBy($grupo)
            ->map(fn (Collection $delGrupo, string $etiqueta) => [
                'etiqueta' => $etiqueta,
                'valor' => round((float) $delGrupo->sum('total'), 2),
                'detalle' => $delGrupo->count().' ventas',
            ])
            ->sortByDesc('valor')
            ->values()
            ->all();
    }

    /**
     * Ingresos de mostrador por empleado.
     *
     * @param  Collection<int, Venta>  $ventas
     * @return list<array{etiqueta: string, valor: float, detalle: string}>
     */
    private function porEmpleado(Collection $ventas): array
    {
        $ventas->loadMissing('empleado');

        return $ventas->whereNotNull('empleado_id')
            ->groupBy('empleado_id')
            ->map(fn (Collection $delEmpleado) => [
                'etiqueta' => $delEmpleado->first()->empleado->nombre.' '.$delEmpleado->first()->empleado->apellido_paterno,
                'valor' => round((float) $delEmpleado->sum('total'), 2),
                'detalle' => $delEmpleado->count().' ventas',
            ])
            ->sortByDesc('valor')
            ->take(10)
            ->values()
            ->all();
    }
}
