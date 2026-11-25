@extends('layouts.acceso')

@section('titulo', 'Sudcali - Iniciar Sesión')

@php
    // La pestaña con errores gana; si no, la que pidió la ruta (/login = cliente, /pos/login = empleado).
    $pestanaActiva = match (true) {
        $errors->hasBag('cliente') => 'cliente',
        $errors->hasBag('empleado') => 'empleado',
        in_array(request('tipo'), ['cliente', 'empleado'], true) => request('tipo'),
        default => $pestanaInicial ?? 'cliente',
    };

    $pestanas = [
        'cliente' => [
            'titulo' => 'Cliente',
            'subtitulo' => 'Compra en la tienda en línea y sigue tus pedidos',
            'accion' => route('tienda.login.store'),
            'url' => route('login'),
            'placeholder' => 'tucorreo@ejemplo.com',
            'recordar' => 'Recordarme',
            'boton' => 'Entrar a la tienda',
        ],
        'empleado' => [
            'titulo' => 'Empleado',
            'subtitulo' => 'Acceso al punto de venta de Sudcali',
            'accion' => route('pos.login.store'),
            'url' => route('pos.login'),
            'placeholder' => 'empleado@sudcali.test',
            'recordar' => 'Mantener sesión en esta caja',
            'boton' => 'Entrar al punto de venta',
        ],
    ];
@endphp

@section('contenido')
<section class="seccion-login">
    <div class="tarjeta-login tarjeta-acceso">
        <header class="encabezado-login">
            <h2>Iniciar Sesión</h2>
            <p>Elige cómo quieres entrar</p>
        </header>

        <div class="pestanas-acceso" role="tablist" aria-label="Tipo de cuenta">
            @foreach ($pestanas as $clave => $pestana)
                <a href="{{ $pestana['url'] }}"
                   id="pestana-{{ $clave }}"
                   class="pestana-acceso"
                   role="tab"
                   aria-selected="{{ $pestanaActiva === $clave ? 'true' : 'false' }}"
                   aria-controls="panel-{{ $clave }}"
                   tabindex="{{ $pestanaActiva === $clave ? '0' : '-1' }}"
                   data-pestana="{{ $clave }}">
                    {{ $pestana['titulo'] }}
                </a>
            @endforeach
        </div>

        @foreach ($pestanas as $clave => $pestana)
            @php($bolsa = $errors->getBag($clave))

            <div id="panel-{{ $clave }}" class="panel-acceso" role="tabpanel" aria-labelledby="pestana-{{ $clave }}" @if ($pestanaActiva !== $clave) hidden @endif>
                <p class="subtitulo-panel-acceso">{{ $pestana['subtitulo'] }}</p>

                <form action="{{ $pestana['accion'] }}" method="POST" class="formulario-login" data-validar-acceso novalidate>
                    @csrf

                    <div class="grupo-campo">
                        <label for="correo-{{ $clave }}">Correo electrónico</label>
                        <input
                            type="email"
                            id="correo-{{ $clave }}"
                            name="correo"
                            value="{{ $bolsa->any() ? old('correo') : '' }}"
                            placeholder="{{ $pestana['placeholder'] }}"
                            maxlength="255"
                            required
                            autocomplete="username"
                            aria-describedby="error-correo-{{ $clave }}"
                            @if ($bolsa->has('correo')) aria-invalid="true" @endif
                        >
                        <p id="error-correo-{{ $clave }}" class="error-campo-pos" aria-live="polite">{{ $bolsa->first('correo') }}</p>
                    </div>

                    <div class="grupo-campo">
                        <label for="password-{{ $clave }}">Contraseña</label>
                        <div class="campo-password-pos">
                            <input
                                type="password"
                                id="password-{{ $clave }}"
                                name="password"
                                placeholder="••••••••"
                                maxlength="255"
                                required
                                autocomplete="current-password"
                                aria-describedby="error-password-{{ $clave }}"
                                @if ($bolsa->has('password')) aria-invalid="true" @endif
                            >
                            <button type="button" class="boton-ver-password-pos" data-ver-password aria-controls="password-{{ $clave }}" aria-pressed="false">Mostrar</button>
                        </div>
                        <p id="error-password-{{ $clave }}" class="error-campo-pos" aria-live="polite">{{ $bolsa->first('password') }}</p>
                    </div>

                    <div class="opciones-login">
                        <label class="recordar-sesion">
                            <input type="checkbox" name="recordar" value="1">
                            <span>{{ $pestana['recordar'] }}</span>
                        </label>
                    </div>

                    <button type="submit" class="boton-login">{{ $pestana['boton'] }}</button>
                </form>

                <footer class="pie-login">
                    @if ($clave === 'cliente')
                        <p>¿No tienes una cuenta? <a href="{{ route('registro') }}">Regístrate aquí</a></p>
                        <p><a href="{{ route('catalogo') }}">Ver el catálogo sin iniciar sesión</a></p>
                    @else
                        <p>¿Olvidaste tu contraseña? Pídele a tu gerente que la restablezca.</p>
                    @endif
                </footer>
            </div>
        @endforeach
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('js/acceso.js') }}" defer></script>
@endpush
