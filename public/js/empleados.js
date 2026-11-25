/**
 * Catálogo de Personal: IndexedDB, tabla, gráfica y WebSocket.
 */
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js');
}

// WebSocket autenticado: el token lo firma Laravel y caduca en 30 minutos.
// Si WEBSOCKET_URL / WEBSOCKET_SECRET no están en .env, la página funciona sin tiempo real.
const configuracionSocket = document.getElementById('catalogoPersonal').dataset;
let socket = null;

if (configuracionSocket.websocketUrl && configuracionSocket.websocketToken) {
    const direccion = new URL(configuracionSocket.websocketUrl);
    direccion.searchParams.set('token', configuracionSocket.websocketToken);
    socket = new WebSocket(direccion);
    socket.onopen = () => console.log('Conectado al servidor de WebSockets en vivo');
    socket.onmessage = (evento) => {
        if (evento.data === 'actualizar_empleados') cargarTabla();
    };
}

function avisarCambio() {
    if (socket && socket.readyState === WebSocket.OPEN) {
        socket.send('actualizar_empleados');
    }
}

document.addEventListener('DOMContentLoaded', async () => {
    await abrirDB();
    cargarTabla();

    document.getElementById('formEmpleado').addEventListener('submit', async (e) => {
        e.preventDefault();
        const fileInput = document.getElementById('empFoto');
        
        const archivoNuevo = fileInput.files[0] ?? null;

        if (archivoNuevo && (!['image/png', 'image/jpeg', 'image/webp', 'image/gif'].includes(archivoNuevo.type) || archivoNuevo.size > 2 * 1024 * 1024)) {
            alert('La foto debe ser PNG, JPG, WEBP o GIF de máximo 2 MB.');
            return;
        }

        const archivoFoto = archivoNuevo ?? fotoEnEdicion;

        const emp = {
            id: document.getElementById('empId').value ? Number(document.getElementById('empId').value) : null,
            nombre: document.getElementById('empNombre').value,
            email: document.getElementById('empEmail').value,
            puesto: document.getElementById('empPuesto').value,
            sucursal: document.getElementById('empSucursal').value,
            estatus: document.getElementById('empEstatus').value,
            fecha: document.getElementById('empFecha').value,
            foto: archivoFoto 
        };

        await guardarEmpleado(emp);
        document.getElementById('formEmpleado').reset();
        document.getElementById('empId').value = '';
        fotoEnEdicion = null;

        avisarCambio();
        cargarTabla();
    });

    document.getElementById('btnBuscar').addEventListener('click', async () => {
        const puesto = document.getElementById('filtroPuesto').value;
        const res = await buscarPorPuesto(puesto);
        renderizarFilas(res);
    });
});

async function cargarTabla() {
    const empleados = await obtenerEmpleados();
    renderizarFilas(empleados);
    if (typeof renderizarGrafico === 'function') renderizarGrafico();
}

const FOTO_PREDETERMINADA = 'data:image/svg+xml,' + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 44 44"><rect width="44" height="44" fill="#334155"/><circle cx="22" cy="17" r="8" fill="#94a3b8"/><path d="M6 40c2-9 9-13 16-13s14 4 16 13" fill="#94a3b8"/></svg>');

/**
 * Solo se aceptan fotos guardadas como archivo (Blob) o como imagen en data:.
 * Cualquier otra cadena (javascript:, https://…) se descarta.
 */
function urlSeguraDeFoto(foto) {
    if (foto instanceof Blob) {
        return foto.type.startsWith('image/') ? URL.createObjectURL(foto) : FOTO_PREDETERMINADA;
    }
    if (typeof foto === 'string' && /^data:image\/(png|jpe?g|gif|webp);base64,/i.test(foto)) {
        return foto;
    }
    return FOTO_PREDETERMINADA;
}

function crearCelda(...hijos) {
    const td = document.createElement('td');
    td.append(...hijos);
    return td;
}

function crearTexto(etiqueta, texto, estilo) {
    const elemento = document.createElement(etiqueta);
    elemento.textContent = texto ?? '';
    if (estilo) elemento.style.cssText = estilo;
    return elemento;
}

function crearBoton(texto, clase, accion) {
    const boton = crearTexto('button', texto);
    boton.type = 'button';
    boton.className = `btn-accion ${clase}`;
    boton.addEventListener('click', accion);
    return boton;
}

// Los datos vienen de IndexedDB (controlada por el navegador): se tratan como
// no confiables y se insertan solo como texto, nunca como HTML (OWASP A05).
function renderizarFilas(lista) {
    const tbody = document.getElementById('tablaEmpleados');
    tbody.replaceChildren();

    lista.forEach(e => {
        const foto = document.createElement('img');
        foto.src = urlSeguraDeFoto(e.foto);
        foto.className = 'foto-perfil';
        foto.alt = `Foto de ${e.nombre ?? ''}`;

        const tr = document.createElement('tr');
        tr.append(
            crearCelda(foto),
            crearCelda(
                crearTexto('strong', e.nombre, 'display:block; font-size:0.95rem; color:#f8fafc;'),
                crearTexto('span', e.email, 'color:var(--color-texto-secundario, #94a3b8); font-size:0.85rem;'),
            ),
            crearCelda(crearTexto('span', e.puesto, 'background:rgba(255,255,255,0.05); padding:0.25rem 0.6rem; border-radius:9999px; font-size:0.85rem; border:1px solid rgba(255,255,255,0.1);')),
            crearTexto('td', e.fecha, 'font-size:0.9rem; color:#cbd5e1;'),
            crearCelda(
                crearBoton('Editar', 'btn-editar', () => editar(e.id)),
                crearBoton('Baja', 'btn-baja', () => borrar(e.id)),
            ),
        );
        tbody.appendChild(tr);
    });
}

let fotoEnEdicion = null;

async function editar(id) {
    const empleados = await obtenerEmpleados();
    const e = empleados.find(emp => emp.id === id);
    if (!e) return;
    document.getElementById('empId').value = e.id;
    document.getElementById('empNombre').value = e.nombre;
    document.getElementById('empEmail').value = e.email;
    document.getElementById('empPuesto').value = e.puesto;
    document.getElementById('empSucursal').value = e.sucursal;
    document.getElementById('empEstatus').value = e.estatus;
    document.getElementById('empFecha').value = e.fecha;
    fotoEnEdicion = e.foto ?? null;
    document.getElementById('tituloForm').innerText = 'Editar Registro #' + e.id;
    
    // Hacer scroll suave hacia el formulario al editar
    document.querySelector('.tarjeta-panel').scrollIntoView({ behavior: 'smooth' });
}

async function borrar(id) {
    if (confirm('¿Confirmas dar de baja a este elemento del personal?')) {
        await eliminarEmpleado(id);
        avisarCambio();
        cargarTabla();
    }
}
