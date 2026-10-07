@extends('layouts.app')

@section('titulo', 'Sudcali - Crear Cuenta')

@section('contenido')
<section class="seccion-login">
    <div class="tarjeta-login">
        <header class="encabezado-login">
            <h2>Crear Cuenta</h2>
            <p>Regístrate para comprar y dar seguimiento a tus pedidos</p>
        </header>

        <form action="#" method="POST" class="formulario-login">
            @csrf

            <!-- Nombre Completo -->
            <div class="grupo-campo">
                <label for="name">Nombre Completo</label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    placeholder="Ej. Juan Pérez" 
                    required 
                    autocomplete="name"
                >
            </div>

            <!-- Correo Electrónico -->
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

            <!-- Contraseña -->
            <div class="grupo-campo">
                <label for="password">Contraseña</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="Mínimo 8 caracteres" 
                    required 
                    autocomplete="new-password"
                >
            </div>

            <!-- Confirmar Contraseña -->
            <div class="grupo-campo">
                <label for="password_confirmation">Confirmar Contraseña</label>
                <input 
                    type="password" 
                    id="password_confirmation" 
                    name="password_confirmation" 
                    placeholder="Repite tu contraseña" 
                    required 
                    autocomplete="new-password"
                >
            </div>

            <!-- Términos y Condiciones -->
            <div class="opciones-login">
                <label class="recordar-sesion">
                    <input type="checkbox" name="terms" required>
                    <span>Acepto los términos y condiciones</span>
                </label>
            </div>

            <!-- Botón de Registro -->
            <button type="submit" class="boton-login">Registrarme</button>
        </form>

        <footer class="pie-login">
            <p>¿Ya tienes una cuenta? <a href="/login">Inicia sesión aquí</a></p>
        </footer>
    </div>
</section>
@endsection