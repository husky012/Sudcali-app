<?php

namespace App\Http\Controllers\PuntoVenta\Admin;

use App\Actions\Reportes\CalcularEstadisticas;
use App\Actions\Reportes\GenerarReporteExcel;
use App\Enums\OrigenVenta;
use App\Http\Controllers\Controller;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EstadisticaController extends Controller
{
    /**
     * Periodos rápidos del filtro.
     *
     * @var array<string, string>
     */
    public const PERIODOS = [
        'este_mes' => 'Este mes',
        'mes_pasado' => 'Mes pasado',
        'ultimos_7' => 'Últimos 7 días',
        'ultimos_30' => 'Últimos 30 días',
        'este_anio' => 'Este año',
        'personalizado' => 'Personalizado',
    ];

    /**
     * Panel de estadísticas con gráficas.
     */
    public function index(Request $request, CalcularEstadisticas $calcular): View
    {
        [$desde, $hasta, $origen, $periodo] = $this->filtros($request);

        return view('pos.admin.estadisticas.index', [
            'estadisticas' => $calcular->handle($desde, $hasta, $origen),
            'desde' => $desde,
            'hasta' => $hasta,
            'origen' => $origen,
            'periodo' => $periodo,
            'periodos' => self::PERIODOS,
        ]);
    }

    /**
     * Descarga un reporte en Excel (.xlsx).
     */
    public function exportar(Request $request, string $reporte, GenerarReporteExcel $generar): BinaryFileResponse
    {
        [$desde, $hasta, $origen] = $this->filtros($request);

        $libro = match ($reporte) {
            'ventas' => $generar->ventas($desde, $hasta, $origen),
            'catalogo' => $generar->catalogo(),
            'empleados' => $generar->empleados(),
        };

        $nombre = match ($reporte) {
            'ventas' => "sudcali-ventas-{$desde->format('Y-m-d')}-a-{$hasta->format('Y-m-d')}.xlsx",
            default => "sudcali-{$reporte}-".now()->format('Y-m-d').'.xlsx',
        };

        return response()
            ->download($libro->guardar(), $nombre, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])
            ->deleteFileAfterSend();
    }

    /**
     * Traduce los filtros de la URL a un rango de fechas.
     *
     * @return array{0: CarbonImmutable, 1: CarbonImmutable, 2: OrigenVenta|null, 3: string}
     *
     * @throws ValidationException
     */
    private function filtros(Request $request): array
    {
        $datos = $request->validate([
            'periodo' => ['nullable', Rule::in(array_keys(self::PERIODOS))],
            'desde' => ['nullable', 'required_if:periodo,personalizado', 'date'],
            'hasta' => ['nullable', 'required_if:periodo,personalizado', 'date', 'after_or_equal:desde'],
            'origen' => ['nullable', Rule::enum(OrigenVenta::class)],
        ], [
            'hasta.after_or_equal' => 'La fecha final debe ser igual o posterior a la inicial.',
        ]);

        $periodo = $datos['periodo'] ?? 'este_mes';
        $hoy = CarbonImmutable::today();

        [$desde, $hasta] = match ($periodo) {
            'mes_pasado' => [$hoy->subMonthNoOverflow()->startOfMonth(), $hoy->subMonthNoOverflow()->endOfMonth()],
            'ultimos_7' => [$hoy->subDays(6), $hoy],
            'ultimos_30' => [$hoy->subDays(29), $hoy],
            'este_anio' => [$hoy->startOfYear(), $hoy],
            'personalizado' => [CarbonImmutable::parse($datos['desde']), CarbonImmutable::parse($datos['hasta'])],
            default => [$hoy->startOfMonth(), $hoy],
        };

        if ($desde->diffInDays($hasta) > 731) {
            throw ValidationException::withMessages(['hasta' => 'El periodo no puede ser mayor a dos años.']);
        }

        $origen = isset($datos['origen']) ? OrigenVenta::from($datos['origen']) : null;

        return [$desde->startOfDay(), $hasta->endOfDay(), $origen, $periodo];
    }
}
