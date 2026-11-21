<?php

namespace App\Http\Controllers\PuntoVenta\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PuntoVenta\Admin\GuardarCategoriaRequest;
use App\Models\Categoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoriaController extends Controller
{
    /**
     * Listado de categorías con su número de productos.
     */
    public function index(): View
    {
        return view('pos.admin.categorias.index', [
            'categorias' => Categoria::withCount('productos')->orderBy('nombre')->get(),
        ]);
    }

    /**
     * Formulario de alta.
     */
    public function create(): View
    {
        return view('pos.admin.categorias.form', ['categoria' => new Categoria]);
    }

    /**
     * Guarda una categoría.
     */
    public function store(GuardarCategoriaRequest $request): RedirectResponse
    {
        $categoria = Categoria::create($request->validated());

        return to_route('pos.admin.categorias.index')->with('exito', "Categoría «{$categoria->nombre}» creada.");
    }

    /**
     * Formulario de edición.
     */
    public function edit(Categoria $categoria): View
    {
        return view('pos.admin.categorias.form', ['categoria' => $categoria]);
    }

    /**
     * Actualiza una categoría.
     */
    public function update(GuardarCategoriaRequest $request, Categoria $categoria): RedirectResponse
    {
        $categoria->update($request->validated());

        return to_route('pos.admin.categorias.index')->with('exito', "Categoría «{$categoria->nombre}» actualizada.");
    }

    /**
     * Elimina una categoría vacía.
     */
    public function destroy(Categoria $categoria): RedirectResponse
    {
        if ($categoria->productos()->exists()) {
            return back()->with('aviso', "«{$categoria->nombre}» tiene productos. Muévelos a otra categoría antes de eliminarla.");
        }

        $categoria->delete();

        return to_route('pos.admin.categorias.index')->with('exito', "Categoría «{$categoria->nombre}» eliminada.");
    }
}
