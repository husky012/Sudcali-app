# Bitácora de aportaciones — Sudcali

Aplicaciones Web II · Instituto Tecnológico de La Paz · 25 de noviembre de 2026

Se hizo a partir del historial de la rama `main` y solo incluye lo que ese historial permite comprobar. Para compararla:

```bash
git shortlog -sn main
git log main --date=short --pretty="%h %ad %an %s"
```

## Resumen

| Integrante | Autor en Git | Commits hasta `b1998d4` |
| --- | --- | --- |
| Jorge Eduardo Alvarez Aviles | husky012 | 15 (dos son uniones de ramas) |
| Omar Alberto Rios Aviles | OmarRios667 | 2 |
| Logan Ransseth Rios Manriquez | Logan | 1 |

Después de `b1998d4` viene el commit de husky012 que agrega esta bitácora, el mapa con el camino al domicilio y la foto de entrega.

## Jorge Eduardo Alvarez Aviles

| Fecha | Qué hizo | Commit |
| --- | --- | --- |
| 10/09/2026 | Creación del proyecto Laravel | `167e227` |
| 10/09/2026 | Carpeta de documentación y fundamentación (`Sudcali.pdf`) | `be41940` |
| 17/09/2026 | Página de inicio, estilos y animaciones | `55bbd75`, `a925901`, `c8f76e3` |
| 24/09/2026 | Diseño adaptable, plantilla base y tarjeta de producto reutilizable | `8d241b4` |
| 07/10/2026 | Catálogo, carrito, login, registro y detalle de producto (Prácticas 1 a 3) | `6e8ced4` |
| 08/10/2026 | Entrega del catálogo HTML5 y ajustes de la tarjeta de producto | `44088d7` |
| 21/10/2026 | Catálogo de Personal: IndexedDB, Service Worker, Canvas y servidor WebSocket (Práctica 4) | `1139b1c` |
| 28/10/2026 | Ajuste de la conexión WebSocket | `438fa4a` |
| 28/10/2026 | Unión del trabajo de Logan y solución de conflictos | `9beb2f0` |
| 20/11/2026 | Base de datos, punto de venta, tienda en línea, administración, estadísticas, reportes y pruebas | `4e09933` |
| 20/11/2026 | Unión del trabajo de Omar | `55b1f8e` |
| 23/11/2026 | Seguridad: captcha, cabeceras, bitácora de eventos, WebSocket firmado y ampliación de `superficies.md` | `d335cb0` |
| 25/11/2026 | Docker, balanceo y mediciones (Práctica 5); HTTPS, CSP sin scripts en línea, archivos privados y pruebas (Práctica 6); Tareas 4 y 5, y documentación técnica | `b1998d4` |

## Logan Ransseth Rios Manriquez

| Fecha | Qué hizo | Commit |
| --- | --- | --- |
| 28/10/2026 | Rediseño de `catalogo.blade.php`, `empleados.blade.php` e `inicio.blade.php`; menú lateral en `layouts/app.blade.php` y estilos en `app.css` | `68e3bf8` |
| 28/10/2026 | 60 fotografías de producto: 30 gorras, 22 buffs y 8 camisas | `68e3bf8` |

## Omar Alberto Rios Aviles

| Fecha | Qué hizo | Commit |
| --- | --- | --- |
| 28/10/2026 | `docs/balanceoCarga.md`: elección del algoritmo de balanceo para cada caso y para Sudcali | `5dbfee4` |
| 04/11/2026 | `docs/superficies.md`: primera versión del taller de superficies de ataque | `4def39d` |

## Historial completo de `main`

| Fecha | Commit | Autor | Mensaje |
| --- | --- | --- | --- |
| 10/09/2026 | `167e227` | husky012 | Primer commit - Proyecto Laravel |
| 10/09/2026 | `be41940` | husky012 | docs: agregar carpeta de documentacion y PDF |
| 17/09/2026 | `55bbd75` | husky012 | feat: Primer Avance de proyecto: Animaciones |
| 17/09/2026 | `a925901` | husky012 | Primer Avance de proyecto: Animaciones |
| 17/09/2026 | `c8f76e3` | husky012 | Primer Avance de proyecto: Animaciones |
| 24/09/2026 | `8d241b4` | husky012 | Responsive y componente reutilizable |
| 07/10/2026 | `6e8ced4` | husky012 | Avances Practica 1, 2, 3 |
| 08/10/2026 | `44088d7` | husky012 | Catalogo HTML5 |
| 21/10/2026 | `1139b1c` | husky012 | Tarea 4 - Avance 2 |
| 28/10/2026 | `68e3bf8` | Logan | Modificacion Y correcciones |
| 28/10/2026 | `438fa4a` | husky012 | Ajustes de WebSocket, SW |
| 28/10/2026 | `9beb2f0` | husky012 | Conflictos resueltos en app.blade.php (unión) |
| 28/10/2026 | `5dbfee4` | OmarRios667 | Add files via upload (`balanceoCarga.md`) |
| 04/11/2026 | `4def39d` | OmarRios667 | Add files via upload (`superficies.md`) |
| 20/11/2026 | `4e09933` | husky012 | Base de datos añadido, y funcionamiento |
| 20/11/2026 | `55b1f8e` | husky012 | Merge branch 'main' (unión) |
| 23/11/2026 | `d335cb0` | husky012 | Seguridad |
| 25/11/2026 | `b1998d4` | husky012 | Cambios Finales |

Hay además un commit automático de `dependabot[bot]` en otra rama, que no se unió a `main`.

El uso de inteligencia artificial está declarado en [LEEME.md](LEEME.md), apartados 4 y 5.
