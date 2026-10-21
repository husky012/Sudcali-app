const DB_NAME = 'SudcaliPersonalDB';
const DB_VERSION = 2; 
let db = null;

// Inicialización de IndexedDB
function abrirDB() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open(DB_NAME, DB_VERSION);

        request.onupgradeneeded = (event) => {
            const dbInstance = event.target.result;
            let store;

            // Creación/Migración v1 -> v2
            if (!dbInstance.objectStoreNames.contains('empleados')) {
                store = dbInstance.createObjectStore('empleados', { keyPath: 'id', autoIncrement: true });
            } else {
                store = event.transaction.objectStore('empleados');
            }

            // Crear índice sobre el campo "puesto" para búsquedas reales
            if (!store.indexNames.contains('puesto')) {
                store.createIndex('puesto', 'puesto', { unique: false });
            }
        };

        request.onsuccess = (event) => {
            db = event.target.result;
            resolve(db);
        };

        request.onerror = (event) => reject('Error al abrir IndexedDB: ' + event.target.error);
    });
}

// Operación: Guardar / Actualizar Empleado (Guarda el objeto File/Blob directo)
async function guardarEmpleado(empleado) {
    const tx = db.transaction('empleados', 'readwrite');
    const store = tx.objectStore('empleados');
    if (empleado.id) {
        store.put(empleado);
    } else {
        delete empleado.id;
        store.add(empleado);
    }
    return new Promise((resolve) => tx.oncomplete = () => resolve());
}

// Operación: Listar todos
function obtenerEmpleados() {
    return new Promise((resolve) => {
        const tx = db.transaction('empleados', 'readonly');
        const store = tx.objectStore('empleados');
        const request = store.getAll();
        request.onsuccess = () => resolve(request.result);
    });
}

// Operación: Búsqueda por Índice ('puesto')
function buscarPorPuesto(puesto) {
    return new Promise((resolve) => {
        if (!puesto) {
            obtenerEmpleados().then(resolve);
            return;
        }
        const tx = db.transaction('empleados', 'readonly');
        const store = tx.objectStore('empleados');
        const index = store.index('puesto');
        const request = index.getAll(puesto);
        request.onsuccess = () => resolve(request.result);
    });
}

// Operación: Eliminar con confirmación
function eliminarEmpleado(id) {
    return new Promise((resolve) => {
        const tx = db.transaction('empleados', 'readwrite');
        const store = tx.objectStore('empleados');
        store.delete(id);
        tx.oncomplete = () => resolve();
    });
}

// Generación de Gráfico con Canvas API
async function renderizarGrafico() {
    const empleados = await obtenerEmpleados();
    const canvas = document.getElementById('graficoPuestos');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // Agrupar por Puesto
    const conteo = {};
    empleados.forEach(e => {
        conteo[e.puesto] = (conteo[e.puesto] || 0) + 1;
    });

    const puestos = Object.keys(conteo);
    const valores = Object.values(conteo);
    if (puestos.length === 0) {
        ctx.fillStyle = '#a3a3a3';
        ctx.font = '14px sans-serif';
        ctx.fillText('Sin datos registrados para graficar', 20, 100);
        return;
    }

    const colores = ['#ffffff', '#3b82f6', '#ef4444', '#10b981', '#f59e0b'];
    let barraWidth = 40;
    let gap = 20;

    puestos.forEach((puesto, i) => {
        let alto = valores[i] * 30;
        let x = 30 + i * (barraWidth + gap);
        let y = canvas.height - alto - 30;

        ctx.fillStyle = colores[i % colores.length];
        ctx.fillRect(x, y, barraWidth, alto);

        ctx.fillStyle = '#f5f5f5';
        ctx.font = '10px sans-serif';
        ctx.fillText(`${puesto} (${valores[i]})`, x - 5, canvas.height - 10);
    });
}