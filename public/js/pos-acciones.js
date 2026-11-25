/**
 * Acciones comunes del punto de venta sin código dentro del HTML (la CSP lo bloquea):
 * - data-confirmar="pregunta" en un formulario: pide confirmación antes de enviarlo.
 * - data-imprimir en un botón: imprime la página.
 * - data-ticket-cobrado: la venta ya se guardó, se borra el ticket pendiente.
 */
document.addEventListener('submit', (evento) => {
    const pregunta = evento.target.dataset.confirmar;
    if (pregunta && !window.confirm(pregunta)) evento.preventDefault();
});

document.querySelectorAll('[data-imprimir]').forEach((boton) => {
    boton.addEventListener('click', () => window.print());
});

if (document.querySelector('[data-ticket-cobrado]')) {
    try {
        sessionStorage.removeItem('sudcali-pos-ticket');
    } catch {
        // Sin almacenamiento de sesión no hay nada que borrar.
    }
}
