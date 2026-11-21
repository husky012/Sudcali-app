<?php

namespace App\Actions\Tienda;

use App\Models\Inventario;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Collection;

/**
 * Carrito de compras guardado en la sesión como [inventario_id => cantidad].
 *
 * Solo guarda ids y cantidades: precios, nombres y existencias se leen
 * siempre de la base de datos.
 */
class Carrito
{
    private const CLAVE = 'carrito';

    public const COSTO_ENVIO = 99.0;

    public const ENVIO_GRATIS_DESDE = 1000.0;

    public function __construct(private Session $sesion) {}

    /**
     * Agrega piezas de una variante sin rebasar sus existencias.
     *
     * @return int Cantidad final de esa variante en el carrito.
     */
    public function agregar(Inventario $variante, int $cantidad): int
    {
        $contenido = $this->contenido();
        $nuevaCantidad = min(($contenido[$variante->id] ?? 0) + $cantidad, $variante->stock);
        $contenido[$variante->id] = $nuevaCantidad;

        $this->guardar($contenido);

        return $nuevaCantidad;
    }

    /**
     * Cambia la cantidad de una variante; 0 la quita.
     */
    public function actualizar(Inventario $variante, int $cantidad): int
    {
        $contenido = $this->contenido();
        $cantidad = min(max($cantidad, 0), $variante->stock);

        if ($cantidad === 0) {
            unset($contenido[$variante->id]);
        } else {
            $contenido[$variante->id] = $cantidad;
        }

        $this->guardar($contenido);

        return $cantidad;
    }

    public function quitar(int $inventarioId): void
    {
        $contenido = $this->contenido();
        unset($contenido[$inventarioId]);
        $this->guardar($contenido);
    }

    public function vaciar(): void
    {
        $this->sesion->forget(self::CLAVE);
    }

    /**
     * Número total de piezas (para el contador del encabezado).
     */
    public function contarPiezas(): int
    {
        return array_sum($this->contenido());
    }

    /**
     * Renglones con datos actuales de la base de datos.
     * Descarta variantes borradas o de productos ocultos.
     *
     * @return Collection<int, array{variante: Inventario, cantidad: int, importe: float, disponible: bool}>
     */
    public function renglones(): Collection
    {
        $contenido = $this->contenido();

        if ($contenido === []) {
            return collect();
        }

        $variantes = Inventario::with('producto')
            ->whereIn('id', array_keys($contenido))
            ->whereHas('producto', fn ($producto) => $producto->where('activo', true))
            ->get()
            ->keyBy('id');

        if ($variantes->count() !== count($contenido)) {
            $this->guardar(array_intersect_key($contenido, $variantes->all()));
        }

        return $variantes->map(fn (Inventario $variante) => [
            'variante' => $variante,
            'cantidad' => $contenido[$variante->id],
            'importe' => round((float) $variante->producto->precio * $contenido[$variante->id], 2),
            'disponible' => $variante->stock >= $contenido[$variante->id],
        ])->values();
    }

    /**
     * Totales del carrito a partir de sus renglones.
     *
     * @param  Collection<int, array{importe: float}>  $renglones
     * @return array{subtotal: float, envio: float, total: float}
     */
    public static function totales(Collection $renglones): array
    {
        $subtotal = round($renglones->sum('importe'), 2);
        $envio = $subtotal === 0.0 || $subtotal >= self::ENVIO_GRATIS_DESDE ? 0.0 : self::COSTO_ENVIO;

        return [
            'subtotal' => $subtotal,
            'envio' => $envio,
            'total' => round($subtotal + $envio, 2),
        ];
    }

    /**
     * @return array<int, int>
     */
    public function contenido(): array
    {
        return $this->sesion->get(self::CLAVE, []);
    }

    /**
     * @param  array<int, int>  $contenido
     */
    private function guardar(array $contenido): void
    {
        $this->sesion->put(self::CLAVE, array_filter($contenido, fn (int $cantidad) => $cantidad > 0));
    }
}
