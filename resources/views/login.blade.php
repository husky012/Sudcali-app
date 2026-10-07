@extends('layouts.app')

@section('titulo', 'Sudcali - Iniciar Sesión')

@section('contenido')
<section class="seccion-login">
    <div class="tarjeta-login">
        <header class="encabezado-login">
            <h2>Iniciar Sesión</h2>
            <p>Accede a tu cuenta de Sudcali</p>
        </header>

        <form action="#" method="POST" class="formulario-login">
            @csrf

            <!-- Campo Correo Electrónico -->
            <div class="grupo-campo">
                <label for="email">Correo Electrónico</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="usuario@sudcali.com" 
                    required 
                    autocomplete="email"
                >
            </div>

            <!-- Campo Contraseña -->
            <div class="grupo-campo">
                <label for="password">Contraseña</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="••••••••" 
                    required 
                    autocomplete="current-password"
                >
            </div>

            <!-- Opciones adicionales -->
            <div class="opciones-login">
                <label class="recordar-sesion">
                    <input type="checkbox" name="remember">
                    <span>Recordarme</span>
                </label>
                <a href="#" class="enlace-olvido">¿Olvidaste tu contraseña?</a>
            </div>

            <!-- Botón de Ingreso -->
            <button type="submit" class="boton-login">Ingresar</button>
        </form>

        <footer class="pie-login">
            <p>¿No tienes una cuenta? <a href="/registro">Regístrate aquí</a></p>
        </footer>
    </div>
</section>
@endsection