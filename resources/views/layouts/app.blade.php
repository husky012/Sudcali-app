<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo', 'Sudcali - Tienda en Línea & Punto de Venta | La Paz, B.C.S.')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="cuerpo-sudcali">

    <!-- 1. ENCABEZADO PRINCIPAL -->
    <header class="encabezado-sudcali">
        <div class="contenedor-encabezado">
            <a href="/" class="logotipo-marca">
                <h1>SUDCALI</h1>
            </a>
            
            <nav class="navegacion-principal" aria-label="Navegación principal">
                <ul>
                    <li><a href="/">Inicio</a></li>
                   <li><a href="/catalogo">Catálogo</a></li>
                    <li><a href="#pedidos">Seguimiento de Pedidos</a></li>
                    <li><a href="#contacto">Contacto</a></li>
                    <li><a href="/empleados">Personal</a></li>
                </ul>
            </nav>

            <div class="acciones-encabezado">
                <a href="/login" class="boton-secundario">Iniciar Sesión</a>
                <a href="/carrito" class="boton-principal" aria-label="Carrito de compras">Carrito (0)</a>
            </div>
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
<script>
    // Cambia la dirección por la que te genere el túnel de Cloudflare
    const socketUrl = 'wss://career-checkout-mpeg-per.trycloudflare.com';
    const socket = new WebSocket(socketUrl);

    socket.onopen = () => console.log('Conectado al servidor de WebSockets en vivo');
    
    socket.onmessage = (event) => {
        console.log('Evento recibido en tiempo real:', event.data);
        // Si el mensaje indica que hubo un cambio en empleados o carrito, recarga los datos
        if (typeof cargarTabla === 'function') {
            cargarTabla();
        }
    };
</script>
</body>
</html>