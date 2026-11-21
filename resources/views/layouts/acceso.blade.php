<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo', 'Sudcali - Acceso')</title>

    @vite(['resources/css/app.css'])
</head>
<body class="cuerpo-sudcali cuerpo-acceso">

    <div class="pantalla-acceso">
        <!-- Panel de marca (se oculta en pantallas angostas) -->
        <aside class="panel-marca-acceso" aria-hidden="true">
            <div class="contenido-marca-acceso">
                <span class="logotipo-acceso">SUDCALI</span>
                <p class="lema-acceso">Ropa y accesorios con identidad sudcaliforniana.</p>
                <p class="ubicacion-acceso">La Paz, Baja California Sur</p>
            </div>
        </aside>

        <!-- Formulario -->
        <main class="panel-formulario-acceso">
            <header class="encabezado-acceso">
                <a href="{{ route('home') }}" class="logotipo-marca logotipo-movil-acceso">
                    <span class="logotipo-acceso">SUDCALI</span>
                </a>
                <a href="{{ route('home') }}" class="enlace-volver-tienda">← Volver a la tienda</a>
            </header>

            @if (session('exito'))
                <p class="alerta-pos alerta-exito" role="status">{{ session('exito') }}</p>
            @endif

            @if (session('aviso'))
                <p class="alerta-pos alerta-aviso" role="status">{{ session('aviso') }}</p>
            @endif

            @yield('contenido')

            <footer class="pie-acceso">
                <p>&copy; {{ date('Y') }} Sudcali · Proyecto Aplicaciones Web II</p>
            </footer>
        </main>
    </div>

    @stack('scripts')
</body>
</html>
