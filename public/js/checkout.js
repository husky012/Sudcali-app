/**
 * Confirmación del pedido: muestra la dirección nueva y evita el doble envío.
 */
(() => {
    const formulario = document.getElementById('formCheckout');
    const bloqueNueva = document.getElementById('direccionNueva');

    formulario.addEventListener('change', (evento) => {
        if (evento.target.name === 'direccion_id') {
            bloqueNueva.hidden = evento.target.value !== 'nueva';
        }
    });

    formulario.addEventListener('submit', () => {
        const boton = document.getElementById('botonConfirmar');
        boton.disabled = true;
        boton.textContent = 'Registrando pedido…';
    });
})();
