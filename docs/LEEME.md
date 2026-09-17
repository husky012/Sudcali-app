# Justificación Técnica - Práctica 1

## Declaración de uso de IA
Se utilizó un asistente de IA para estructurar la plantilla del componente e integrar la sintaxis CSS 3D y la variante `prefers-reduced-motion`.

## Rendimiento y Optimizaciones
Se eligió animar exclusivamente las propiedades `transform` y `opacity`. Estas propiedades son procesadas directamente por la GPU mediante la fase de *Compositing*, evitando disparar recálculos de maquetación (*Reflow*) o repintados masivos (*Repaint*).

## Estado de Compatibilidad (Baseline)
- `transform` (2D/3D), `perspective`, `transform-style` y `backface-visibility`: Pertenecen a **Baseline Newly Available / Widely Available**, con soporte completo en todos los navegadores modernos.
- `@media (prefers-reduced-motion)`: Compatibilidad universal en Baseline para garantizar la accesibilidad según pautas WCAG.