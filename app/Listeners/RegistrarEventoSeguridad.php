<?php

namespace App\Listeners;

use App\Support\BitacoraSeguridad;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

/**
 * Lleva a la bitácora de seguridad los eventos de autenticación de todos los guards.
 */
class RegistrarEventoSeguridad
{
    /**
     * Intento de inicio de sesión con credenciales incorrectas.
     */
    public function handleFailed(Failed $evento): void
    {
        BitacoraSeguridad::registrar('login_fallido', [
            'guard' => $evento->guard,
            'correo' => $evento->credentials['correo'] ?? $evento->credentials['email'] ?? null,
        ]);
    }

    /**
     * Se agotaron los intentos permitidos.
     */
    public function handleLockout(Lockout $evento): void
    {
        BitacoraSeguridad::registrar('login_bloqueado', [
            'correo' => $evento->request->input('correo') ?? $evento->request->input('email'),
        ], 'alert', $evento->request);
    }

    /**
     * Inicio de sesión correcto.
     */
    public function handleLogin(Login $evento): void
    {
        BitacoraSeguridad::registrar('login_exitoso', [
            'guard' => $evento->guard,
            'usuario_id' => $evento->user->getAuthIdentifier(),
        ], 'info');
    }

    /**
     * Cierre de sesión.
     */
    public function handleLogout(Logout $evento): void
    {
        BitacoraSeguridad::registrar('logout', [
            'guard' => $evento->guard,
            'usuario_id' => $evento->user?->getAuthIdentifier(),
        ], 'info');
    }
}
