<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Teams\TeamInvitationController;
use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Support\Facades\Route;

// La aplicación siempre arranca en el login del punto de venta
// (si el empleado ya tiene sesión, el login lo manda directo a vender).
Route::redirect('/', '/pos/login');

// Vista Catálogo de Personal
Route::get('/empleados', function () {
    return view('empleados');
})->name('empleados');

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
require __DIR__.'/tienda.php';
require __DIR__.'/pos.php';
