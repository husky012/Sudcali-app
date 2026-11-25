/**
 * Seguimiento del pedido del cliente: consulta el estado, la ubicación y el camino cada 5 segundos.
 * Si el estado cambia (por ejemplo, a entregado) vuelve a cargar la página.
 */
(() => {
    const datos = document.getElementById('seguimientoPedido').dataset;
    const contenedor = document.getElementById('mapaPedido');
    const aviso = document.getElementById('avisoMapa');
    let mapa = null;

    function mostrar({ ubicacion, destino, ruta }) {
        if (!contenedor) return;
        contenedor.hidden = !ubicacion;

        if (!ubicacion) {
            aviso.textContent = 'Quien lleva tu pedido todavía no comparte su ubicación.';
            return;
        }

        mapa ??= new MapaSudcali(contenedor);
        mapa.ubicar(ubicacion.latitud, ubicacion.longitud, ubicacion.precision);
        mapa.trazar(destino, ruta);

        const momento = ubicacion.segundos < 15
            ? 'Ubicación en vivo.'
            : `Última ubicación hace ${ubicacion.segundos < 60 ? `${ubicacion.segundos} s` : `${Math.round(ubicacion.segundos / 60)} min`}.`;
        aviso.textContent = `${textoRuta(ruta)} ${momento}`.trim();
    }

    async function consultar() {
        try {
            const respuesta = await fetch(datos.url, { headers: { Accept: 'application/json' } });

            if (respuesta.ok) {
                const seguimiento = await respuesta.json();
                if (seguimiento.estado !== datos.estado) {
                    location.reload();
                    return;
                }
                mostrar(seguimiento);
            } else if (aviso) {
                aviso.textContent = 'No se pudo consultar la ubicación. Recarga la página.';
            }
        } catch {
            if (aviso) aviso.textContent = 'Sin conexión. Se intentará de nuevo.';
        }

        setTimeout(consultar, 5000);
    }

    consultar();
})();
