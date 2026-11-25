<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo', 'Sudcali - Tienda en Línea & Punto de Venta | La Paz, B.C.S.')</title>
    
    @vite(['resources/css/app.css'])
</head>
<body class="cuerpo-sudcali">

    <!-- 1. ENCABEZADO PRINCIPAL -->
    <header class="encabezado-sudcali">
        <div class="contenedor-encabezado">
            <!-- Logotipo a la izquierda -->
            <a href="{{ route('home') }}" class="logotipo-marca">
                <h1>SUDCALI</h1>
            </a>
            
            <!-- Acciones del lado derecho (Carrito e Ícono de Menú) -->
            <div class="acciones-encabezado">
                <!-- Ícono de Carrito con badge/contador -->
                <a href="{{ route('carrito') }}" class="boton-carrito-icono" aria-label="Carrito de compras: {{ $cantidadCarrito }} {{ $cantidadCarrito === 1 ? 'pieza' : 'piezas' }}">
                    <svg viewBox="0 0 24 24" width="26" height="26" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                    <span class="contador-carrito">{{ $cantidadCarrito }}</span>
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
                    <li><a href="{{ route('home') }}">Inicio</a></li>
                    <li><a href="{{ route('catalogo') }}">Catálogo</a></li>
                    <li><a href="{{ route('carrito') }}">Carrito ({{ $cantidadCarrito }})</a></li>
                    <li><a href="{{ route('pedidos.index') }}">Mis pedidos</a></li>
                    @if (auth('empleado')->user()?->puesto->puedeAdministrar())
                        <li><a href="{{ route('empleados') }}">Personal</a></li>
                    @endif
                    <li class="separador-menu"></li>
                    @if ($clienteActual)
                        <li class="saludo-cliente-menu">Hola, {{ $clienteActual->nombre }}</li>
                        <li>
                            <form method="POST" action="{{ route('tienda.logout') }}">
                                @csrf
                                <button type="submit" class="enlace-login-menu boton-enlace-menu">Cerrar sesión</button>
                            </form>
                        </li>
                    @else
                        <li><a href="{{ route('login') }}" class="enlace-login-menu">Iniciar Sesión</a></li>
                        <li><a href="{{ route('registro') }}">Crear cuenta</a></li>
                    @endif
                </ul>
            </nav>

            <!-- Fondo oscuro semitransparente al abrir el menú -->
            <div id="fondoMenu" class="fondo-menu-overlay"></div>
        </div>
    </header>

    <!-- 2. CONTENIDO DINÁMICO DE LAS VISTAS -->
    <main class="principal-sudcali" id="inicio">
        @if (session('exito'))
            <p class="alerta-pos alerta-exito alerta-tienda" role="status">{{ session('exito') }}</p>
        @endif

        @if (session('aviso'))
            <p class="alerta-pos alerta-aviso alerta-tienda" role="status">{{ session('aviso') }}</p>
        @endif

        @yield('contenido')
    </main>

    <!-- 3. PIE DE PÁGINA -->
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
    <script src="{{ asset('js/menu.js') }}" defer></script>

    @stack('scripts')
</body>
</html>