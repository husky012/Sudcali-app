<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo', 'Sudcali - Tienda en Línea & Punto de Venta | La Paz, B.C.S.')</title>
    @vite(['resources/css/app.css'])
</head>
<!-- <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests"> -->
<body class="cuerpo-sudcali">

   <!-- 1. ENCABEZADO PRINCIPAL -->
<header class="encabezado-sudcali">
    <div class="contenedor-encabezado">
        <!-- Logotipo a la izquierda -->
        <a href="/" class="logotipo-marca">
            <h1>SUDCALI</h1>
        </a>
        
        <!-- Acciones del lado derecho (Carrito e Ícono de Menú) -->
        <div class="acciones-encabezado">
            <!-- Ícono de Carrito con badge/contador -->
            <a href="/carrito" class="boton-carrito-icono" aria-label="Carrito de compras">
                <svg viewBox="0 0 24 24" width="26" height="26" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
                <span class="contador-carrito">0</span>
            </a>

            <!-- Botón de tres líneas (Hamburguesa) -->
            <button id="botonMenu" class="boton-menu" aria-label="Abrir menú">
                <svg viewBox="0 0 24 24" width="28" height="28" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>
        </div>

        <!-- Menú Lateral Desplegable (Off-Canvas) -->
        <nav id="menuPrincipal" class="navegacion-principal" aria-label="Navegación principal">
            <!-- Botón para cerrar (X) -->
            <button id="botonCerrar" class="boton-cerrar-menu" aria-label="Cerrar menú">&times;</button>
            
            <ul>
                <li><a href="/">Inicio</a></li>
                <li><a href="/catalogo">Catálogo</a></li>
                <li><a href="#pedidos">Seguimiento de Pedidos</a></li>
                <li><a href="#contacto">Contacto</a></li>
                <li><a href="/empleados">Personal</a></li>
                <li class="separador-menu"></li>
                <li><a href="/login" class="enlace-login-menu">Iniciar Sesión</a></li>
            </ul>
        </nav>

        <!-- Fondo oscuro semitransparente al abrir el menú -->
        <div id="fondoMenu" class="fondo-menu-overlay"></div>
    </div>
</header>

    <!-- 2. CONTENIDO DINÁMICO DE LAS VISTAS -->
    <main class="principal-sudcali" id="inicio">
        @yield('contenido')
    </main>

    <!-- 3. PIE DE PÁGINA  -->
    <footer class="pie-pagina-sudcali">
        <div class="contenedor-pie-pagina">
            <div class="marca-pie-pagina">
                <h3>SUDCALI</h3>
                <p>Tienda de ropa & accesorios locales en La Paz, Baja California Sur.</p>
            </div>
            <div class="derechos-pie-pagina">
                <p>&copy; 2026 Sudcali. Todos los derechos reservados. Proyecto Aplicaciones Web II.</p>
            </div>
        </div>
    </footer>

    <!-- SCRIPTS -->
    <script>
        // Websockets
        const socketUrl = 'wss://worked-tons-everywhere-additions.trycloudflare.com';
        const socket = new WebSocket(socketUrl);

        socket.onopen = () => console.log('Conectado al servidor de WebSockets en vivo');
        
        socket.onmessage = (event) => {
            console.log('Evento recibido en tiempo real:', event.data);
            if (typeof cargarTabla === 'function') {
                cargarTabla();
            }
        };

        // Control del menú hamburguesa
        document.addEventListener('DOMContentLoaded', () => {
            const botonMenu = document.getElementById('botonMenu');
            const botonCerrar = document.getElementById('botonCerrar');
            const menuPrincipal = document.getElementById('menuPrincipal');
            const fondoMenu = document.getElementById('fondoMenu');

            function abrirMenu() {
                menuPrincipal.classList.add('activo');
                fondoMenu.classList.add('activo');
            }

            function cerrarMenu() {
                menuPrincipal.classList.remove('activo');
                fondoMenu.classList.remove('activo');
            }

            if (botonMenu) botonMenu.addEventListener('click', abrirMenu);
            if (botonCerrar) botonCerrar.addEventListener('click', cerrarMenu);
            if (fondoMenu) fondoMenu.addEventListener('click', cerrarMenu);
        });
    </script>
</body>
</html>