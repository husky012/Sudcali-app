<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('titulo', 'Punto de Venta') · Sudcali POS</title>

    @vite(['resources/css/app.css'])
</head>
<body class="cuerpo-sudcali cuerpo-pos">

    <header class="encabezado-sudcali encabezado-pos">
        <div class="contenedor-encabezado contenedor-encabezado-pos">
            <a href="{{ route('pos.inicio') }}" class="logotipo-marca">
                <h1>SUDCALI <span class="etiqueta-pos">POS</span></h1>
            </a>

            @auth('empleado')
                @php($empleadoActual = auth('empleado')->user())

                <nav class="navegacion-pos" aria-label="Navegación del punto de venta">
                    <a href="{{ route('pos.ventas.create') }}" @class(['activo' => request()->routeIs('pos.ventas.create')])>Vender</a>
                    <a href="{{ route('pos.ventas.index') }}" @class(['activo' => request()->routeIs('pos.ventas.index', 'pos.ventas.show')])>Ventas del turno</a>
                    <a href="{{ route('pos.caja.edit') }}" @class(['activo' => request()->routeIs('pos.caja.*')])>Cerrar caja</a>

                    @if ($empleadoActual->puesto->puedeGestionarInventario())
                        <span class="separador-navegacion-pos" aria-hidden="true"></span>
                        <a href="{{ route('pos.admin.inventario.index') }}" @class(['activo' => request()->routeIs('pos.admin.inventario.*')])>Inventario</a>
                        <a href="{{ route('pos.admin.pedidos.index') }}" @class(['activo' => request()->routeIs('pos.admin.pedidos.*')])>Pedidos en línea</a>
                    @endif

                    @if ($empleadoActual->puesto->puedeAdministrar())
                        <a href="{{ route('pos.admin.estadisticas.index') }}" @class(['activo' => request()->routeIs('pos.admin.estadisticas.*')])>Estadísticas</a>
                        <a href="{{ route('pos.admin.productos.index') }}" @class(['activo' => request()->routeIs('pos.admin.productos.*')])>Productos</a>
                        <a href="{{ route('pos.admin.categorias.index') }}" @class(['activo' => request()->routeIs('pos.admin.categorias.*')])>Categorías</a>
                        <a href="{{ route('pos.admin.empleados.index') }}" @class(['activo' => request()->routeIs('pos.admin.empleados.*')])>Empleados</a>
                    @endif
                </nav>

                <div class="sesion-pos">
                    <div class="datos-empleado-pos">
                        <strong>{{ $empleadoActual->nombre }} {{ $empleadoActual->apellido_paterno }}</strong>
                        <span>{{ $empleadoActual->puesto->value }} · {{ $empleadoActual->sucursal->nombre }}</span>
                    </div>
                    <form method="POST" action="{{ route('pos.logout') }}">
                        @csrf
                        <button type="submit" class="boton-secundario-pos">Salir</button>
                    </form>
                </div>
            @endauth
        </div>
    </header>

    <main class="principal-pos">
        @if (session('exito'))
            <p class="alerta-pos alerta-exito" role="status">{{ session('exito') }}</p>
        @endif

        @if (session('aviso'))
            <p class="alerta-pos alerta-aviso" role="status">{{ session('aviso') }}</p>
        @endif

        @yield('contenido')
    </main>

    <script src="{{ asset('js/pos-acciones.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
