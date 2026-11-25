<?php

use App\Http\Controllers\Tienda\CarritoController;
use App\Http\Controllers\Tienda\CatalogoController;
use App\Http\Controllers\Tienda\CheckoutController;
use App\Http\Controllers\Tienda\ImagenProductoController;
use App\Http\Controllers\Tienda\PedidoController;
use App\Http\Controllers\Tienda\RegistroController;
use App\Http\Controllers\Tienda\SesionClienteController;
use Illuminate\Support\Facades\Route;

/*
| Tienda en línea: catálogo público, carrito en sesión y compras de clientes
| (guard "cliente", tabla clientes).
*/
Route::get('/tienda', [CatalogoController::class, 'inicio'])->name('home');
Route::get('/catalogo', [CatalogoController::class, 'index'])->name('catalogo');
Route::get('/producto/{producto}', [CatalogoController::class, 'show'])->name('producto.detalle');

// Imágenes subidas desde el POS: se guardan fuera de la carpeta pública.
Route::get('/imagenes-producto/{archivo}', ImagenProductoController::class)
    ->where('archivo', '[A-Za-z0-9]{40}\.(jpg|jpeg|png|webp)')
    ->name('producto.imagen');

Route::get('/carrito', [CarritoController::class, 'index'])->name('carrito');
Route::post('/carrito', [CarritoController::class, 'store'])->name('carrito.agregar');
Route::patch('/carrito/{variante}', [CarritoController::class, 'update'])->name('carrito.actualizar');
Route::delete('/carrito/{variante}', [CarritoController::class, 'destroy'])->whereNumber('variante')->name('carrito.quitar');

// Acceso unificado: pestañas Cliente / Empleado
Route::get('/login', [SesionClienteController::class, 'create'])->name('login');
Route::post('/tienda/login', [SesionClienteController::class, 'store'])->name('tienda.login.store');
Route::get('/registro', [RegistroController::class, 'create'])->name('registro');
Route::post('/registro', [RegistroController::class, 'store'])->middleware('throttle:registro')->name('registro.store');

Route::middleware('auth:cliente')->group(function () {
    Route::post('/tienda/logout', [SesionClienteController::class, 'destroy'])->name('tienda.logout');

    Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    Route::get('/mis-pedidos', [PedidoController::class, 'index'])->name('pedidos.index');
    Route::get('/mis-pedidos/{pedido}', [PedidoController::class, 'show'])->name('pedidos.show');
    Route::get('/mis-pedidos/{pedido}/seguimiento', [PedidoController::class, 'seguimiento'])->name('pedidos.seguimiento');
    Route::put('/mis-pedidos/{pedido}/domicilio', [PedidoController::class, 'domicilio'])->name('pedidos.domicilio');
    Route::get('/mis-pedidos/{pedido}/foto-entrega', [PedidoController::class, 'foto'])->name('pedidos.foto');
});
