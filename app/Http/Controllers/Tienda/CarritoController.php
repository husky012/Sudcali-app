<?php

namespace App\Http\Controllers\Tienda;

use App\Actions\Tienda\Carrito;
use App\Http\Controllers\Controller;
use App\Models\Inventario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CarritoController extends Controller
{
    public function __construct(private Carrito $carrito) {}

    /**
     * Muestra el carrito con precios y existencias actuales.
     */
    public function index(): View
    {
        $renglones = $this->carrito->renglones();

        return view('carrito', [
            'renglones' => $renglones,
            'totales' => Carrito::totales($renglones),
            'envioGratisDesde' => Carrito::ENVIO_GRATIS_DESDE,
        ]);
    }

    /**
     * Agrega una talla al carrito. Con "comprar_ahora" lleva directo a pagar.
     */
    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'inventario_id' => ['required', 'integer', Rule::exists('inventario', 'id')],
            'cantidad' => ['required', 'integer', 'min:1', 'max:20'],
        ], [
            'inventario_id.required' => 'Elige una talla.',
        ]);

        $variante = Inventario::with('producto')->findOrFail($datos['inventario_id']);

        if (! $variante->producto->activo || $variante->stock < 1) {
            throw ValidationException::withMessages(['inventario_id' => 'Esa talla está agotada.']);
        }

        $antes = $this->carrito->contenido()[$variante->id] ?? 0;
        $pedidas = $antes + (int) $datos['cantidad'];
        $enCarrito = $this->carrito->agregar($variante, (int) $datos['cantidad']);

        $mensaje = $enCarrito < $pedidas
            ? "Solo hay {$variante->stock} piezas de {$variante->producto->titulo} (talla {$variante->talla}); tienes {$enCarrito} en tu carrito."
            : "{$variante->producto->titulo} (talla {$variante->talla}) se agregó a tu carrito.";

        if ($request->boolean('comprar_ahora')) {
            return to_route('checkout.create');
        }

        return to_route('carrito')->with('exito', $mensaje);
    }

    /**
     * Cambia la cantidad de una talla.
     */
    public function update(Request $request, Inventario $variante): RedirectResponse
    {
        $datos = $request->validate(['cantidad' => ['required', 'integer', 'min:0', 'max:99']]);

        $cantidad = $this->carrito->actualizar($variante, (int) $datos['cantidad']);

        return to_route('carrito')->with(
            $cantidad < (int) $datos['cantidad'] ? 'aviso' : 'exito',
            $cantidad < (int) $datos['cantidad'] ? "Solo hay {$variante->stock} piezas disponibles." : 'Carrito actualizado.',
        );
    }

    /**
     * Quita una talla del carrito.
     */
    public function destroy(int $variante): RedirectResponse
    {
        $this->carrito->quitar($variante);

        return to_route('carrito')->with('exito', 'Producto quitado del carrito.');
    }
}
