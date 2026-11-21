<?php

namespace App\Http\Controllers\PuntoVenta\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PuntoVenta\Admin\AjustarStockRequest;
use App\Models\Categoria;
use App\Models\Inventario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InventarioController extends Controller
{
    /**
     * Existencias considerada bajas.
     */
    public const STOCK_BAJO = 3;

    /**
     * Existencias por talla/color con filtros.
     */
    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'buscar' => ['nullable', 'string', 'max:100'],
            'categoria' => ['nullable', 'integer'],
            'existencias' => ['nullable', 'in:bajo,agotado'],
        ]);

        $variantes = Inventario::query()
            ->with('producto.categoria')
            ->when($filtros['buscar'] ?? null, fn (Builder $consulta, string $buscar) => $consulta->where(
                fn (Builder $coincidencia) => $coincidencia
                    ->where('codigo_barras', $buscar)
                    ->orWhereHas('producto', fn (Builder $producto) => $producto->where('titulo', 'like', "%{$buscar}%"))
            ))
            ->when($filtros['categoria'] ?? null, fn (Builder $consulta, int $categoria) => $consulta->whereHas(
                'producto', fn (Builder $producto) => $producto->where('categoria_id', $categoria)
            ))
            ->when(($filtros['existencias'] ?? null) === 'bajo', fn (Builder $consulta) => $consulta->whereBetween('stock', [1, self::STOCK_BAJO]))
            ->when(($filtros['existencias'] ?? null) === 'agotado', fn (Builder $consulta) => $consulta->where('stock', 0))
            ->orderBy('stock')
            ->orderBy('producto_id')
            ->paginate(25)
            ->withQueryString();

        return view('pos.admin.inventario.index', [
            'variantes' => $variantes,
            'filtros' => $filtros,
            'categorias' => Categoria::orderBy('nombre')->get(),
            'resumen' => [
                'piezas' => (int) Inventario::sum('stock'),
                'bajo' => Inventario::whereBetween('stock', [1, self::STOCK_BAJO])->count(),
                'agotado' => Inventario::where('stock', 0)->count(),
            ],
            'stockBajo' => self::STOCK_BAJO,
        ]);
    }

    /**
     * Registra una entrada, una salida o un conteo físico.
     */
    public function update(AjustarStockRequest $request, Inventario $variante): RedirectResponse
    {
        $cantidad = (int) $request->validated('cantidad');
        $operacion = $request->validated('operacion');

        $variante = DB::transaction(function () use ($variante, $cantidad, $operacion) {
            $variante = Inventario::whereKey($variante->id)->lockForUpdate()->firstOrFail();

            $nuevoStock = match ($operacion) {
                'entrada' => $variante->stock + $cantidad,
                'salida' => $variante->stock - $cantidad,
                'conteo' => $cantidad,
            };

            if ($nuevoStock < 0) {
                throw ValidationException::withMessages([
                    'cantidad' => "Solo hay {$variante->stock} piezas; no puedes sacar {$cantidad}.",
                ])->errorBag("ajuste{$variante->id}");
            }

            $variante->update(['stock' => $nuevoStock]);

            return $variante;
        });

        $variante->load('producto');

        return back()->with('exito', "{$variante->producto->titulo} ({$variante->talla}): ahora hay {$variante->stock} piezas.");
    }
}
