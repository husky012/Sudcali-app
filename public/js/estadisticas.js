/**
 * Estadísticas: filtros y tooltips de las gráficas.
 */
(() => {
    // Mostrar las fechas solo en el periodo personalizado.
    const periodo = document.getElementById('periodo');
    const rango = document.getElementById('rangoPersonalizado');
    periodo.addEventListener('change', () => {
        rango.hidden = periodo.value !== 'personalizado';
        if (periodo.value !== 'personalizado') periodo.form.requestSubmit();
    });
    document.getElementById('origen').addEventListener('change', (evento) => evento.target.form.requestSubmit());

    // Tooltip compartido para todas las marcas con data-tooltip-*.
    const tooltip = document.getElementById('tooltipGrafica');
    const [titulo, valor] = tooltip.children;

    function mostrar(marca, x, y) {
        titulo.textContent = marca.dataset.tooltipTitulo;
        valor.textContent = marca.dataset.tooltipValor;
        tooltip.hidden = false;

        const caja = tooltip.getBoundingClientRect();
        const izquierda = Math.min(Math.max(8, x - caja.width / 2), window.innerWidth - caja.width - 8);
        const arriba = y - caja.height - 12 < 8 ? y + 16 : y - caja.height - 12;
        tooltip.style.left = `${izquierda}px`;
        tooltip.style.top = `${arriba}px`;
    }

    function ocultar() {
        tooltip.hidden = true;
    }

    document.querySelectorAll('[data-tooltip-titulo]').forEach((marca) => {
        marca.addEventListener('pointermove', (evento) => mostrar(marca, evento.clientX, evento.clientY));
        marca.addEventListener('pointerleave', ocultar);
        marca.addEventListener('focus', () => {
            const caja = marca.getBoundingClientRect();
            mostrar(marca, caja.left + caja.width / 2, caja.top);
        });
        marca.addEventListener('blur', ocultar);
    });

    document.addEventListener('keydown', (evento) => evento.key === 'Escape' && ocultar());
    window.addEventListener('scroll', ocultar, { passive: true });
})();
