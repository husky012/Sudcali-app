# Práctica 6 — Medidas de seguridad en Sudcali

Equipo: Jorge Eduardo Alvarez Aviles, Logan Ransseth Rios Manriquez, Omar Alberto Rios Aviles

Lista de verificación de las 14 medidas. Parte de [superficies.md](superficies.md). La razón de cada decisión está en la [Tarea 5](Tarea5_Propuesta_Integral_de_Seguridad.pdf) y el procedimiento de HTTPS en la [Tarea 4](Tarea4_Firma_Certificados_HTTPS.pdf).

**Pruebas (24 de noviembre de 2026):** `SeguridadTest` 35 de 35, `AdministracionPosTest` 42 de 42, `TiendaEnLineaTest` 22 de 22, `PuntoVentaTest` 22 de 22. En total, 202 de 219 pasan, 8 se omiten (funciones apagadas) y 9 fallan. Esas 9 son de páginas del kit de inicio de Laravel que Sudcali no usa.

## Lista de verificación

| # | Medida | Dónde está | Prueba automática | Estado |
| --- | --- | --- | --- | --- |
| **Autenticación** | | | | |
| 1 | Contraseñas con bcrypt | `casts()` de `Cliente` y `Empleado` (`hashed`), `BCRYPT_ROUNDS=12` | `AdministracionPosTest`: «a manager can register an employee who can then log in» | ✅ |
| 2 | Mensaje que no revela si el usuario existe | `SesionController`, `SesionClienteController`, `RegistrarClienteRequest` | `SeguridadTest`: «the login message is the same…», «registering with a taken email…» | ✅ En el registro se puede *deducir* (ver Tarea 5, apartado 5) |
| 3 | Captcha en el registro | `Captcha`, campo trampa `sitio_web` | `SeguridadTest`: «registration is rejected…», «a captcha answer cannot be reused» | ✅ |
| 4 | Límite de intentos | `IniciarSesionRequest` (5 por correo e IP), `AppServiceProvider` (registro) | `PuntoVentaTest`: «login is locked…»; `SeguridadTest`: «registration is limited…» | ✅ [Captura](capturas/practica6/bloqueo-6-intentos.jpg) |
| 5 | Cookies `HttpOnly`, `Secure` y `SameSite` | `config/session.php` | Se comprueba en el navegador | ✅ [Cookies por HTTPS](capturas/practica5/cookies-csp.png) |
| **Aplicación** | | | | |
| 6 | Consultas parametrizadas | Eloquent en toda la aplicación | `SeguridadTest`: «a sql injection in the search…» | ✅ |
| 7 | Validación en el servidor y salida codificada | `app/Http/Requests`, `{{ }}`, `textContent` en `empleados.js` | `SeguridadTest`: «a search with a script is shown as text» | ✅ [Búsqueda](capturas/07-xss-busqueda.png), [nombre de empleado](capturas/practica6/xss-empleados.jpg) |
| 8 | Control de acceso | `PedidoController::show`, `AsegurarPermisoPuesto`, guards separados | `TiendaEnLineaTest`: «customers see their own orders…»; `SeguridadTest`: «only managers…» | ✅ [Captura](capturas/08-empleados-sin-sesion.png) |
| 9 | Subida de archivos segura | `GuardarEmpleadoRequest`, `GuardarProductoRequest`, `EmpleadoController::foto`, `ImagenProductoController` | `AdministracionPosTest`: «a program disguised as an image is rejected», «employee photos are only served…» | ✅ |
| 10 | Bitácora sin contraseñas ni datos personales | `BitacoraSeguridad`, `RegistrarEventoSeguridad`, `server-socket.js` | `SeguridadTest`: «failed logins are logged without the password or personal data» | ✅ Ver muestra abajo |
| **Servidor** | | | | |
| 11 | HTTPS con redirección | `ForzarHttps`, `config/trustedproxy.php`, [compose.https.yml](../compose.https.yml), [nginx-sudcali.conf](servidor/nginx-sudcali.conf) | `SeguridadTest`: «in production http requests are redirected to https», «the trusted balancer…», «nobody else can pretend…» | ✅ Probado en Docker: [redirección y HSTS](capturas/practica5/https.png), [TLS](capturas/practica5/tls.png) |
| 12 | Cinco cabeceras de seguridad | `CabecerasSeguridad` (global) | `SeguridadTest`: «every web response carries the security headers» (página, redirección y 404), «hsts is only sent over https» | ✅ [Página y 404 por HTTPS](capturas/practica5/https.png) |
| 13 | CSP bloqueando un script | `CabecerasSeguridad`, scripts en `public/js` | `SeguridadTest`: «the content security policy does not allow inline scripts», «no view has scripts…» | ✅ [Bloqueo por HTTPS](capturas/practica5/cookies-csp.png) |
| 14 | Depuración apagada, sin credenciales en el repositorio | `.env.example`, `.gitignore`, seeders | `git log --all --oneline -- .env` no devuelve nada | ✅ En el `.env` de cada equipo hay que poner `APP_DEBUG=false` |

## Evidencia

**Medida 13.** Al insertar un script y un `onerror` en `/tienda`, Edge respondió:

```
Executing inline script violates the following Content Security Policy directive
'script-src 'self' …'. The action has been blocked.
Executing inline event handler violates the following Content Security Policy directive
'script-src 'self' …'. The action has been blocked.
```

**Medida 10.** Bloqueo real en `storage/logs/seguridad-2026-11-24.log` (se omite el navegador):

```
local.ALERT: login_bloqueado {"cuenta":"49224ba65ae52335","red":"127.0.0.0","origen":"b648e3117d2a6b4f","metodo":"POST","ruta":"pos/login",…}
```

`cuenta` y `origen` son huellas (HMAC) del correo y de la IP. Los archivos anteriores a ese día pueden tener el formato viejo, con correo e IP.

**Medida 5.** Cabecera `Set-Cookie` en modo producción:

```
Set-Cookie: laravel-session=…; path=/; secure; httponly; samesite=lax
```

`XSRF-TOKEN` no lleva `HttpOnly` a propósito: es el testigo contra CSRF que debe poder leer JavaScript. No sirve para entrar a una cuenta.

## Cómo demostrar cada medida en vivo

**Antes de empezar**

1. Detener `npm run dev` y ejecutar `npm run build`.
2. En `.env`: `APP_DEBUG=false`. Después, `php artisan config:clear`.
3. `php artisan serve`. Abrir `http://localhost:8000/tienda` con F12.

| # | Qué hacer | Qué debe pasar |
| --- | --- | --- |
| 1 | `php artisan tinker --execute 'echo App\Models\Empleado::first()->password;'` | Empieza con `$2y$12$` |
| 2 | En `/login`, entrar con un correo inventado y luego con uno real y contraseña mala | El mismo mensaje |
| 3 | En `/registro`, responder mal la suma | Rechazo y pregunta nueva |
| 4 | En `/pos/login`, fallar 6 veces con el mismo correo | «Demasiados intentos fallidos» |
| 5 | F12 › Aplicación › Cookies | La de sesión con `HttpOnly` y `SameSite`; con HTTPS, también `Secure` |
| 6 | En `/catalogo`, buscar `zzz' OR 1=1 --` | Sin resultados y sin error |
| 7 | En `/catalogo`, buscar `<script>alert(1)</script>` | Se ve como texto |
| 8 | Con un cliente, abrir `/mis-pedidos/` con el número de otro. Con un vendedor, abrir `/pos/admin/empleados` | 404 y 403 |
| 9 | Como gerente, subir como foto un `.php` renombrado a `foto.jpg` | Error de validación |
| 10 | Abrir el archivo más reciente de `storage/logs/seguridad-*.log` | Sin correos, contraseñas ni IP completas |
| 11 | Ver la sección siguiente | 301 hacia `https://` |
| 12 | F12 › Red › documento › Encabezados. Repetir con `/no-existe` | Las cinco cabeceras (HSTS, por HTTPS) |
| 13 | Pegar en la consola el código de abajo | No aparece el aviso y la consola muestra el bloqueo |
| 14 | Abrir `/no-existe`; ejecutar `git log --all --oneline -- .env` | Página sin detalles; sin resultados |

Para la medida 13:

```js
const s = document.createElement('script');
s.textContent = "alert('ataque')";
document.body.append(s);
```

## HTTPS (medida 11)

Se levanta con Docker: el balanceador termina TLS y redirige, y los dos nodos corren en modo producción. Los pasos están en [practica5.md](practica5.md#https-con-docker-práctica-6-medida-11) y la configuración comentada en [balanceo-https.conf](servidor/docker/balanceo-https.conf). Se probó el 24 de noviembre de 2026: 301 desde `http`, TLS 1.2 y 1.3 aceptados, 1.0 y 1.1 rechazados, y un nodo llamado directo por HTTP también redirige.

Con un dominio propio, los pasos son los de la Tarea 4 con [nginx-sudcali.conf](servidor/nginx-sudcali.conf).

## Guion de la exposición (5 minutos, sin leer)

La decisión propuesta es la bitácora. Cambien el guion si para ustedes la más difícil fue otra.

1. **El problema (1 min).** Para investigar un ataque queremos saber quién fue. Si lo guardamos, la bitácora es una lista de clientes con su IP.
2. **Lo que teníamos (1 min).** Creíamos cumplir porque no guardaba contraseñas. Guardaba correo e IP completos.
3. **La salida (1 min 30 s).** Una huella HMAC. Mostrar una línea de la bitácora. Por qué HMAC y no SHA-256 simple: los correos se pueden adivinar; la llave, no.
4. **Lo que perdimos (1 min).** No se puede ir de la huella a la persona, y al cambiar `APP_KEY` las huellas dejan de coincidir.
5. **Cierre (30 s).** Seguimos detectando ataques sin acumular datos de nadie.
