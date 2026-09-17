<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sudcali - Tienda en Línea & Punto de Venta | La Paz, B.C.S.</title>
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
                    <li><a href="#inicio">Inicio</a></li>
                    <li><a href="#catalogo">Catálogo</a></li>
                    <li><a href="#pedidos">Seguimiento de Pedidos</a></li>
                    <li><a href="#contacto">Contacto</a></li>
                </ul>
            </nav>

            <div class="acciones-encabezado">
                <a href="/login" class="boton-secundario">Iniciar Sesión</a>
                <a href="/carrito" class="boton-principal" aria-label="Carrito de compras">Carrito (0)</a>
            </div>
        </div>
    </header>

    <!-- 2. CONTENIDO PRINCIPAL -->
    <main class="principal-sudcali" id="inicio">
        
        <!-- Sección Principal / Banner -->
        <section class="seccion-bienvenida">
            <div class="contenido-bienvenida">
                <h2>Moda & Estilo en La Paz, B.C.S.</h2>
                <p>Explora nuestro catálogo exclusivo de ropa y accesorios con entregas locales directas a tu domicilio.</p>
                <a href="#catalogo" class="boton-banner">Ver Catálogo</a>
            </div>
        </section>

        <!-- Sección de Catálogo -->
        <section class="seccion-catalogo" id="catalogo">
            <header class="encabezado-seccion">
                <h2>Catálogo Destacado</h2>
                <p>Nuestros artículos populares disponibles en tienda</p>
            </header>

            <div class="cuadricula-productos">
                
                <!-- Tarjeta de Producto 1 -->
                <article class="contenedor-tarjeta-producto" tabindex="0" aria-label="Tarjeta de producto: Playera Sudcali Clásica">
                    <div class="envoltorio-tarjeta-3d">
                        <!-- Cara Frontal -->
                        <div class="cara-tarjeta cara-frontal">
                            <figure class="contenedor-imagen-tarjeta">
                                <img src="https://scontent-qro1-1.xx.fbcdn.net/v/t51.82787-15/681050819_18346264966246782_7528669526424101271_n.jpg?stp=dst-jpg_tt6&cstp=mx899x1198&ctp=s899x1198&_nc_cat=105&ccb=1-7&_nc_sid=127cfc&_nc_ohc=hkBACdLiWTkQ7kNvwGZzmI1&_nc_oc=AdpphfrJVRhUtJzA5ZY7D4LYbN_rLBUb6JMHUf25XsSdZi3Gn7eTmPZFo6DRGZyFUlgjKNQnT5m-OuYzd103YZDv&_nc_zt=23&_nc_ht=scontent-qro1-1.xx&_nc_gid=OEKP16hKgV9ssCyQorGQFA&_nc_ss=7b289&oh=00_AQKyyAV_opHPOqpnvHRxsV1hXNXTeiF4N5KvRF7CR0TSKg&oe=6AB20ED4" alt="Playera Sudcali Clásica" class="imagen-tarjeta">
                                <span class="etiqueta-pulso">¡Nuevo!</span>
                            </figure>
                            <div class="cuerpo-tarjeta">
                                <h3>Playera Sudcali Clásica</h3>
                                <p class="precio">$250 MXN</p>
                                <p class="indicacion">Pasa el cursor o usa Tab para girar</p>
                            </div>
                        </div>

                        <!-- Cara Trasera -->
                        <div class="cara-tarjeta cara-trasera">
                            <h3>Playera Sudcali Clásica</h3>
                            <p class="descripcion">Algodón 100% premium. Diseñada localmente en La Paz. Disponible en todas las tallas.</p>
                            <div class="accion-pie-tarjeta">
                                <button type="button" class="boton-agregar-carrito">Añadir al Carrito</button>
                            </div>
                        </div>
                    </div>
                </article>

                <!-- Tarjeta de Producto 2 -->
                <article class="contenedor-tarjeta-producto" tabindex="0" aria-label="Tarjeta de producto: Sudadera Sudcali Urbana">
                    <div class="envoltorio-tarjeta-3d">
                        <div class="cara-tarjeta cara-frontal">
                            <figure class="contenedor-imagen-tarjeta">
                                <img src="https://scontent-qro1-2.xx.fbcdn.net/v/t39.30808-6/807285053_1074182325319857_1790081506254451619_n.jpg?stp=dst-jpg_tt6&cstp=mx1280x960&ctp=s1280x960&_nc_cat=101&ccb=1-7&_nc_sid=833d8c&_nc_ohc=h7MxK0TNrIMQ7kNvwEH3Gbl&_nc_oc=AdrSv8RldLWak5E0U4l80RBV-BpWbUx6xg2YsqOY7-eX9cs7IjmH40GB1Ei4XdP5fb8QYWRKchr9ZM8ezwDvOq8w&_nc_zt=23&_nc_ht=scontent-qro1-2.xx&_nc_gid=UvO3iASt6kIhzafoatiiyw&_nc_ss=7b289&oh=00_AQLRxaCsqlBiLkmvJ8NqYuBZpHsgfMO4KLocuWMJUMKk8Q&oe=6AB21D76" alt="Gorra Sudcali Delta " class="imagen-tarjeta">
                                <span class="etiqueta-pulso">¡Popular!</span>
                            </figure>
                            <div class="cuerpo-tarjeta">
                                <h3>Gorra Sudcali Delta</h3>
                                <p class="precio">$650 MXN</p>
                                <p class="indicacion">Pasa el cursor o usa Tab para girar</p>
                            </div>
                        </div>
                        <div class="cara-tarjeta cara-trasera">
                            <h3>Gorra Sudcali Delta</h3>
                            <p class="descripcion">Ideal para el dia de malecón. Corte unisex súper cómodo.</p>
                            <div class="accion-pie-tarjeta">
                                <button type="button" class="boton-agregar-carrito">Añadir al Carrito</button>
                            </div>
                        </div>
                    </div>
                </article>

            </div>
        </section>

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

</body>
</html>