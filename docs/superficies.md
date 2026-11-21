# Superficies de ataque — Sudcali

## Alcance

Análisis estático del código del proyecto. Las medidas descritas son propuestas y todavía no se han implementado ni comprobado mediante pruebas de seguridad. Las categorías corresponden al Top 10 de OWASP 2025.

## Entradas del sistema

- `/login`: formulario de correo y contraseña. La vista Blade tiene `action="#"`; también existe configuración de autenticación con Fortify, por lo que se debe comprobar qué pantalla queda registrada y cómo se conecta al inicio de sesión.
- `/registro`: formulario de nombre, correo, contraseña y confirmación. Actualmente tiene `action="#"` y el registro no está habilitado en las características de Fortify.
- Recuperación y restablecimiento de contraseña: correo, token y contraseña nueva, mediante la funcionalidad habilitada de Fortify.
- `/empleados`: formulario de nombre, correo, puesto, sucursal, estatus, fecha de contratación y fotografía; incluye búsqueda por puesto y operaciones de edición y eliminación.
- Selector de fotografía: recibe archivos del dispositivo y los guarda como parte del registro local del empleado.
- IndexedDB: almacena y devuelve registros de empleados controlados desde el navegador. Sus datos deben tratarse como entradas no confiables al mostrarlos.
- WebSocket: recibe conexiones y mensajes externos en `server-socket.js`; el navegador se conecta a una dirección WSS configurada en la plantilla.
- `/{current_team}/dashboard`: recibe el identificador del equipo en la dirección.
- `/settings/profile` y `/settings/password`: reciben cambios de datos personales, contraseña actual y contraseña nueva; la eliminación de cuenta recibe confirmación de contraseña.
- `/settings/teams` y `/settings/teams/{team}`: reciben nombres e identificadores de equipos y solicitudes para actualizar, eliminar, cambiar o abandonar equipos.
- `/settings/teams/{team}/members/{user}`: recibe identificadores de equipo y usuario, y cambios de rol o solicitudes de eliminación.
- `/settings/teams/{team}/invitations`: recibe correo y rol del destinatario. Las rutas de cancelación, aceptación y rechazo reciben el identificador de la invitación.
- Cookies y sesión: reciben el identificador de sesión y preferencias del navegador.
- `/catalogo`, `/producto/1` y `/carrito`: son vistas públicas. En el código revisado no se identificó un endpoint de pago ni de creación de pedidos; esas funciones deberán analizarse cuando se implementen.

## Las tres más expuestas

| Superficie | Categoría | Qué podría pasar | Medida |
| --- | --- | --- | --- |
| Acceso al módulo `/empleados` y a sus registros locales | A01: Control de acceso roto | Un visitante puede abrir la interfaz sin iniciar sesión. En un navegador compartido podría consultar, modificar o eliminar los registros guardados allí. | Exigir autenticación y permisos de administración. Para datos reales, almacenar los empleados en el servidor y autorizar cada operación con políticas. Retirar la página privada de la caché y limpiar los datos locales al cerrar sesión. |
| Campos de empleados insertados en la tabla mediante `innerHTML` | A05: Inyección, específicamente XSS | Un valor manipulado puede interpretarse como HTML o JavaScript al renderizar una fila, comprometiendo los datos y acciones accesibles desde esa página. | Crear los elementos con `createElement`, colocar los valores de texto con `textContent` y validar por separado las direcciones de imágenes. No interpolar entradas del usuario en HTML ni en manejadores de eventos. |
| Conexiones y mensajes del servidor WebSocket | A01: Control de acceso roto | Si el servicio es accesible, un tercero podría enviar eventos falsos o recibir mensajes retransmitidos globalmente. El envío repetido también puede consumir recursos. | Autenticar cada conexión, autorizar los mensajes por equipo, comprobar el origen permitido y validar el esquema de los eventos. Limitar tamaño, frecuencia y número de conexiones, manteniendo WSS. |

## Evidencia y alcance de los hallazgos

- `routes/web.php`: `/empleados` está fuera de los grupos con middleware de autenticación.
- `resources/views/empleados.blade.php`: `renderizarFilas` interpola nombre, correo, puesto, fecha y dirección de fotografía en `tr.innerHTML`.
- `public/js/empleados-db.js`: los empleados se almacenan en `SudcaliPersonalDB`, dentro del navegador. No se observó sincronización de esos registros con una base de datos del servidor.
- `public/sw.js`: guarda `/empleados` en caché. Proteger solamente la ruta no elimina la copia previamente almacenada ni los registros de IndexedDB.
- `server-socket.js`: retransmite cada mensaje a todos los clientes conectados sin comprobar identidad, equipo u origen. No configura límites propios de tamaño o frecuencia.
- `resources/views/layouts/app.blade.php`: ante un mensaje WebSocket, vuelve a cargar la tabla local. El mensaje no sincroniza los registros entre navegadores ni se inserta directamente como HTML.

El riesgo de XSS observado persiste en el navegador donde se guarda el registro. No se ha demostrado propagación a otros usuarios. Del mismo modo, que `/empleados` sea público no demuestra acceso remoto a los registros locales de otros dispositivos.

## Cómo comprobar las medidas

1. Un visitante sin sesión no debe obtener la interfaz privada de empleados; un usuario sin permisos debe recibir una denegación. Comprobar también el comportamiento sin conexión y después de cerrar sesión.
2. Los valores que contengan caracteres de HTML deben mostrarse como texto, sin crear elementos ni ejecutar código.
3. El WebSocket debe rechazar conexiones sin credenciales válidas y orígenes no permitidos. Un usuario de un equipo no debe recibir eventos de otro. Los eventos inválidos o excesivos deben rechazarse de forma controlada.

## Controles complementarios

- Completar la verificación de correo y evaluar segundo factor para administradores. El modelo `User` actualmente no implementa `MustVerifyEmail`.
- Conservar las políticas y comprobaciones de pertenencia existentes para equipos, miembros e invitaciones.
- Verificar HTTPS, cookies seguras, depuración desactivada y cabeceras de seguridad en producción.
- Validar tipo, dimensiones y tamaño de las fotografías. `accept="image/*"` no es una validación de seguridad; si se habilita una subida al servidor, repetir allí las comprobaciones.
- Auditar dependencias con `composer audit` y `npm audit`, y registrar accesos rechazados, cambios de privilegios y abuso del WebSocket sin almacenar secretos.

## Referencias

- Material de clase: «14. Taller - Superficies de ataque de su proyecto», páginas 1–3, y «Tipos de ataques a sitios web», diapositivas 4–6 y 10.
- [OWASP Top 10:2025](https://top10.owasp.org/2025/0x00_2025-Introduction/).
- [Prevención de XSS basado en DOM](https://cheatsheetseries.owasp.org/cheatsheets/DOM_based_XSS_Prevention_Cheat_Sheet.html).
- [Seguridad de WebSocket](https://cheatsheetseries.owasp.org/cheatsheets/WebSocket_Security_Cheat_Sheet.html).
