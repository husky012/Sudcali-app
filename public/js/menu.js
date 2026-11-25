/**
 * Menú hamburguesa de la tienda.
 */
// Control del menú hamburguesa
document.addEventListener('DOMContentLoaded', () => {
    const botonMenu = document.getElementById('botonMenu');
    const botonCerrar = document.getElementById('botonCerrar');
    const menuPrincipal = document.getElementById('menuPrincipal');
    const fondoMenu = document.getElementById('fondoMenu');

    function abrirMenu() {
        menuPrincipal.classList.add('activo');
        fondoMenu.classList.add('activo');
    }

    function cerrarMenu() {
        menuPrincipal.classList.remove('activo');
        fondoMenu.classList.remove('activo');
    }

    if (botonMenu) botonMenu.addEventListener('click', abrirMenu);
    if (botonCerrar) botonCerrar.addEventListener('click', cerrarMenu);
    if (fondoMenu) fondoMenu.addEventListener('click', cerrarMenu);
});
