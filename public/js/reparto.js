/**
 * Reparto: comparte la ubicación de quien lleva el pedido y toma la foto de la entrega.
 */
(() => {
    const { url, olvidar, token } = document.getElementById('reparto').dataset;

    // ---------- Ubicación ----------
    const ENVIAR_CADA_MS = 5000;
    const contenedor = document.getElementById('mapaReparto');
    const avisoUbicacion = document.getElementById('avisoUbicacion');
    const botonUbicacion = document.getElementById('botonUbicacion');
    let mapa = null;
    let vigilancia = null;
    let temporizador = null;
    let ultima = null;

    const peticion = (metodo, destino, datos) => fetch(destino, {
        method: metodo,
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': token },
        body: datos ? JSON.stringify(datos) : undefined,
    });

    async function enviar() {
        if (!ultima) return;

        try {
            const respuesta = await peticion('PUT', url, ultima);
            if (respuesta.status === 409) {
                detener('Este pedido ya no va en camino.');
            } else if (vigilancia !== null && respuesta.ok) {
                const { destino, ruta } = await respuesta.json();
                mapa.trazar(destino, ruta);
                avisoUbicacion.textContent = `Compartiendo tu ubicación. ${textoRuta(ruta)} Deja esta pantalla abierta.`.replace('  ', ' ');
            } else if (vigilancia !== null) {
                avisoUbicacion.textContent = 'No se pudo enviar la ubicación. Recarga la página.';
            }
        } catch {
            avisoUbicacion.textContent = 'Sin conexión. Se intentará de nuevo.';
        }
    }

    function detener(mensaje) {
        navigator.geolocation.clearWatch(vigilancia);
        clearInterval(temporizador);
        vigilancia = temporizador = ultima = null;
        contenedor.hidden = true;
        botonUbicacion.textContent = 'Compartir mi ubicación';
        avisoUbicacion.textContent = mensaje;
    }

    function compartir() {
        botonUbicacion.textContent = 'Dejar de compartir';
        avisoUbicacion.textContent = 'Buscando tu ubicación…';

        vigilancia = navigator.geolocation.watchPosition(({ coords }) => {
            const primera = ultima === null;
            ultima = { latitud: coords.latitude, longitud: coords.longitude, precision: coords.accuracy };
            contenedor.hidden = false;
            mapa ??= new MapaSudcali(contenedor);
            mapa.ubicar(coords.latitude, coords.longitude, coords.accuracy);
            if (primera) enviar();
        }, (error) => {
            if (error.code === error.PERMISSION_DENIED) {
                detener('El navegador no dio permiso para usar la ubicación.');
            } else {
                avisoUbicacion.textContent = 'Todavía no se encuentra tu ubicación.';
            }
        }, { enableHighAccuracy: true, maximumAge: 5000 });

        // Se reenvía aunque no haya movimiento: así el cliente sabe que sigue en vivo.
        temporizador = setInterval(enviar, ENVIAR_CADA_MS);
    }

    botonUbicacion.addEventListener('click', async () => {
        if (vigilancia !== null) {
            detener('No estás compartiendo tu ubicación.');
            await peticion('DELETE', olvidar).catch(() => {});
        } else if (!('geolocation' in navigator) || !window.isSecureContext) {
            avisoUbicacion.textContent = 'La ubicación solo funciona con HTTPS o en localhost.';
        } else {
            compartir();
        }
    });

    // ---------- Foto ----------
    const LADO_MAXIMO = 1600;
    const LADO_MINIMO = 120;
    const entrada = document.getElementById('foto');
    const video = document.getElementById('videoCamara');
    const vista = document.getElementById('vistaFoto');
    const botonCamara = document.getElementById('botonCamara');
    const botonTomar = document.getElementById('botonTomar');
    const botonEntregar = document.getElementById('botonEntregar');
    const avisoCamara = document.getElementById('avisoCamara');
    let camara = null;

    function cerrarCamara() {
        camara?.getTracks().forEach((pista) => pista.stop());
        camara = null;
        video.hidden = botonTomar.hidden = true;
        botonCamara.textContent = 'Abrir cámara';
    }

    /**
     * Vuelve a dibujar la imagen en un canvas: la reduce y le quita los datos ocultos
     * (EXIF), que pueden incluir las coordenadas de la casa.
     */
    async function usarImagen(fuente, ancho, alto) {
        const escala = Math.min(1, LADO_MAXIMO / Math.max(ancho, alto));
        const lienzo = document.createElement('canvas');
        lienzo.width = Math.round(ancho * escala);
        lienzo.height = Math.round(alto * escala);
        lienzo.getContext('2d').drawImage(fuente, 0, 0, lienzo.width, lienzo.height);

        const imagen = await new Promise((listo) => lienzo.toBlob(listo, 'image/jpeg', 0.82));
        const archivos = new DataTransfer();
        archivos.items.add(new File([imagen], 'entrega.jpg', { type: 'image/jpeg' }));
        entrada.files = archivos.files;

        URL.revokeObjectURL(vista.src);
        vista.src = URL.createObjectURL(imagen);
        vista.hidden = false;
        avisoCamara.textContent = 'Foto lista. Revísala y presiona «Entregar pedido».';
    }

    // Al abrirse, la cámara puede tardar en dar una imagen de verdad.
    const actualizarTomar = () => { botonTomar.disabled = video.videoWidth < LADO_MINIMO; };
    video.addEventListener('loadeddata', actualizarTomar);
    video.addEventListener('resize', actualizarTomar);

    botonCamara.addEventListener('click', async () => {
        if (camara) {
            cerrarCamara();
            return;
        }

        if (!navigator.mediaDevices?.getUserMedia) {
            avisoCamara.textContent = 'La cámara solo funciona con HTTPS o en localhost. Elige una imagen.';
            return;
        }

        try {
            camara = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }, audio: false });
            video.srcObject = camara;
            await video.play();
            vista.hidden = true;
            video.hidden = botonTomar.hidden = false;
            actualizarTomar();
            botonCamara.textContent = 'Cerrar cámara';
            avisoCamara.textContent = '';
        } catch {
            cerrarCamara();
            avisoCamara.textContent = 'No se pudo abrir la cámara. Revisa el permiso o elige una imagen.';
        }
    });

    botonTomar.addEventListener('click', async () => {
        await usarImagen(video, video.videoWidth, video.videoHeight);
        cerrarCamara();
    });

    entrada.addEventListener('change', async () => {
        const [archivo] = entrada.files;
        if (!archivo) return;

        botonEntregar.disabled = true;
        try {
            const imagen = await createImageBitmap(archivo);
            await usarImagen(imagen, imagen.width, imagen.height);
        } catch {
            entrada.value = '';
            vista.hidden = true;
            avisoCamara.textContent = 'Ese archivo no es una imagen que se pueda usar.';
        }
        botonEntregar.disabled = false;
    });

    window.addEventListener('pagehide', cerrarCamara);
})();
