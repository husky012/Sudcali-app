---
name: generar-documento
description: Genera reportes técnicos, informes de prácticas, manuales de usuario o documentación en formato Word (.docx) o Markdown (.md) con formato profesional y estructura académica/técnica.
---

# Guía para la Creación de Documentos

Cuando el usuario solicite redactar o generar un documento, informe o manual:

1. **Analiza el proyecto real:** Revisa los archivos relevantes del repositorio (`routes/web.php`, migraciones, controladores, vistas o archivos de configuración como `docker-compose.yml` y `nginx`) para basar el contenido en código y datos reales del proyecto, sin inventar rutas ni tablas.
2. **Estructura del documento:**
   - **Portada / Encabezado:** Título del documento, nombre del proyecto (Sudcali), materia o área, autor y fecha.
   - **Introducción y Objetivo:** Propósito claro del documento o práctica.
   - **Desarrollo Técnico:** Explicación paso a paso organizada con subtítulos claros, fragmentos de código relevantes y tablas descriptivas (por ejemplo, diccionario de datos, endpoints o resultados de pruebas).
   - **Evidencias / Resultados:** Espacio o referencia a las capturas obtenidas y análisis de resultados.
   - **Conclusión:** Cierre técnico sintetizando lo logrado.
3. **Generación del archivo:**
   - Si el usuario pide un **Word (`.docx`)**, escribe y ejecuta un script temporal en Node.js (`.mjs` o `.cjs`) usando la librería `docx` ya instalada en `node_modules` para crear el documento con estilos limpios (títulos, tablas con bordes y párrafos bien estructurados), guárdalo en una carpeta `docs/` dentro del proyecto y elimina el script temporal al terminar.
4. **Confirmación:** Indica al usuario la ruta exacta donde quedó guardado el archivo generado.