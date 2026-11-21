<?php

namespace App\Http\Controllers\Tienda;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogoController extends Controller
{
    /**
     * Página de inicio con productos destacados.
     */
    public function inicio(): View
    {
        return view('inicio', [
            'destacados' => $this->visibles()->has('inventario')->inRandomOrder()->limit(9)->get(),
        ]);
    }

    /**
     * Catálogo con filtro por categoría y búsqueda.
     */
    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'categoria' => ['nullable', 'string', 'max:60'],
            'buscar' => ['nullable', 'string', 'max:100'],
        ]);

        $categoriaActual = $filtros['categoria'] ?? 'todos';

        $productos = $this->visibles()
            ->when($categoriaActual !== 'todos', fn (Builder $consulta) => $consulta->whereHas(
                'categoria', fn (Builder $categoria) => $categoria->where('slug', $categoriaActual)
            ))
            ->when($filtros['buscar'] ?? null, fn (Builder $consulta, string $buscar) => $consulta->where('titulo', 'like', "%{$buscar}%"))
            ->orderBy('titulo')
            ->paginate(12)
            ->withQueryString();

        return view('catalogo', [
            'productos' => $productos,
            'categorias' => Categoria::whereHas('productos', fn (Builder $producto) => $producto->where('activo', true))->orderBy('nombre')->get(),
            'categoriaActual' => $categoriaActual,
            'buscar' => $filtros['buscar'] ?? '',
        ]);
    }

    /**
     * Detalle de un producto visible con sus tallas.
     */
    public function show(Producto $producto): View
    {
        abort_unless($producto->activo, 404);

        $producto->load(['categoria', 'inventario' => fn ($consulta) => $consulta->orderBy('id')]);

        return view('producto-detalle', [
            'producto' => $producto,
            'relacionados' => $this->visibles()
                ->where('categoria_id', $producto->categoria_id)
                ->whereKeyNot($producto->id)
                ->inRandomOrder()
                ->limit(4)
                ->get(),
        ]);
    }

    /**
     * Productos visibles con su categoría y existencias totales.
     *
     * @return Builder<Producto>
     */
    private function visibles(): Builder
    {
        return Producto::query()
            ->where('activo', true)
            ->with('categoria')
            ->withSum('inventario', 'stock');
    }
}
