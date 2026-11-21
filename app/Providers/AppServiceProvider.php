<?php

namespace App\Providers;

use App\Actions\Tienda\Carrito;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
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
