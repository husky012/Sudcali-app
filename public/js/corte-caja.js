/**
 * Corte de caja: diferencia contra el efectivo esperado.
 */
(() => {
    const campo = document.getElementById('monto_final_real');
    const salida = document.getElementById('diferenciaCorte');
    const esperado = Number(campo.dataset.esperado);
    const moneda = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' });

    campo.addEventListener('input', () => {
        if (campo.value === '') {
            salida.textContent = '';
            return;
        }

        const diferencia = Number(campo.value) - esperado;
        salida.textContent = diferencia === 0
            ? 'La caja cuadra exactamente.'
            : (diferencia > 0 ? 'Sobrante de ' : 'Faltante de ') + moneda.format(Math.abs(diferencia));
        salida.dataset.estado = diferencia === 0 ? 'cuadra' : (diferencia > 0 ? 'sobrante' : 'faltante');
    });
})();
