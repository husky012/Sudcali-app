/**
 * Detalle de producto: ajusta el máximo de piezas a la talla elegida.
 */
(() => {
    // Ajusta el máximo de piezas a las existencias de la talla elegida.
    const cantidad = document.getElementById('cantidad');
    const aviso = document.getElementById('avisoExistencias');
    if (!cantidad) return;

    function actualizar() {
        const elegida = document.querySelector('input[name="inventario_id"]:checked');
        if (!elegida) return;
        const stock = Number(elegida.dataset.stock);
        cantidad.max = Math.min(20, stock);
        if (Number(cantidad.value) > stock) cantidad.value = stock;
        aviso.textContent = stock <= 3 ? `¡Solo quedan ${stock}!` : '';
    }

    document.querySelectorAll('input[name="inventario_id"]').forEach((radio) => radio.addEventListener('change', actualizar));
    actualizar();
})();
