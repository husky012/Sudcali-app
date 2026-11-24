@extends('layouts.acceso')

@section('titulo', 'Sudcali - Crear Cuenta')

@section('contenido')
<section class="seccion-login">
    <div class="tarjeta-login tarjeta-acceso">
        <header class="encabezado-login">
            <h2>Crear Cuenta</h2>
            <p>Regístrate para comprar y dar seguimiento a tus pedidos</p>
        </header>

        <form action="{{ route('registro.store') }}" method="POST" class="formulario-login">
            @csrf

            <div class="grupo-campo">
                <label for="nombre">Nombre(s) *</label>
                <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" required maxlength="100" autocomplete="given-name" autofocus>
                @include('pos.partials.campo-error', ['campo' => 'nombre'])
            </div>

            <div class="rejilla-dos-columnas">
                <div class="grupo-campo">
                    <label for="apellido_paterno">Apellido paterno *</label>
                    <input type="text" id="apellido_paterno" name="apellido_paterno" value="{{ old('apellido_paterno') }}" required maxlength="100" autocomplete="family-name">
                    @include('pos.partials.campo-error', ['campo' => 'apellido_paterno'])
                </div>
                <div class="grupo-campo">
                    <label for="apellido_materno">Apellido materno</label>
                    <input type="text" id="apellido_materno" name="apellido_materno" value="{{ old('apellido_materno') }}" maxlength="100">
                    @include('pos.partials.campo-error', ['campo' => 'apellido_materno'])
                </div>
            </div>

            <div class="grupo-campo">
                <label for="correo">Correo electrónico *</label>
                <input type="email" id="correo" name="correo" value="{{ old('correo') }}" placeholder="tucorreo@ejemplo.com" required maxlength="255" autocomplete="email">
                @include('pos.partials.campo-error', ['campo' => 'correo'])
            </div>

            <div class="grupo-campo">
                <label for="telefono">Teléfono (opcional)</label>
                <input type="tel" id="telefono" name="telefono" value="{{ old('telefono') }}" placeholder="612 123 4567" inputmode="numeric" autocomplete="tel">
                @include('pos.partials.campo-error', ['campo' => 'telefono'])
            </div>

            <div class="grupo-campo">
                <label for="password">Contraseña *</label>
                <input type="password" id="password" name="password" placeholder="Mínimo 8 caracteres" required minlength="8" autocomplete="new-password">
                @include('pos.partials.campo-error', ['campo' => 'password'])
            </div>

            <div class="grupo-campo">
                <label for="password_confirmation">Confirmar contraseña *</label>
                <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Repite tu contraseña" required minlength="8" autocomplete="new-password">
            </div>

            <div class="opciones-login">
                <label class="recordar-sesion">
                    <input type="checkbox" name="terminos" value="1" required @checked(old('terminos'))>
                    <span>Acepto los términos y condiciones</span>
                </label>
            </div>
            @include('pos.partials.campo-error', ['campo' => 'terminos'])

            <div class="grupo-campo">
                <label for="captcha">Verificación: {{ $preguntaCaptcha }} *</label>
                <input type="text" id="captcha" name="captcha" inputmode="numeric" pattern="[0-9]*" maxlength="3" required autocomplete="off" placeholder="Escribe el resultado">
                @include('pos.partials.campo-error', ['campo' => 'captcha'])
            </div>

            {{-- Campo trampa contra bots: oculto para personas y lectores de pantalla --}}
            <div class="campo-trampa" aria-hidden="true">
                <label for="sitio_web">No llenes este campo</label>
                <input type="text" id="sitio_web" name="sitio_web" tabindex="-1" autocomplete="off">
            </div>
            @include('pos.partials.campo-error', ['campo' => 'sitio_web'])

            <button type="submit" class="boton-login">Crear mi cuenta</button>
        </form>

        <footer class="pie-login">
            <p>¿Ya tienes una cuenta? <a href="{{ route('login') }}">Inicia sesión aquí</a></p>
        </footer>
    </div>
</section>
@endsection
