<?php

namespace App\Http\Controllers\PuntoVenta\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PuntoVenta\Admin\GuardarProductoRequest;
use App\Models\Categoria;
use App\Models\DetalleVenta;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductoController extends Controller
{
    /**
     * Catálogo de productos con búsqueda y filtros.
     */
    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'buscar' => ['nullable', 'string', 'max:100'],
            'categoria' => ['nullable', 'integer'],
            'estado' => ['nullable', 'in:activos,inactivos'],
        ]);

        $productos = Producto::query()
            ->with('categoria')
            ->withCount('inventario')
            ->withSum('inventario', 'stock')
            ->when($filtros['buscar'] ?? null, fn (Builder $consulta, string $buscar) => $consulta->where('titulo', 'like', "%{$buscar}%"))
            ->when($filtros['categoria'] ?? null, fn (Builder $consulta, int $categoria) => $consulta->where('categoria_id', $categoria))
            ->when(($filtros['estado'] ?? null) === 'activos', fn (Builder $consulta) => $consulta->where('activo', true))
            ->when(($filtros['estado'] ?? null) === 'inactivos', fn (Builder $consulta) => $consulta->where('activo', false))
            ->orderBy('titulo')
            ->paginate(20)
            ->withQueryString();

        return view('pos.admin.productos.index', [
            'productos' => $productos,
            'filtros' => $filtros,
            'categorias' => Categoria::orderBy('nombre')->get(),
        ]);
    }

    /**
     * Formulario de alta.
     */
    public function create(): View
    {
        return view('pos.admin.productos.form', [
            'producto' => new Producto(['activo' => true]),
            'categorias' => Categoria::orderBy('nombre')->get(),
        ]);
    }

    /**
     * Guarda un producto y lleva a capturar sus tallas.
     */
    public function store(GuardarProductoRequest $request): RedirectResponse
    {
        $datos = $request->safe()->except('imagen');

        if ($request->hasFile('imagen')) {
            $datos['imagen'] = '/storage/'.$request->file('imagen')->store('productos', 'public');
        }

        $producto = Producto::create($datos);

        return to_route('pos.admin.productos.edit', $producto)
            ->with('exito', 'Producto creado. Ahora agrega sus tallas y colores.');
    }

    /**
     * Formulario de edición con sus variantes.
     */
    public function edit(Producto $producto): View
    {
        $producto->load(['inventario' => fn ($consulta) => $consulta->withCount('detallesVenta')->orderBy('id')]);

        return view('pos.admin.productos.form', [
            'producto' => $producto,
            'categorias' => Categoria::orderBy('nombre')->get(),
        ]);
    }

    /**
     * Actualiza un producto.
     */
    public function update(GuardarProductoRequest $request, Producto $producto): RedirectResponse
    {
        $datos = $request->safe()->except('imagen');

        if ($request->hasFile('imagen')) {
            $this->borrarImagenSubida($producto);
            $datos['imagen'] = '/storage/'.$request->file('imagen')->store('productos', 'public');
        }

        $producto->update($datos);

        return to_route('pos.admin.productos.edit', $producto)->with('exito', 'Producto actualizado.');
    }

    /**
     * Elimina el producto; si ya se vendió, solo se oculta para conservar el historial.
     */
    public function destroy(Producto $producto): RedirectResponse
    {
        $tieneVentas = DetalleVenta::whereIn('inventario_id', $producto->inventario()->select('id'))->exists();

        if ($tieneVentas) {
            $producto->update(['activo' => false]);

            return to_route('pos.admin.productos.index')
                ->with('aviso', "«{$producto->titulo}» ya tiene ventas: se ocultó del catálogo en lugar de eliminarlo.");
        }

        $this->borrarImagenSubida($producto);
        $producto->delete();

        return to_route('pos.admin.productos.index')->with('exito', "«{$producto->titulo}» eliminado.");
    }

    /**
     * Borra la imagen solo si fue subida desde el POS (no las de public/imagenes).
     */
    private function borrarImagenSubida(Producto $producto): void
    {
        if ($producto->imagen && str_starts_with($producto->imagen, '/storage/')) {
            Storage::disk('public')->delete(substr($producto->imagen, strlen('/storage/')));
        }
    }
}
