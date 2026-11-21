<?php

use App\Http\Controllers\PuntoVenta\Admin\CategoriaController;
use App\Http\Controllers\PuntoVenta\Admin\EmpleadoController;
use App\Http\Controllers\PuntoVenta\Admin\EstadisticaController;
use App\Http\Controllers\PuntoVenta\Admin\InventarioController;
use App\Http\Controllers\PuntoVenta\Admin\PedidoEnLineaController;
use App\Http\Controllers\PuntoVenta\Admin\ProductoController;
use App\Http\Controllers\PuntoVenta\Admin\VarianteController;
use App\Http\Controllers\PuntoVenta\BuscarInventarioController;
use App\Http\Controllers\PuntoVenta\CajaController;
use App\Http\Controllers\PuntoVenta\SesionController;
use App\Http\Controllers\PuntoVenta\VentaController;
use App\Http\Middleware\AsegurarCajaAbierta;
use App\Http\Middleware\AsegurarPermisoPuesto;
use Illuminate\Support\Facades\Route;

/*
| Punto de venta: solo para empleados (guard "empleado", tabla empleados).
*/
Route::prefix('pos')->name('pos.')->group(function () {
    Route::get('login', [SesionController::class, 'create'])->name('login');
    Route::post('login', [SesionController::class, 'store'])->name('login.store');

    Route::middleware('auth:empleado')->group(function () {
        Route::post('logout', [SesionController::class, 'destroy'])->name('logout');

        Route::redirect('/', '/pos/vender')->name('inicio');

        Route::get('caja/abrir', [CajaController::class, 'create'])->name('caja.create');
        Route::post('caja', [CajaController::class, 'store'])->name('caja.store');
        Route::get('cortes/{corte}', [CajaController::class, 'show'])->name('caja.show');

        Route::middleware(AsegurarCajaAbierta::class)->group(function () {
            Route::get('caja/cerrar', [CajaController::class, 'edit'])->name('caja.edit');
            Route::put('caja', [CajaController::class, 'update'])->name('caja.update');

            Route::get('vender', [VentaController::class, 'create'])->name('ventas.create');
            Route::post('ventas', [VentaController::class, 'store'])->name('ventas.store');
            Route::get('ventas', [VentaController::class, 'index'])->name('ventas.index');
            Route::get('inventario', BuscarInventarioController::class)->name('inventario.buscar');
        });

        Route::get('ventas/{venta}', [VentaController::class, 'show'])->name('ventas.show');

        /*
        | Administración: Gerente y Administración (empleados y catálogo);
        | Almacén también puede ajustar existencias.
        */
        Route::prefix('admin')->name('admin.')->group(function () {
            Route::middleware(AsegurarPermisoPuesto::class.':administrar')->group(function () {
                Route::get('estadisticas', [EstadisticaController::class, 'index'])->name('estadisticas.index');
                Route::get('estadisticas/exportar/{reporte}', [EstadisticaController::class, 'exportar'])
                    ->whereIn('reporte', ['ventas', 'catalogo', 'empleados'])
                    ->name('estadisticas.exportar');

                Route::resource('empleados', EmpleadoController::class)->except('show');
                Route::resource('categorias', CategoriaController::class)->except('show');
                Route::resource('productos', ProductoController::class)->except('show');

                Route::post('productos/{producto}/variantes', [VarianteController::class, 'store'])->name('productos.variantes.store');
                Route::put('productos/{producto}/variantes/{variante}', [VarianteController::class, 'update'])->name('productos.variantes.update');
                Route::delete('productos/{producto}/variantes/{variante}', [VarianteController::class, 'destroy'])->name('productos.variantes.destroy');
            });

            Route::middleware(AsegurarPermisoPuesto::class.':inventario')->group(function () {
                Route::get('inventario', [InventarioController::class, 'index'])->name('inventario.index');
                Route::patch('inventario/{variante}', [InventarioController::class, 'update'])->name('inventario.update');

                Route::get('pedidos', [PedidoEnLineaController::class, 'index'])->name('pedidos.index');
                Route::patch('pedidos/{pedido}', [PedidoEnLineaController::class, 'update'])->name('pedidos.update');
            });
        });
    });
});
