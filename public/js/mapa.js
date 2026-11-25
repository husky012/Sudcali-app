/**
 * Mapa sencillo con imágenes de OpenStreetMap, sin librerías.
 * Proyección Web Mercator: cada nivel de zoom duplica el tamaño del mundo.
 */
class MapaSudcali {
    static LADO = 256;
    static ZOOM_MIN = 10;
    static ZOOM_MAX = 18;

    /** Centro de La Paz: donde empieza el mapa para marcar un domicilio. */
    static LA_PAZ = { latitud: 24.1426, longitud: -110.3128 };

    constructor(contenedor, zoom = 16) {
        this.contenedor = contenedor;
        this.zoom = zoom;
        this.centro = null;
        this.punto = null;
        this.destino = null;
        this.camino = [];
        this.seguir = true;
        this.imagenes = new Map();

        this.capa = this.crear('div', 'capa-mapa');
        this.lienzo = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        this.lienzo.setAttribute('class', 'camino-mapa');
        this.borde = this.trazo('borde-camino');
        this.linea = this.trazo('linea-camino');
        contenedor.append(this.lienzo);
        this.circulo = this.crear('div', 'precision-mapa');
        this.casa = this.crear('div', 'casa-mapa');
        this.marcador = this.crear('div', 'marcador-mapa');
        this.circulo.hidden = this.casa.hidden = this.marcador.hidden = true;
        this.crearControles();
        this.escucharArrastre();
        new ResizeObserver(() => this.dibujar()).observe(contenedor);
    }

    crear(etiqueta, clase, padre = this.contenedor) {
        const elemento = document.createElement(etiqueta);
        elemento.className = clase;
        padre.append(elemento);
        return elemento;
    }

    trazo(clase) {
        const trazo = document.createElementNS('http://www.w3.org/2000/svg', 'polyline');
        trazo.setAttribute('class', clase);
        this.lienzo.append(trazo);
        return trazo;
    }

    crearControles() {
        const controles = this.crear('div', 'controles-mapa');

        for (const [texto, nombre, accion] of [
            ['+', 'Acercar', () => this.acercar(1)],
            ['−', 'Alejar', () => this.acercar(-1)],
            ['◎', 'Centrar en la ubicación', () => { this.seguir = true; this.encuadrar(); this.dibujar(); }],
        ]) {
            const boton = this.crear('button', '', controles);
            boton.type = 'button';
            boton.textContent = texto;
            boton.setAttribute('aria-label', nombre);
            boton.addEventListener('click', accion);
        }

        const credito = this.crear('a', 'credito-mapa');
        credito.href = 'https://www.openstreetmap.org/copyright';
        credito.target = '_blank';
        credito.rel = 'noopener';
        credito.textContent = '© OpenStreetMap';
    }

    escucharArrastre() {
        let inicio = null;

        this.contenedor.addEventListener('pointerdown', (evento) => {
            if (!this.centro || evento.target.closest('button, a')) return;
            inicio = { x: evento.clientX, y: evento.clientY, centro: this.aPixeles(this.centro) };
            this.contenedor.setPointerCapture(evento.pointerId);
        });

        this.contenedor.addEventListener('pointermove', (evento) => {
            if (!inicio) return;
            this.seguir = false;
            this.centro = this.aCoordenadas({
                x: inicio.centro.x - (evento.clientX - inicio.x),
                y: inicio.centro.y - (evento.clientY - inicio.y),
            });
            this.dibujar();
        });

        for (const tipo of ['pointerup', 'pointercancel']) {
            this.contenedor.addEventListener(tipo, () => { inicio = null; });
        }
    }

    acercar(paso) {
        if (this.destino) this.seguir = false;
        this.zoom = Math.min(MapaSudcali.ZOOM_MAX, Math.max(MapaSudcali.ZOOM_MIN, this.zoom + paso));
        this.dibujar();
    }

    /** Mueve el marcador; el mapa lo sigue hasta que alguien lo arrastra. */
    ubicar(latitud, longitud, precision = 0) {
        this.punto = { latitud, longitud };
        this.precision = precision ?? 0;
        if (this.seguir || !this.centro) this.encuadrar();
        this.dibujar();
    }

    /** Marca el domicilio y dibuja el camino: destino es [latitud, longitud]; ruta viene del servidor. */
    trazar(destino, ruta) {
        this.destino = destino ? { latitud: destino[0], longitud: destino[1] } : null;
        this.camino = ruta ? ruta.puntos.map(([latitud, longitud]) => ({ latitud, longitud })) : [];
        this.lienzo.classList.toggle('camino-aproximado', Boolean(ruta?.aproximada));
        if (this.seguir) this.encuadrar();
        this.dibujar();
    }

    /** Mapa para elegir un punto: la marca queda fija en el centro y se mueve el mapa. */
    elegir(inicio) {
        this.centro = inicio ?? MapaSudcali.LA_PAZ;
        this.seguir = false;
        this.contenedor.classList.add('mapa-elegir');
        this.crear('div', 'mira-mapa');
        this.dibujar();
    }

    /** Centra en la ubicación; si hay domicilio, acomoda el mapa para que se vean los dos. */
    encuadrar() {
        const { clientWidth: ancho, clientHeight: alto } = this.contenedor;
        const puntos = [this.punto, this.destino, ...this.camino].filter(Boolean);
        if (!puntos.length) return;

        if (!this.punto || !this.destino || !ancho) {
            this.centro = this.punto ?? this.destino;
            return;
        }

        for (this.zoom = 17; this.zoom > MapaSudcali.ZOOM_MIN; this.zoom--) {
            const { x, y } = this.extremos(puntos);
            if (x.max - x.min <= ancho - 120 && y.max - y.min <= alto - 90) break;
        }

        const { x, y } = this.extremos(puntos);
        this.centro = this.aCoordenadas({ x: (x.min + x.max) / 2, y: (y.min + y.max) / 2 });
    }

    extremos(puntos) {
        const pixeles = puntos.map((punto) => this.aPixeles(punto));
        const limites = (eje) => ({ min: Math.min(...pixeles.map((p) => p[eje])), max: Math.max(...pixeles.map((p) => p[eje])) });
        return { x: limites('x'), y: limites('y') };
    }

    aPixeles({ latitud, longitud }) {
        const mundo = MapaSudcali.LADO * 2 ** this.zoom;
        const seno = Math.sin((latitud * Math.PI) / 180);
        return {
            x: ((longitud + 180) / 360) * mundo,
            y: (0.5 - Math.log((1 + seno) / (1 - seno)) / (4 * Math.PI)) * mundo,
        };
    }

    aCoordenadas({ x, y }) {
        const mundo = MapaSudcali.LADO * 2 ** this.zoom;
        return {
            latitud: (Math.atan(Math.sinh(Math.PI * (1 - (2 * y) / mundo))) * 180) / Math.PI,
            longitud: (x / mundo) * 360 - 180,
        };
    }

    dibujar() {
        const { clientWidth: ancho, clientHeight: alto } = this.contenedor;
        if (!this.centro || !ancho) return;

        const centro = this.aPixeles(this.centro);
        const origen = { x: centro.x - ancho / 2, y: centro.y - alto / 2 };
        const lado = MapaSudcali.LADO;
        const limite = 2 ** this.zoom;
        const visibles = new Set();

        for (let x = Math.floor(origen.x / lado); x <= Math.floor((origen.x + ancho) / lado); x++) {
            for (let y = Math.floor(origen.y / lado); y <= Math.floor((origen.y + alto) / lado); y++) {
                if (y < 0 || y >= limite) continue;

                const columna = ((x % limite) + limite) % limite;
                const clave = `${this.zoom}/${columna}/${y}`;
                visibles.add(clave);

                let imagen = this.imagenes.get(clave);
                if (!imagen) {
                    imagen = this.crear('img', 'mosaico-mapa', this.capa);
                    imagen.alt = '';
                    imagen.draggable = false;
                    imagen.src = `https://tile.openstreetmap.org/${clave}.png`;
                    this.imagenes.set(clave, imagen);
                }
                imagen.style.transform = `translate(${Math.round(x * lado - origen.x)}px, ${Math.round(y * lado - origen.y)}px)`;
            }
        }

        for (const [clave, imagen] of this.imagenes) {
            if (!visibles.has(clave)) {
                imagen.remove();
                this.imagenes.delete(clave);
            }
        }

        const enPantalla = (punto) => {
            const { x, y } = this.aPixeles(punto);
            return { x: x - origen.x, y: y - origen.y };
        };

        const trazo = this.camino.map(enPantalla).map(({ x, y }) => `${x.toFixed(1)},${y.toFixed(1)}`).join(' ');
        this.borde.setAttribute('points', trazo);
        this.linea.setAttribute('points', trazo);

        this.casa.hidden = !this.destino;
        if (this.destino) {
            const casa = enPantalla(this.destino);
            this.casa.style.transform = `translate(${casa.x}px, ${casa.y}px)`;
        }

        this.marcador.hidden = this.circulo.hidden = !this.punto;
        if (!this.punto) return;

        const { x, y } = enPantalla(this.punto);
        const metrosPorPixel = (156543.03392 * Math.cos((this.punto.latitud * Math.PI) / 180)) / 2 ** this.zoom;
        const radio = Math.min(this.precision / metrosPorPixel, 2000);
        this.marcador.style.transform = `translate(${x}px, ${y}px)`;
        this.circulo.style.transform = `translate(${x - radio}px, ${y - radio}px)`;
        this.circulo.style.width = this.circulo.style.height = `${radio * 2}px`;
    }
}

/** Texto para la distancia y el tiempo que faltan. */
function textoRuta(ruta) {
    if (!ruta) return '';
    const distancia = ruta.metros < 1000 ? `${ruta.metros} m` : `${(ruta.metros / 1000).toFixed(1)} km`;
    return ruta.aproximada
        ? `A ${distancia} en línea recta (no se pudo consultar el camino).`
        : `A ${distancia}, unos ${Math.max(1, Math.round(ruta.segundos / 60))} min en auto.`;
}

/**
 * Panel para marcar el domicilio: <section data-domicilio="ruta" data-token="…"> con un mapa,
 * un botón para abrirlo, otro para guardar y un aviso.
 */
document.querySelectorAll('[data-domicilio]').forEach((panel) => {
    const contenedor = panel.querySelector('.mapa-sudcali');
    const abrir = panel.querySelector('[data-abrir]');
    const guardar = panel.querySelector('[data-guardar]');
    const aviso = panel.querySelector('[role="status"]');
    const { latitud, longitud } = panel.dataset;
    let mapa = null;

    abrir.addEventListener('click', () => {
        abrir.hidden = true;
        contenedor.hidden = guardar.hidden = false;
        aviso.textContent = 'Arrastra el mapa hasta que la casa quede sobre el domicilio y guarda.';

        if (!mapa) {
            mapa = new MapaSudcali(contenedor, latitud ? 17 : 14);
            mapa.elegir(latitud ? { latitud: Number(latitud), longitud: Number(longitud) } : null);
        }
    });

    guardar.addEventListener('click', async () => {
        guardar.disabled = true;

        try {
            const respuesta = await fetch(panel.dataset.domicilio, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': panel.dataset.token },
                body: JSON.stringify(mapa.centro),
            });

            if (respuesta.ok) {
                location.reload();
                return;
            }

            aviso.textContent = respuesta.status === 422
                ? 'Ese punto está fuera de Baja California Sur.'
                : 'No se pudo guardar. Recarga la página.';
        } catch {
            aviso.textContent = 'Sin conexión. Intenta de nuevo.';
        }

        guardar.disabled = false;
    });
});
