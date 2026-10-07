<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Teams\TeamInvitationController;
use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Support\Facades\Route;

// Vista Inicio (Principal)
Route::get('/', function () {
    return view('inicio');
})->name('home');

// Vista Catálogo (Con filtro por categorías)
Route::get('/catalogo', function () {
    return view('catalogo');
})->name('catalogo');

// Vista Detalle de Producto
Route::get('/producto/1', function () {
    return view('producto-detalle');
})->name('producto.detalle');

// Vista Carrito de Compras
Route::get('/carrito', function () {
    return view('carrito');
})->name('carrito');

// Vista Iniciar Sesión (Login)
Route::get('/login', function () {
    return view('login');
})->name('login');
// Vista Registrarse (Registro)
Route::get('/registro', function () {
    return view('registro');
})->name('registro');

Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', EnsureTeamMembership::class])
    ->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard');
    });

Route::middleware(['auth'])->group(function () {
    Route::post('invitations/{invitation}/accept', [TeamInvitationController::class, 'accept'])->name('invitations.accept');
    Route::delete('invitations/{invitation}', [TeamInvitationController::class, 'decline'])->name('invitations.decline');
});

require __DIR__.'/settings.php';
