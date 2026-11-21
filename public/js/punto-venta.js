/**
 * Punto de venta Sudcali
 * - Busca variantes (talla/color) en el servidor por código de barras o nombre.
 * - Arma el ticket en el navegador y lo guarda en sessionStorage para no perderlo
 *   si el servidor regresa un error de validación.
 * - El servidor recalcula precios y stock: aquí solo se muestran.
 */
(() => {
    const CLAVE_TICKET = 'sudcali-pos-ticket';
    const moneda = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' });

    const raiz = document.getElementById('puntoVenta');
    const urlBusqueda = raiz.dataset.urlBusqueda;

    const formBusqueda = document.getElementById('formBusqueda');
    const campoBusqueda = document.getElementById('campoBusqueda');
    const filtroCategoria = document.getElementById('filtroCategoria');
    const estadoBusqueda = document.getElementById('estadoBusqueda');
    const resultados = document.getElementById('resultadosBusqueda');

    const listaTicket = document.getElementById('listaTicket');
    const ticketVacio = document.getElementById('ticketVacio');
    const camposArticulos = document.getElementById('camposArticulos');
    const totalArticulos = document.getElementById('totalArticulos');
    const totalVenta = document.getElementById('totalVenta');
    const formCobro = document.getElementById('formCobro');
    const grupoEfectivo = document.getElementById('grupoEfectivo');
    const montoRecibido = document.getElementById('montoRecibido');
    const cambioVenta = document.getElementById('cambioVenta');
    const botonCobrar = document.getElementById('botonCobrar');
    const botonCancelar = document.getElementById('botonCancelar');

    /** @type {Map<number, {id:number, titulo:string, talla:string, color:string, precio:number, stock:number, cantidad:number}>} */
    let ticket = new Map();
    let ultimosResultados = [];
    let temporizador = null;
    let controlador = null;

    // ---------- Persistencia del ticket ----------
    function guardarTicket() {
        try {
            sessionStorage.setItem(CLAVE_TICKET, JSON.stringify([...ticket.values()]));
        } catch (e) { /* almacenamiento no disponible */ }
    }

    function restaurarTicket() {
        try {
            const guardado = JSON.parse(sessionStorage.getItem(CLAVE_TICKET) || '[]');
            ticket = new Map(guardado.map((renglon) => [renglon.id, renglon]));
        } catch (e) {
            ticket = new Map();
        }
    }

    // ---------- Búsqueda ----------
    async function buscar() {
        const parametros = new URLSearchParams();
        if (campoBusqueda.value.trim() !== '') parametros.set('buscar', campoBusqueda.value.trim());
        if (filtroCategoria.value !== '') parametros.set('categoria', filtroCategoria.value);

        controlador?.abort();
        controlador = new AbortController();
        estadoBusqueda.textContent = 'Buscando…';

        try {
            const respuesta = await fetch(`${urlBusqueda}?${parametros}`, {
                headers: { Accept: 'application/json' },
                signal: controlador.signal,
            });

            if (respuesta.status === 401) {
                window.location.reload();
                return [];
            }

            const { data } = await respuesta.json();
            ultimosResultados = data;
            pintarResultados(data);
            estadoBusqueda.textContent = data.length === 0
                ? 'Sin coincidencias.'
                : `${data.length} resultado${data.length === 1 ? '' : 's'}`;

            return data;
        } catch (error) {
            if (error.name !== 'AbortError') {
                estadoBusqueda.textContent = 'No se pudo buscar. Revisa la conexión.';
            }
            return [];
        }
    }

    function pintarResultados(variantes) {
        resultados.replaceChildren(...variantes.map((variante) => {
            const boton = document.createElement('button');
            boton.type = 'button';
            boton.className = 'tarjeta-variante-pos';
            boton.disabled = variante.stock <= 0;
            boton.addEventListener('click', () => {
                agregar(variante);
                campoBusqueda.focus();
            });

            if (variante.imagen) {
                const imagen = document.createElement('img');
                imagen.src = variante.imagen;
                imagen.alt = '';
                imagen.loading = 'lazy';
                boton.append(imagen);
            }

            const titulo = document.createElement('strong');
            titulo.textContent = variante.titulo;

            const detalle = document.createElement('span');
            detalle.className = 'detalle-variante-pos';
            detalle.textContent = `Talla ${variante.talla} · ${variante.color}`;

            const pie = document.createElement('span');
            pie.className = 'pie-variante-pos';
            const precio = document.createElement('span');
            precio.textContent = moneda.format(variante.precio);
            const stock = document.createElement('span');
            stock.className = variante.stock <= 3 ? 'stock-bajo-pos' : '';
            stock.textContent = variante.stock <= 0 ? 'Agotado' : `${variante.stock} disp.`;
            pie.append(precio, stock);

            boton.append(titulo, detalle, pie);
            return boton;
        }));
    }

    // ---------- Ticket ----------
    function agregar(variante, cantidad = 1) {
        const renglon = ticket.get(variante.id) ?? { ...variante, cantidad: 0 };
        const nuevaCantidad = Math.min(renglon.cantidad + cantidad, variante.stock);

        if (nuevaCantidad === renglon.cantidad) {
            estadoBusqueda.textContent = `No hay más stock de ${variante.titulo} (${variante.talla}).`;
            return;
        }

        renglon.cantidad = nuevaCantidad;
        renglon.stock = variante.stock;
        ticket.set(variante.id, renglon);
        actualizarTicket();
    }

    function cambiarCantidad(id, cambio) {
        const renglon = ticket.get(id);
        if (!renglon) return;

        renglon.cantidad = Math.min(renglon.cantidad + cambio, renglon.stock);
        if (renglon.cantidad <= 0) ticket.delete(id);
        actualizarTicket();
    }

    function calcularTotal() {
        let total = 0;
        let piezas = 0;
        ticket.forEach((renglon) => {
            total += renglon.precio * renglon.cantidad;
            piezas += renglon.cantidad;
        });
        return { total: Math.round(total * 100) / 100, piezas };
    }

    function actualizarTicket() {
        listaTicket.replaceChildren(...[...ticket.values()].map((renglon) => {
            const elemento = document.createElement('li');
            elemento.className = 'renglon-ticket-pos';

            const info = document.createElement('div');
            const nombre = document.createElement('strong');
            nombre.textContent = renglon.titulo;
            const detalle = document.createElement('span');
            detalle.textContent = `Talla ${renglon.talla} · ${moneda.format(renglon.precio)} c/u`;
            info.append(nombre, detalle);

            const controles = document.createElement('div');
            controles.className = 'control-cantidad';
            const menos = document.createElement('button');
            menos.type = 'button';
            menos.textContent = '−';
            menos.setAttribute('aria-label', `Quitar uno de ${renglon.titulo}`);
            menos.addEventListener('click', () => cambiarCantidad(renglon.id, -1));
            const cantidad = document.createElement('span');
            cantidad.textContent = renglon.cantidad;
            const mas = document.createElement('button');
            mas.type = 'button';
            mas.textContent = '+';
            mas.setAttribute('aria-label', `Agregar uno de ${renglon.titulo}`);
            mas.disabled = renglon.cantidad >= renglon.stock;
            mas.addEventListener('click', () => cambiarCantidad(renglon.id, 1));
            controles.append(menos, cantidad, mas);

            const importe = document.createElement('span');
            importe.className = 'importe-renglon-pos';
            importe.textContent = moneda.format(renglon.precio * renglon.cantidad);

            elemento.append(info, controles, importe);
            return elemento;
        }));

        // Campos ocultos que viajan al servidor
        camposArticulos.replaceChildren(...[...ticket.values()].flatMap((renglon, indice) => {
            const id = document.createElement('input');
            id.type = 'hidden';
            id.name = `articulos[${indice}][inventario_id]`;
            id.value = renglon.id;
            const cantidad = document.createElement('input');
            cantidad.type = 'hidden';
            cantidad.name = `articulos[${indice}][cantidad]`;
            cantidad.value = renglon.cantidad;
            return [id, cantidad];
        }));

        const { total, piezas } = calcularTotal();
        ticketVacio.hidden = ticket.size > 0;
        totalArticulos.textContent = piezas;
        totalVenta.textContent = moneda.format(total);

        guardarTicket();
        actualizarCobro();
    }

    function metodoSeleccionado() {
        return formCobro.querySelector('input[name="metodo_pago"]:checked')?.value;
    }

    function actualizarCobro() {
        const { total } = calcularTotal();
        const esEfectivo = metodoSeleccionado() === 'efectivo';

        grupoEfectivo.hidden = !esEfectivo;
        montoRecibido.required = esEfectivo;
        montoRecibido.disabled = !esEfectivo;

        const recibido = Number(montoRecibido.value || 0);
        const cambio = recibido - total;
        cambioVenta.textContent = moneda.format(Math.max(cambio, 0));
        cambioVenta.classList.toggle('faltante', esEfectivo && montoRecibido.value !== '' && cambio < 0);

        botonCobrar.disabled = ticket.size === 0 || (esEfectivo && cambio < 0);
        botonCobrar.textContent = ticket.size === 0 ? 'Cobrar' : `Cobrar ${moneda.format(total)}`;
    }

    // ---------- Eventos ----------
    campoBusqueda.addEventListener('input', () => {
        clearTimeout(temporizador);
        temporizador = setTimeout(buscar, 250);
    });

    filtroCategoria.addEventListener('change', buscar);

    // Enter: los lectores de código de barras "teclean" el código y mandan Enter.
    formBusqueda.addEventListener('submit', async (evento) => {
        evento.preventDefault();
        clearTimeout(temporizador);

        const termino = campoBusqueda.value.trim();
        if (termino === '') return;

        const encontrados = await buscar();
        const exacto = encontrados.find((variante) => variante.codigo_barras === termino);
        const elegido = exacto ?? (encontrados.length === 1 ? encontrados[0] : null);

        if (elegido) {
            agregar(elegido);
            campoBusqueda.value = '';
            campoBusqueda.focus();
        }
    });

    formCobro.addEventListener('change', (evento) => {
        if (evento.target.name === 'metodo_pago') actualizarCobro();
    });

    montoRecibido.addEventListener('input', actualizarCobro);

    formCobro.addEventListener('submit', () => {
        botonCobrar.disabled = true;
        botonCobrar.textContent = 'Registrando…';
    });

    botonCancelar.addEventListener('click', () => {
        if (ticket.size === 0) return;

        if (botonCancelar.dataset.confirmar !== '1') {
            botonCancelar.dataset.confirmar = '1';
            botonCancelar.textContent = '¿Seguro? Toca otra vez para vaciar';
            setTimeout(() => {
                botonCancelar.dataset.confirmar = '';
                botonCancelar.textContent = 'Cancelar venta';
            }, 3000);
            return;
        }

        ticket.clear();
        montoRecibido.value = '';
        botonCancelar.dataset.confirmar = '';
        botonCancelar.textContent = 'Cancelar venta';
        actualizarTicket();
        campoBusqueda.focus();
    });

    // F2 = enfocar búsqueda, F4 = cobrar
    document.addEventListener('keydown', (evento) => {
        if (evento.key === 'F2') {
            evento.preventDefault();
            campoBusqueda.focus();
            campoBusqueda.select();
        }
        if (evento.key === 'F4' && !botonCobrar.disabled) {
            evento.preventDefault();
            formCobro.requestSubmit();
        }
    });

    // ---------- Inicio ----------
    restaurarTicket();
    actualizarTicket();
    buscar();
})();
