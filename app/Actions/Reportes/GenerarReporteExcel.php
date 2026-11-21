<?php

namespace App\Actions\Reportes;

use App\Enums\OrigenVenta;
use App\Models\Categoria;
use App\Models\DetalleVenta;
use App\Models\Empleado;
use App\Models\Inventario;
use App\Models\Producto;
use App\Models\Venta;
use Carbon\CarbonImmutable;

class GenerarReporteExcel
{
    public function __construct(private CalcularEstadisticas $estadisticas) {}

    /**
     * Ventas del periodo: una hoja por venta, una por artículo y un resumen por producto.
     */
    public function ventas(CarbonImmutable $desde, CarbonImmutable $hasta, ?OrigenVenta $origen = null): LibroExcel
    {
        $ventas = $this->estadisticas->ventas($desde->startOfDay(), $hasta->endOfDay(), $origen)
            ->with(['empleado.sucursal', 'cliente'])
            ->withSum('detalles', 'cantidad')
            ->orderBy('created_at')
            ->get();

        $detalles = DetalleVenta::query()
            ->whereIn('venta_id', $ventas->pluck('id'))
            ->with(['venta', 'inventario.producto.categoria'])
            ->orderBy('venta_id')
            ->get();

        return (new LibroExcel)
            ->hoja('Ventas', [
                'Folio', 'Fecha', 'Origen', 'Sucursal', 'Atendió', 'Cliente', 'Método de pago',
                'Estado de envío', 'Piezas', 'Subtotal', 'Envío', 'Total',
            ], $ventas->map(fn (Venta $venta) => [
                $venta->id,
                $venta->created_at,
                $venta->origen === OrigenVenta::Tienda ? 'Tienda física' : 'En línea',
                $venta->empleado?->sucursal->nombre,
                $venta->empleado?->nombreCompleto(),
                $venta->cliente ? trim("{$venta->cliente->nombre} {$venta->cliente->apellido_paterno}") : null,
                ucfirst($venta->metodo_pago->value),
                $venta->estado_envio ? ucfirst($venta->estado_envio->value) : null,
                (int) $venta->detalles_sum_cantidad,
                (float) $venta->subtotal,
                (float) $venta->costo_envio,
                (float) $venta->total,
            ])->all(), columnasMoneda: [9, 10, 11])
            ->hoja('Artículos vendidos', [
                'Folio', 'Fecha', 'Producto', 'Categoría', 'Talla', 'Color', 'Código de barras',
                'Cantidad', 'Precio unitario', 'Importe',
            ], $detalles->map(fn (DetalleVenta $detalle) => [
                $detalle->venta_id,
                $detalle->venta->created_at,
                $detalle->inventario->producto->titulo,
                $detalle->inventario->producto->categoria->nombre,
                $detalle->inventario->talla,
                $detalle->inventario->color,
                $detalle->inventario->codigo_barras,
                $detalle->cantidad,
                (float) $detalle->precio_unitario,
                (float) $detalle->subtotal,
            ])->all(), columnasMoneda: [8, 9])
            ->hoja('Resumen por producto', ['Producto', 'Categoría', 'Piezas', 'Importe'],
                $detalles->groupBy(fn (DetalleVenta $detalle) => $detalle->inventario->producto_id)
                    ->map(fn ($renglones) => [
                        $renglones->first()->inventario->producto->titulo,
                        $renglones->first()->inventario->producto->categoria->nombre,
                        (int) $renglones->sum('cantidad'),
                        (float) $renglones->sum('subtotal'),
                    ])
                    ->sortByDesc(fn (array $fila) => $fila[2])
                    ->values()
                    ->all(), columnasMoneda: [3]);
    }

    /**
     * Catálogo completo: productos, tallas con existencias y categorías.
     */
    public function catalogo(): LibroExcel
    {
        $productos = Producto::with('categoria')
            ->withCount('inventario')
            ->withSum('inventario', 'stock')
            ->orderBy('titulo')
            ->get();

        $variantes = Inventario::with('producto.categoria')->orderBy('producto_id')->orderBy('id')->get();

        return (new LibroExcel)
            ->hoja('Productos', ['ID', 'Producto', 'Categoría', 'Precio', 'Visible', 'Tallas', 'Existencias', 'Descripción'],
                $productos->map(fn (Producto $producto) => [
                    $producto->id,
                    $producto->titulo,
                    $producto->categoria->nombre,
                    (float) $producto->precio,
                    $producto->activo,
                    (int) $producto->inventario_count,
                    (int) $producto->inventario_sum_stock,
                    $producto->descripcion,
                ])->all(), columnasMoneda: [3])
            ->hoja('Inventario', ['Producto', 'Categoría', 'Talla', 'Color', 'Código de barras', 'Existencias', 'Precio', 'Valor en inventario'],
                $variantes->map(fn (Inventario $variante) => [
                    $variante->producto->titulo,
                    $variante->producto->categoria->nombre,
                    $variante->talla,
                    $variante->color,
                    $variante->codigo_barras,
                    $variante->stock,
                    (float) $variante->producto->precio,
                    round($variante->stock * (float) $variante->producto->precio, 2),
                ])->all(), columnasMoneda: [6, 7])
            ->hoja('Categorías', ['ID', 'Categoría', 'Productos'],
                Categoria::withCount('productos')->orderBy('nombre')->get()
                    ->map(fn (Categoria $categoria) => [$categoria->id, $categoria->nombre, (int) $categoria->productos_count])
                    ->all());
    }

    /**
     * Plantilla de empleados (sin contraseñas).
     */
    public function empleados(): LibroExcel
    {
        return (new LibroExcel)
            ->hoja('Empleados', ['ID', 'Nombre', 'Apellido paterno', 'Apellido materno', 'Puesto', 'Sucursal', 'Correo', 'Teléfono', 'Contratación', 'Activo'],
                Empleado::with('sucursal')->orderBy('nombre')->get()
                    ->map(fn (Empleado $empleado) => [
                        $empleado->id,
                        $empleado->nombre,
                        $empleado->apellido_paterno,
                        $empleado->apellido_materno,
                        $empleado->puesto->value,
                        $empleado->sucursal->nombre,
                        $empleado->correo,
                        $empleado->telefono,
                        $empleado->fecha_contratacion->format('Y-m-d'),
                        $empleado->activo,
                    ])->all());
    }
}
