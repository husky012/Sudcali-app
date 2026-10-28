# Selección de algoritmos de balanceo de carga

Aplicaciones Web II · Unidad 3 · Sesión 15  
Proyecto Sudcali · Tienda web de ropa

Elegimos un algoritmo para cada caso según la duración de las peticiones, la capacidad de los servidores y las conexiones activas. Para Sudcali proponemos menos conexiones y sesiones en un almacén compartido.

| Caso | Algoritmo | Por qué | Qué pasaría con turno rotatorio |
| --- | --- | --- | --- |
| A. Catálogo público | Turno rotatorio (round robin). | Las consultas son cortas y parecidas; los tres servidores tienen la misma capacidad. El reparto por turnos es adecuado. | Funcionaría bien: cada servidor recibiría aproximadamente la misma cantidad de peticiones y una carga similar. |
| B. Reportes de ventas | Menos conexiones (least_conn). | Los reportes duran de 5 a 30 segundos y los cobros unos 100 ms. Elegir el servidor con menos conexiones activas ayuda a repartir el trabajo pendiente. | Podrían acumularse reportes largos en un servidor y retrasar los cobros que reciba, mientras otros tienen menos trabajo. |
| C. Máquinas desiguales | Turno rotatorio ponderado. | Permite enviar más tráfico a los servidores nuevos. Proponemos pesos iniciales 2:2:1, ajustables con pruebas; tener el doble de memoria no garantiza el doble de rendimiento. | Cada servidor recibiría cerca de un tercio de las peticiones. El viejo podría saturarse antes por tener menos recursos. |
| D. Pantalla de cocina | Menos conexiones (least_conn). | Los WebSockets permanecen abiertos todo el turno. Las nuevas conexiones se asignan al servidor que mantiene menos conexiones activas. | Repartiría las conexiones nuevas sin considerar cuántas siguen abiertas. Las desconexiones y reconexiones podrían desequilibrar la carga. |
| E. Sudcali | Menos conexiones (least_conn). | El proyecto combina solicitudes web y WebSockets persistentes. Proponemos este algoritmo para el grupo que los atienda y sesiones en una base de datos compartida entre servidores. | No consideraría las conexiones que siguen abiertas, por lo que algunos servidores podrían acumular más que otros. La sesión requiere una solución compartida con cualquier algoritmo. |

## Aplicación en Sudcali

Los perfiles de tráfico identificados son las solicitudes web de la tienda, las conexiones WebSocket y las operaciones locales del módulo de empleados. Este módulo utiliza TiendaPersonalDB en IndexedDB y un Service Worker con caché sudcali-v2. Las consultas locales y los recursos servidos desde caché no pasan por el balanceador.

Para resolver la sesión proponemos un almacén compartido en el servidor, accesible por todas las instancias de la aplicación. IndexedDB conserva datos en cada navegador y no sustituye ese almacén. Si varios servidores WebSocket deben distribuir los mismos eventos, también necesitaremos un canal compartido, como Redis.

Referencia: material de clase Balanceo de carga · Sesión 15, diapositivas 4 a 6, 11 a 13 y 16 a 17.
