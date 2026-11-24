<?php

namespace App\Providers;

use App\Actions\Tienda\Carrito;
use App\Support\BitacoraSeguridad;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->compartirDatosDeLaTienda();
        $this->configurarLimitesDeRegistro();
    }

    /**
     * Máximo 5 registros por minuto y 20 por día desde la misma IP (OWASP A07).
     */
    protected function configurarLimitesDeRegistro(): void
    {
        RateLimiter::for('registro', function (Request $request): array {
            $responder = function (Request $request): RedirectResponse {
                BitacoraSeguridad::registrar('registro_limitado', request: $request);

                return back()
                    ->withInput($request->except('password', 'password_confirmation', 'captcha'))
                    ->withErrors(['correo' => 'Demasiados intentos de registro. Espera unos minutos e inténtalo de nuevo.']);
            };

            return [
                Limit::perMinute(5)->by($request->ip())->response($responder),
                Limit::perDay(20)->by($request->ip())->response($responder),
            ];
        });
    }

    /**
     * El encabezado de la tienda necesita el contador del carrito y al cliente en sesión.
     */
    protected function compartirDatosDeLaTienda(): void
    {
        View::composer('layouts.app', function (\Illuminate\View\View $vista): void {
            $vista->with([
                'cantidadCarrito' => app(Carrito::class)->contarPiezas(),
                'clienteActual' => auth('cliente')->user(),
            ]);
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        // En producción todas las URL generadas usan HTTPS (OWASP A04).
        URL::forceHttps(app()->isProduction());

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
