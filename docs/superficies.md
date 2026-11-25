# Superficies de ataque — Sudcali

Equipo: Jorge Eduardo Alvarez Aviles, Logan Ransseth Rios Manriquez, Omar Alberto Rios Aviles

Sudcali es una tienda en línea con punto de venta (POS) para ropa local de La Paz, B.C.S. Está hecha en Laravel 13 y PHP 8.5. Las categorías son las del Top 10 de OWASP 2025. La lista de verificación de las 14 medidas, con evidencia y la forma de demostrarlas, está en [practica6.md](practica6.md). Las pruebas están en `tests/Feature` y se ejecutan con `php artisan test --compact`.

## Entradas del sistema

**Acceso**
- `/login`: pestañas Cliente y Empleado. Cliente envía correo, contraseña y "recordarme" a `POST /tienda/login`. Empleado envía lo mismo a `POST /pos/login`.
- `/registro`: nombre, apellidos, teléfono, correo, contraseña y su confirmación, aceptación de términos, captcha y campo trampa.
- Cookies de sesión y "recordarme" de los guards `cliente`, `empleado` y `web`.

**Tienda en línea (pública y de clientes)**
- `/catalogo`: búsqueda `buscar` y filtro `categoria` en la URL.
- `/producto/{slug}`: el slug del producto.
- `/carrito`:
  - `POST` con `inventario_id` y `cantidad`;
  - `PATCH` y `DELETE /carrito/{variante}`.
- `POST /checkout`: dirección guardada o nueva (calle, colonia, C.P., referencias) y método de pago.
- `/mis-pedidos/{pedido}`: identificador numérico del pedido.

**Punto de venta (solo empleados)**
- `POST /pos/caja` y `PUT /pos/caja`: fondo inicial y efectivo contado al cerrar caja.
- `POST /pos/ventas`: renglones (`inventario_id`, cantidad), método de pago y efectivo recibido.
- `/pos/inventario`: búsqueda por texto o código de barras al vender.
- `/pos/ventas/{venta}` y `/pos/cortes/{corte}`: identificadores de ticket y de corte.
- Administración:
  - empleados, con fotografía;
  - categorías y productos, con imagen;
  - variantes (talla, color, código de barras);
  - ajustes de inventario;
  - estado de pedidos en línea.
- `/pos/admin/estadisticas` y `/exportar/{reporte}`: periodo, fechas, canal y tipo de reporte.
- `/empleados`: catálogo de personal en IndexedDB:
  - formulario con fotografía;
  - búsqueda por puesto;
  - los datos guardados en el navegador, que se tratan como no confiables.
- WebSocket (`server-socket.js`): conexiones y mensajes externos.

**Kit de inicio (Fortify, equipos y ajustes)**
- Recuperación de contraseña.
- `/settings/profile` y `/settings/password`.
- `/settings/teams/{team}`, con sus miembros e invitaciones.
- `/{current_team}/dashboard`.

## Las tres más expuestas

| Superficie | Categoría | Qué podría pasar | Medida aplicada |
| --- | --- | --- | --- |
| Inicio de sesión (cliente y empleado) y `/registro` | A07: Fallas de autenticación | Un atacante podría probar contraseñas sin límite hasta entrar al POS. También podría crear cuentas falsas en masa con un bot. | Bloqueo tras 5 intentos fallidos por correo e IP, con un mensaje genérico que no revela si el correo existe. En el registro: captcha propio de un solo uso, campo trampa y límite de 5 registros por minuto y 20 por día por IP. Contraseñas con bcrypt. |
| `POST /checkout` y `/mis-pedidos/{pedido}` | A01: Control de acceso roto | Cambiar el número en la URL para ver pedidos, direcciones y teléfonos de otros clientes. Enviar en el formulario una dirección ajena o un precio alterado. | Cada pedido se compara con el cliente en sesión; si no es suyo, la respuesta es 404 y se registra en la bitácora. La dirección debe pertenecer al cliente. Precios, envío y existencias se recalculan en el servidor dentro de una transacción con bloqueo (A08). |
| `/empleados` (tabla con IndexedDB y WebSocket) | A05: Inyección (XSS) | Un nombre como `<img src=x onerror=…>` se ejecutaría al dibujar la tabla con `innerHTML`. Además, la página era pública y el WebSocket aceptaba a cualquiera. | La tabla se construye con `createElement` y `textContent`. Las fotos solo se aceptan como archivo de imagen o `data:image` de hasta 2 MB. La ruta exige sesión de gerente o administración. El WebSocket exige un token firmado (HMAC, 30 min), revisa el origen y solo acepta un evento conocido, de hasta 1 KB y 10 mensajes cada 10 s. |

## La medida que cubre cada superficie

| Superficie | Categoría | Medida que la cubre |
| --- | --- | --- |
| `POST /tienda/login`, `POST /pos/login` | A07 | Bloqueo tras 5 intentos por correo e IP, mensaje único y sesión nueva al entrar |
| `POST /login` (guard `web` del kit de inicio) | A07 | Límite de intentos de Fortify. No da acceso al POS ni a la tienda |
| `/forgot-password`, `/reset-password` | A07 | Apagados: revelaban si un correo existe y no se usan |
| `POST /registro` | A07 | Captcha de un uso, campo trampa, límite por IP y mensaje que no confirma la cuenta |
| Cookies de sesión y «recordarme» | A07, A04 | `HttpOnly`, `SameSite=Lax`, `Secure` en producción; se invalidan al salir |
| `/catalogo?buscar=`, `categoria` | A05 | Validación, consulta parametrizada y salida codificada |
| `/producto/{slug}` | A01 | Solo productos visibles; si no, 404 |
| `/carrito` | A08 | Cantidad y variante validadas; el precio sale de la base |
| `POST /checkout` | A01, A06, A08 | La dirección debe ser del cliente; C.P. de B.C.S.; totales y existencias en el servidor, con bloqueo |
| `/mis-pedidos/{pedido}` | A01 | Se compara con el cliente en sesión: 404 y bitácora |
| `/pos/caja`, `/pos/cortes/{corte}` | A01, A06 | Sesión de empleado, solo cortes de su sucursal y montos validados |
| `POST /pos/ventas`, `/pos/inventario` | A06, A08 | Caja abierta, existencias con bloqueo, precio de la base y efectivo suficiente |
| `/pos/ventas/{venta}` | A01 | El ticket debe ser de la sucursal del empleado |
| `/pos/admin/*` | A01 | Permiso por puesto en el servidor: 403 y bitácora |
| Foto de empleado | A05, A01 | Tipo real, extensión, 2 MB, nombre nuevo, disco privado y ruta solo para administración |
| Imagen de producto | A05 | Tipo real, extensión, 4 MB, nombre nuevo, disco privado y ruta que no puede leer otros archivos |
| `/pos/admin/estadisticas/exportar/{reporte}` | A01, A05 | Permiso por puesto y lista cerrada de reportes |
| `/empleados` (IndexedDB) | A05 | Sesión de gerencia o administración; datos insertados como texto; CSP |
| WebSocket | A01, A08 | Token firmado de 30 minutos, origen permitido, un solo evento y límite de mensajes |
| `/settings/*`, `/{current_team}/dashboard` (kit de inicio) | A01 | Sesión del guard `web` y políticas de equipo. Sudcali no las usa |
| `/salud` | A02 | Solo lectura, sin sesión ni cookies; si la base falla responde 503 sin detalles |
| Encabezados `X-Forwarded-*` y `Host` | A02, A04 | Solo se aceptan de `TRUSTED_PROXIES`; la redirección usa `APP_URL` |
| Todas las respuestas, también los errores | A02 | Cinco cabeceras de seguridad, sin `X-Powered-By` |

## Medidas por categoría (Práctica 6)

| Categoría | Medida en el proyecto | Evidencia |
| --- | --- | --- |
| A01 Control de acceso | Guards separados (`empleado`, `cliente`). Permisos por puesto (`AsegurarPermisoPuesto`). Pedidos, tickets, direcciones y variantes se validan contra su dueño o sucursal. Ruta `/empleados` protegida. WebSocket con token. | `SeguridadTest`, `AdministracionPosTest` ("salespeople cannot open…"), `TiendaEnLineaTest` ("customers see their own orders…", "…someone else's address"), `PuntoVentaTest` ("…tickets from another branch") |
| A02 Configuración | Middleware global `CabecerasSeguridad`: CSP sin `unsafe-inline` en scripts, `X-Frame-Options: DENY`, `nosniff`, `Referrer-Policy`, `Permissions-Policy`, HSTS en HTTPS y sin `X-Powered-By`, también en las páginas de error. `APP_DEBUG=false` por defecto en `.env.example`. | `SeguridadTest` ("every web response carries the security headers", "the content security policy does not allow inline scripts", "no view has scripts…", "hsts is only sent over https") |
| A03 Cadena de suministro | `composer.lock` y `package-lock.json` versionados. Auditoría con `composer audit` y `npm audit`. El 24 de noviembre de 2026 se actualizaron las dependencias (`composer update`, `npm audit fix`): `laravel/framework` 13.43, Symfony 8.1.11+ y `league/commonmark` 2.10.7. `composer audit` ya no reporta avisos. | `composer audit` sin avisos. `SeguridadTest`, `AdministracionPosTest`, `TiendaEnLineaTest` y `PuntoVentaTest` pasan tras la actualización |
| A04 Criptografía | Contraseñas con bcrypt (cast `hashed`). En producción: redirección 301 a HTTPS (`ForzarHttps`), cookie segura y sesión cifrada. Detrás del balanceador solo se le cree a `TRUSTED_PROXIES`. | `SeguridadTest` ("in production http requests are redirected to https", "the trusted balancer can announce https", "nobody else can pretend to be the balancer") |
| A05 Inyección | Consultas con Eloquent y parámetros enlazados. Blade escapa toda salida con `{{ }}`. Sin `innerHTML` con datos del usuario. Validación de servidor en todos los formularios con Form Requests. | Pruebas de validación en las cuatro suites |
| A06 Diseño inseguro | Existencias verificadas al cobrar. Envío solo a C.P. de B.C.S. El efectivo recibido debe cubrir el total. Un producto con ventas se oculta en vez de borrarse. | `TiendaEnLineaTest`, `PuntoVentaTest` |
| A07 Autenticación | Bloqueo tras 5 intentos fallidos, mensaje genérico y regeneración de la sesión al entrar. En el registro: captcha, campo trampa y límite por IP. | `PuntoVentaTest` ("login is locked…"), `SeguridadTest` (captcha, campo trampa y límite) |
| A08 Integridad | El servidor ignora precios y totales enviados por el navegador y los toma de la base de datos. | `PuntoVentaTest` ("a cash sale uses database prices…") |
| A09 Bitácora | Canal `seguridad` en `storage/logs/seguridad-AAAA-MM-DD.log`, conservado 90 días. Registra:<ul><li>inicios de sesión fallidos y exitosos;</li><li>bloqueos;</li><li>cierres de sesión;</li><li>accesos denegados;</li><li>intentos de ver pedidos ajenos;</li><li>captcha fallido;</li><li>registros nuevos o limitados.</li></ul>Nunca guarda contraseñas, tokens ni datos personales: el correo y la IP se sustituyen por una huella HMAC. El servidor WebSocket registra rechazos por origen, token y exceso de mensajes. | `SeguridadTest` ("failed logins are logged without the password or personal data", "the lockout…", "denied access…") |
| A10 Excepciones | Las ventas y pedidos se hacen en transacciones: si algo falla no se guarda nada. Con `APP_DEBUG=false` los errores no muestran detalles internos. | `TiendaEnLineaTest` ("checkout fails if stock ran out…") |

Además:
- Al cerrar sesión en el POS se envía `Clear-Site-Data: "cache", "storage"`, que borra la caché, el service worker y la IndexedDB del navegador.
- El service worker ya no guarda páginas privadas en caché.

## Cómo comprobar las medidas

1. Sin sesión, abrir `/empleados`: debe enviar al login del POS. Con un vendedor o almacenista: 403.
2. En `/empleados`, guardar un nombre `<img src=x onerror=alert(1)>`: debe verse como texto y no ejecutarse.
3. Iniciar sesión con contraseña incorrecta 6 veces: el sexto intento queda bloqueado y el evento aparece en `storage/logs/seguridad-*.log`.
4. En `/registro`, responder mal el captcha o enviar 6 registros en un minuto: se rechaza.
5. Con un cliente, abrir `/mis-pedidos/{id}` de otro cliente: 404.
6. Revisar las cabeceras con las herramientas del navegador (pestaña Red) o con `curl -I http://localhost:8000/tienda`.
7. Conectarse al WebSocket sin token o desde otro origen: el servidor responde 401 o 403.

## Pendiente

- **Dependencias de compilación (A03):** quedan 7 avisos de `npm audit` dentro de `vite-plus` 0.3 (`tinypool`, `@vitest/mocker`, `oxfmt`). Son herramientas de desarrollo que no llegan al servidor ni al navegador. Corregirlos exige subir `vite-plus` a la versión 1.x, que trae cambios incompatibles.
- **Publicación:** configurar el servidor con los valores de producción de `.env.example`:
  - `APP_ENV=production` y `APP_DEBUG=false`;
  - HTTPS;
  - `SESSION_SECURE_COOKIE=true`;
  - un `WEBSOCKET_SECRET` propio.
- La CSP ya no permite scripts en línea. Conserva `'unsafe-inline'` solo en estilos, porque las gráficas calculan anchos en el atributo `style`.
- Respaldos automáticos y alertas sobre la bitácora (ver Tarea 5, apartado 6).

## Referencias

- Material de clase: «14. Taller - Superficies de ataque de su proyecto» y «15. Lo que queda del semestre».
- [OWASP Top 10:2025](https://top10.owasp.org/2025/0x00_2025-Introduction/).
- [Prevención de XSS basado en DOM](https://cheatsheetseries.owasp.org/cheatsheets/DOM_based_XSS_Prevention_Cheat_Sheet.html).
- [Seguridad de WebSocket](https://cheatsheetseries.owasp.org/cheatsheets/WebSocket_Security_Cheat_Sheet.html).
- [HTTP Headers Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/HTTP_Headers_Cheat_Sheet.html).
