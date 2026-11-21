<?php

namespace App\Http\Controllers\PuntoVenta\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PuntoVenta\Admin\GuardarVarianteRequest;
use App\Models\Inventario;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;

class VarianteController extends Controller
{
    /**
     * Agrega una talla/color al producto. Sin código de barras se genera uno interno.
     */
    public function store(GuardarVarianteRequest $request, Producto $producto): RedirectResponse
    {
        $variante = $producto->inventario()->create([
            ...$request->safe()->except('codigo_barras'),
            'codigo_barras' => $request->validated('codigo_barras') ?? $this->codigoTemporal(),
        ]);

        if ($request->validated('codigo_barras') === null) {
            $variante->update(['codigo_barras' => self::codigoInterno($variante->id)]);
        }

        return to_route('pos.admin.productos.edit', $producto)
            ->with('exito', "Talla {$variante->talla} ({$variante->color}) agregada.");
    }

    /**
     * Corrige talla, color o código de barras.
     */
    public function update(GuardarVarianteRequest $request, Producto $producto, Inventario $variante): RedirectResponse
    {
        abort_unless($variante->producto_id === $producto->id, 404);

        $variante->update([
            ...$request->safe()->except('codigo_barras'),
            'codigo_barras' => $request->validated('codigo_barras') ?? self::codigoInterno($variante->id),
        ]);

        return to_route('pos.admin.productos.edit', $producto)
            ->with('exito', "Talla {$variante->talla} ({$variante->color}) actualizada.");
    }

    /**
     * Elimina una variante que nunca se ha vendido.
     */
    public function destroy(Producto $producto, Inventario $variante): RedirectResponse
    {
        abort_unless($variante->producto_id === $producto->id, 404);

        if ($variante->detallesVenta()->exists()) {
            return back()->with('aviso', 'Esta talla ya tiene ventas y no se puede eliminar. Deja su stock en 0.');
        }

        $variante->delete();

        return to_route('pos.admin.productos.edit', $producto)
            ->with('exito', "Talla {$variante->talla} ({$variante->color}) eliminada.");
    }

    /**
     * Código interno de 13 dígitos con prefijo 200 (rango para uso interno de tiendas).
     */
    public static function codigoInterno(int $id): string
    {
        return '200'.str_pad((string) $id, 10, '0', STR_PAD_LEFT);
    }

    /**
     * Valor único provisional mientras se conoce el id de la variante.
     */
    private function codigoTemporal(): string
    {
        return 'TMP'.bin2hex(random_bytes(8));
    }
}
