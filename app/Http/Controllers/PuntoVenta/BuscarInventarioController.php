<?php

namespace App\Http\Controllers\PuntoVenta;

use App\Http\Controllers\Controller;
use App\Models\Inventario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BuscarInventarioController extends Controller
{
    /**
     * Busca variantes vendibles por código de barras o nombre del producto.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $validado = $request->validate([
            'buscar' => ['nullable', 'string', 'max:100'],
            'categoria' => ['nullable', 'integer'],
        ]);

        $buscar = trim($validado['buscar'] ?? '');

        $variantes = Inventario::query()
            ->with('producto')
            ->whereHas('producto', function (Builder $producto) use ($validado) {
                $producto->where('activo', true)
                    ->when($validado['categoria'] ?? null, fn (Builder $consulta, int $categoria) => $consulta->where('categoria_id', $categoria));
            })
            ->when($buscar !== '', function (Builder $consulta) use ($buscar) {
                $consulta->where(fn (Builder $coincidencia) => $coincidencia
                    ->where('codigo_barras', $buscar)
                    ->orWhereHas('producto', fn (Builder $producto) => $producto->where('titulo', 'like', "%{$buscar}%")));
            })
            ->orderBy('producto_id')
            ->orderBy('id')
            ->limit(40)
            ->get();

        return response()->json([
            'data' => $variantes->map(fn (Inventario $variante) => [
                'id' => $variante->id,
                'titulo' => $variante->producto->titulo,
                'talla' => $variante->talla,
                'color' => $variante->color,
                'codigo_barras' => $variante->codigo_barras,
                'precio' => (float) $variante->producto->precio,
                'stock' => $variante->stock,
                'imagen' => $variante->producto->imagen,
            ]),
        ]);
    }
}
