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
<script>
    (() => {
        // ---------- Pestañas (teclado: flechas izquierda/derecha) ----------
        const pestanas = [...document.querySelectorAll('.pestana-acceso')];

        function activar(pestana, enfocar = true) {
            pestanas.forEach((otra) => {
                const activa = otra === pestana;
                otra.setAttribute('aria-selected', String(activa));
                otra.tabIndex = activa ? 0 : -1;
                document.getElementById(otra.getAttribute('aria-controls')).hidden = !activa;
            });

            history.replaceState(null, '', pestana.href);
            if (enfocar) pestana.focus();
        }

        pestanas.forEach((pestana, indice) => {
            pestana.addEventListener('click', (evento) => {
                evento.preventDefault();
                activar(pestana, false);
                document.querySelector(`#${pestana.getAttribute('aria-controls')} input[type="email"]`).focus();
            });

            pestana.addEventListener('keydown', (evento) => {
                if (evento.key !== 'ArrowRight' && evento.key !== 'ArrowLeft') return;
                evento.preventDefault();
                const paso = evento.key === 'ArrowRight' ? 1 : -1;
                activar(pestanas[(indice + paso + pestanas.length) % pestanas.length]);
            });
        });

        // ---------- Validación en el navegador (mismos mensajes que el servidor) ----------
        const formatoCorreo = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        function mostrar(campo, mensaje) {
            document.getElementById(campo.getAttribute('aria-describedby')).textContent = mensaje;
            if (mensaje) {
                campo.setAttribute('aria-invalid', 'true');
            } else {
                campo.removeAttribute('aria-invalid');
            }
            return mensaje === '';
        }

        function validarCorreo(campo) {
            const valor = campo.value.trim();
            if (valor === '') return 'Escribe tu correo.';
            if (!formatoCorreo.test(valor)) return 'Escribe un correo válido, por ejemplo nombre@sudcali.test.';
            return '';
        }

        function validarPassword(campo) {
            return campo.value === '' ? 'Escribe tu contraseña.' : '';
        }

        document.querySelectorAll('[data-validar-acceso]').forEach((formulario) => {
            const correo = formulario.querySelector('input[name="correo"]');
            const password = formulario.querySelector('input[name="password"]');
            const boton = formulario.querySelector('button[type="submit"]');

            correo.addEventListener('blur', () => mostrar(correo, validarCorreo(correo)));
            password.addEventListener('blur', () => mostrar(password, validarPassword(password)));
            correo.addEventListener('input', () => correo.hasAttribute('aria-invalid') && mostrar(correo, validarCorreo(correo)));
            password.addEventListener('input', () => password.hasAttribute('aria-invalid') && mostrar(password, validarPassword(password)));

            formulario.addEventListener('submit', (evento) => {
                const correoValido = mostrar(correo, validarCorreo(correo));
                const passwordValida = mostrar(password, validarPassword(password));

                if (!correoValido || !passwordValida) {
                    evento.preventDefault();
                    (correoValido ? password : correo).focus();
                    return;
                }

                boton.disabled = true;
                boton.textContent = 'Entrando…';
            });
        });

        document.querySelectorAll('[data-ver-password]').forEach((boton) => {
            boton.addEventListener('click', () => {
                const campo = document.getElementById(boton.getAttribute('aria-controls'));
                const visible = campo.type === 'text';
                campo.type = visible ? 'password' : 'text';
                boton.textContent = visible ? 'Mostrar' : 'Ocultar';
                boton.setAttribute('aria-pressed', String(!visible));
                campo.focus();
            });
        });

        // Enfoca el campo con error o el correo de la pestaña activa.
        const panelActivo = document.querySelector('.panel-acceso:not([hidden])');
        (panelActivo.querySelector('[aria-invalid="true"]') ?? panelActivo.querySelector('input[type="email"]')).focus();
    })();
</script>
@endpush
