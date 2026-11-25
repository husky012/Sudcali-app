/**
 * Visor de imagen en grande de los pedidos en línea.
 */
(() => {
    const visor = document.getElementById('visorImagen');
    const imagen = document.getElementById('imagenVisor');
    const titulo = document.getElementById('tituloVisor');

    document.querySelectorAll('[data-ampliar]').forEach((boton) => {
        boton.addEventListener('click', () => {
            imagen.src = boton.dataset.ampliar;
            imagen.alt = boton.dataset.titulo;
            titulo.textContent = boton.dataset.titulo;
            visor.showModal();
        });
    });

    // Cerrar al hacer clic fuera de la imagen (Esc ya lo cierra el navegador).
    visor.addEventListener('click', (evento) => {
        if (evento.target === visor) visor.close();
    });
})();
