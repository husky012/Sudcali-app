---
name: pruebas-visuales
description: Ejecuta pruebas funcionales en el navegador usando Chrome DevTools y toma capturas de pantalla para verificar el diseño, rutas y errores de consola en Sudcali.
---

# Guía de Pruebas Visuales con Capturas

Cuando el usuario pida probar una vista, ruta o flujo de la aplicación:

1. **Verifica el servidor:** Asegúrate de que `http://localhost:8000` responda correctamente.
2. **Navega a la ruta:** Usa la herramienta `navigate_page` de `chrome-devtools` hacia la URL solicitada (por ejemplo `http://localhost:8000/pos/login` o `http://localhost:8000/catalogo`).
3. **Inspecciona errores:** Ejecuta `list_console_messages` y `list_network_requests` para detectar fallos 404/500 o errores de JavaScript/CSS.
4. **Captura evidencia:** Toma una captura de pantalla completa con `take_screenshot` en escritorio y, si se solicita diseño responsivo, cambia el viewport a móvil (`390x844`) y toma una segunda captura.
5. **Reporte:** Muestra o describe los resultados visuales encontrados junto con cualquier error detectado.