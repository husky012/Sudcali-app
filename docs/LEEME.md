# LEEME — Práctica 4: Catálogo de Personal con Base de Datos Local

**Asignatura:** Aplicaciones Web II (DAM-2306 / ISC-9A)  
**Proyecto:** Sudcali — Tienda de Ropa & Accesorios Locales en La Paz, B.C.S.  
**Integrantes:** Jorge Eduardo Álvarez Avilés (NC: 21280800) y equipo  
**Fecha de entrega:** Miércoles 21 de octubre de 2026  

---

## 1. Esquema de la Base de Datos (`IndexedDB`)

El almacenamiento local estructurado del catálogo de personal se implementó utilizando **IndexedDB** a través de la base de datos `SudcaliPersonalDB` configurada en su **Versión 2** (`DB_VERSION = 2`).

### ObjectStore: `empleados`
* **KeyPath:** `id` (Clave primaria autoincrementable `autoIncrement: true`).
* **Campos del Registro (8 campos estructurados):**
  1. `id` *(Number, PK)*: Identificador único autogenerado del empleado.
  2. `nombre` *(String)*: Nombre completo del trabajador.
  3. `email` *(String)*: Correo electrónico de contacto.
  4. `puesto` *(String)*: Área/Puesto asignado (Vendedor, Almacén, Administración, Gerente).
  5. `sucursal` *(String)*: Sucursal asignada en La Paz (ej. "La Paz Malecón").
  6. `estatus` *(String)*: Estado operativo del personal ("Activo" / "Inactivo").
  7. `fecha` *(String / Date)*: Fecha de contratación en la empresa.
  8. `foto` *(Object / File / Blob)*: Fotografía guardada directamente como binario nativo en la base de datos, garantizando que el catálogo sea autónomo y portable sin depender de rutas relativas o servidores externos.

---

## 2. Justificación del Índice Seleccionado

Se creó un índice no único sobre el campo **`puesto`** mediante la instrucción:
`store.createIndex('puesto', 'puesto', { unique: false })`

### Justificación Técnica y Operativa:
En el modelo de negocio de **Sudcali**, las consultas más recurrentes realizadas por los administradores del punto de venta no se hacen por la clave primaria (`id`), sino por el área de trabajo del personal para la asignación de turnos y roles en tienda. 

Generar una búsqueda sobre el índice `puesto` (`store.index('puesto').getAll(puesto)`) permite al navegador realizar una búsqueda optimizada directa sobre la estructura de árbol de IndexedDB, evitando el costo computacional de iterar manualmente o hacer un escaneo secuencial (*full scan*) sobre la totalidad de los registros guardados.

---

## 3. Estrategia de Caché del Service Worker

Se registró un **Service Worker** (`public/sw.js`) utilizando la estrategia combinada **Cache First con Fallback de Navegación Offline**.

### Recursos Cacheados:
* `/empleados` (Vista HTML/Blade de gestión de personal).
* `/js/empleados-db.js` (Lógica de IndexedDB y renderizado del gráfico con Canvas API).

### Justificación de la Estrategia:
La estrategia *Cache First* busca primero la respuesta dentro del objeto `caches.open('sudcali-v2')`. Si el recurso existe en el almacenamiento del navegador, lo sirve instantáneamente sin realizar una petición de red. En caso de no haber conexión (`Offline`), el interceptor del evento `fetch` redirige el flujo hacia el caché local de la ruta `/empleados`. 

Esto asegura que el sistema responda al 100% ante caídas de internet o falta de señal móvil en La Paz, permitiendo abrir, listar, filtrar por índice, graficar y registrar altas binarias en la base local sin interrupciones.

---

> **Actualización (Práctica 6):** lo anterior describe la entrega de la Práctica 4. Desde la Práctica 6 la caché es `sudcali-v4` y solo guarda archivos JavaScript públicos. La página `/empleados` ya no se guarda porque es privada, así que abrirla exige conexión; con la página abierta, el catálogo sigue trabajando sobre IndexedDB.

---

## 4. Declaración del Uso de Inteligencia Artificial (IA)

De acuerdo con las reglas de la práctica, se declara la asistencia del modelo de inteligencia artificial **Gemini (Google)** para los siguientes puntos específicos:
* **Generación y estructuración de código:** Asistencia en el manejo de promesas asíncronas nativas de IndexedDB (`indexedDB.open`, `onupgradeneeded` para la migración de versión 1 a versión 2) y en la sintaxis de renderizado 2D con la API de `<canvas>` para la gráfica de barras dinámicas.
* **Resolución de errores y depuración:** Asistencia en la configuración del túnel de Cloudflare (`cloudflared`) mediante el protocolo `--protocol http2` y el ajuste de dependencias ES Modules (`ws`) para el servidor de sockets en tiempo real.
* **Justificación y explicación:** La capacidad de explicar, justificar y depurar en vivo cada línea del código frente al docente fue comprendida, verificada y validada en su totalidad por los miembros del equipo.

---

## 5. Declaración del uso de IA: Prácticas 5 y 6, Tareas 4 y 5 y documentación final

Se declara el uso de **Claude (Anthropic), mediante Claude Code**, del 23 al 25 de noviembre de 2026, para:

* **Revisión:** comparar el código con el material «Mecanismos de seguridad y certificados» y encontrar lo que faltaba.
* **Código:** mover los scripts en línea a `public/js`, CSP sin `unsafe-inline`, cabeceras globales, redirección a HTTPS y proxies de confianza, archivos en disco privado, bitácora con huellas y las pruebas automáticas correspondientes.
* **Mapa y foto de entrega:** tabla `entregas`, pantalla de reparto, mapa en JavaScript, camino al domicilio, cámara y `EntregaTest`.
* **Configuración:** `Dockerfile`, `compose.yml`, `compose.https.yml`, los archivos de Nginx en `docs/servidor`, la ruta `/salud` y el programa de mediciones `medir.mjs`. También ejecutó el montaje en Docker, las mediciones con Apache Bench y las capturas, y redactó la conclusión de `practica5.md` con esos números.
* **Documentos:** redacción de `practica6.md`, `superficies.md`, las Tareas 4 y 5 y la documentación final (modelo de datos, diagramas, manuales de instalación y de usuario, y bitácora de aportaciones a partir de `git log`), a partir del código del proyecto y de las fuentes citadas.
* **Pruebas visuales:** recorrido de las vistas y capturas con Microsoft Edge.
