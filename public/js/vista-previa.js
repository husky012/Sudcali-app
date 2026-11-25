/**
 * Vista previa de la imagen elegida. Uso: <input type="file" data-vista-previa="idDeLaImagen">
 */
document.querySelectorAll('[data-vista-previa]').forEach((entrada) => {
    const vista = document.getElementById(entrada.dataset.vistaPrevia);

    entrada.addEventListener('change', () => {
        const [archivo] = entrada.files;
        if (!archivo) return;
        vista.src = URL.createObjectURL(archivo);
        vista.hidden = false;
    });
});
