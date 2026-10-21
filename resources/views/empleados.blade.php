@extends('layouts.app')

@section('titulo', 'Sudcali - Catálogo de Personal')

@section('contenido')
<section class="seccion-empleados">
    <header class="encabezado-seccion">
        <h2>Catálogo de Personal & Vendedores</h2>
        <p>Gestión local con IndexedDB (Operable sin conexión a internet)</p>
    </header>

    <div class="grid-empleados-layout">
        <!-- Formulario (Alta / Edición) -->
        <div class="tarjeta-formulario-emp">
            <h3 id="tituloForm">Nuevo Registro</h3>
            <form id="formEmpleado" class="formulario-login">
                <input type="hidden" id="empId">
                
                <div class="grupo-campo">
                    <label for="empNombre">Nombre Completo *</label>
                    <input type="text" id="empNombre" required>
                </div>

                <div class="grupo-campo">
                    <label for="empEmail">Correo Electrónico *</label>
                    <input type="email" id="empEmail" required>
                </div>

                <div class="grupo-campo">
                    <label for="empPuesto">Puesto / Área *</label>
                    <select id="empPuesto" style="padding:0.75rem; background:var(--color-fondo-superficie); color:#fff; border:1px solid var(--color-borde-oscuro); border-radius:6px;" required>
                        <option value="Vendedor">Vendedor</option>
                        <option value="Almacén">Almacén</option>
                        <option value="Administración">Administración</option>
                        <option value="Gerente">Gerente</option>
                    </select>
                </div>

                <div class="grupo-campo">
                    <label for="empSucursal">Sucursal *</label>
                    <input type="text" id="empSucursal" value="La Paz Malecón" required>
                </div>

                <div class="grupo-campo">
                    <label for="empEstatus">Estatus *</label>
                    <select id="empEstatus" style="padding:0.75rem; background:var(--color-fondo-superficie); color:#fff; border:1px solid var(--color-borde-oscuro); border-radius:6px;" required>
                        <option value="Activo">Activo</option>
                        <option value="Inactivo">Inactivo</option>
                    </select>
                </div>

                <div class="grupo-campo">
                    <label for="empFecha">Fecha de Contratación *</label>
                    <input type="date" id="empFecha" required>
                </div>

                <div class="grupo-campo">
                    <label for="empFoto">Fotografía (Binario) *</label>
                    <input type="file" id="empFoto" accept="image/*">
                </div>

                <button type="submit" class="boton-login" style="margin-top:1rem;">Guardar Personal</button>
            </form>
        </div>

        <!-- Búsqueda, Tabla y Gráfico -->
        <div class="contenedor-lista-grafico">
            <!-- Gráfico con Canvas -->
            <div class="tarjeta-grafico" style="background:var(--color-fondo-tarjeta); border:1px solid var(--color-borde-oscuro); padding:1rem; border-radius:12px; margin-bottom:1.5rem;">
                <h3>Distribución por Puesto (Canvas API)</h3>
                <canvas id="graficoPuestos" width="400" height="180"></canvas>
            </div>

            <!-- Búsqueda usando Índice -->
            <div class="barra-busqueda-indice" style="display:flex; gap:1rem; margin-bottom:1rem;">
                <select id="filtroPuesto" style="padding:0.5rem; background:var(--color-fondo-superficie); color:#fff; border:1px solid var(--color-borde-oscuro); border-radius:6px; flex:1;">
                    <option value="">-- Buscar por Índice (Todos los Puestos) --</option>
                    <option value="Vendedor">Vendedor</option>
                    <option value="Almacén">Almacén</option>
                    <option value="Administración">Administración</option>
                    <option value="Gerente">Gerente</option>
                </select>
                <button type="button" id="btnBuscar" class="boton-secundario">Filtrar</button>
            </div>

            <!-- Tabla de Empleados -->
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; background:var(--color-fondo-tarjeta); border-radius:12px; overflow:hidden;">
                    <thead>
                        <tr style="background:var(--color-fondo-superficie); text-align:left;">
                            <th style="padding:0.75rem;">Foto</th>
                            <th style="padding:0.75rem;">Nombre / Correo</th>
                            <th style="padding:0.75rem;">Puesto</th>
                            <th style="padding:0.75rem;">Fecha</th>
                            <th style="padding:0.75rem;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaEmpleados">
                        <!-- Carga dinámica -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<script src="/js/empleados-db.js"></script>
<script>
    // Registrar Service Worker para funcionamiento sin red
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js');
    }

    document.addEventListener('DOMContentLoaded', async () => {
        await abrirDB();
        cargarTabla();

        // Formulario Submit
        document.getElementById('formEmpleado').addEventListener('submit', async (e) => {
            e.preventDefault();
            const fileInput = document.getElementById('empFoto');
            
            // Pasa directamente el objeto File/Blob nativo sin convertir a Base64
            let archivoFoto = fileInput.files.length > 0 
                ? fileInput.files[0] 
                : (document.getElementById('empFoto').dataset.actual || null);

            const emp = {
                id: document.getElementById('empId').value ? Number(document.getElementById('empId').value) : null,
                nombre: document.getElementById('empNombre').value,
                email: document.getElementById('empEmail').value,
                puesto: document.getElementById('empPuesto').value,
                sucursal: document.getElementById('empSucursal').value,
                estatus: document.getElementById('empEstatus').value,
                fecha: document.getElementById('empFecha').value,
                foto: archivoFoto // Guardamos el objeto File o Blob directamente
            };

            await guardarEmpleado(emp);
            document.getElementById('formEmpleado').reset();
            document.getElementById('empId').value = '';
            
            // Notificar al socket si está disponible
            if (typeof socket !== 'undefined' && socket.readyState === WebSocket.OPEN) {
                socket.send('actualizar_empleados');
            }

            cargarTabla();
        });

        // Búsqueda por índice
        document.getElementById('btnBuscar').addEventListener('click', async () => {
            const puesto = document.getElementById('filtroPuesto').value;
            const res = await buscarPorPuesto(puesto);
            renderizarFilas(res);
        });
    });

    async function cargarTabla() {
        const empleados = await obtenerEmpleados();
        renderizarFilas(empleados);
        renderizarGrafico();
    }

    function renderizarFilas(lista) {
        const tbody = document.getElementById('tablaEmpleados');
        tbody.innerHTML = '';
        lista.forEach(e => {
            let urlFoto = 'https://via.placeholder.com/40';

            // Soporta objetos nativos File/Blob creando URLs dinámicas en memoria
            if (e.foto) {
                if (e.foto instanceof Blob || e.foto instanceof File) {
                    urlFoto = URL.createObjectURL(e.foto);
                } else if (typeof e.foto === 'string') {
                    urlFoto = e.foto;
                }
            }

            const tr = document.createElement('tr');
            tr.style.borderBottom = '1px solid var(--color-borde-oscuro)';
            tr.innerHTML = `
                <td style="padding:0.75rem;">
                    <img src="${urlFoto}" style="width:40px; height:40px; border-radius:50%; object-fit:cover;">
                </td>
                <td style="padding:0.75rem;"><strong>${e.nombre}</strong><br><small style="color:var(--color-texto-secundario);">${e.email}</small></td>
                <td style="padding:0.75rem;">${e.puesto}</td>
                <td style="padding:0.75rem;">${e.fecha}</td>
                <td style="padding:0.75rem;">
                    <button onclick="editar(${e.id})" style="background:none; border:none; color:#3b82f6; cursor:pointer;">Editar</button>
                    <button onclick="borrar(${e.id})" style="background:none; border:none; color:#ef4444; cursor:pointer; margin-left:0.5rem;">Baja</button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

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
        document.getElementById('empFoto').dataset.actual = e.foto;
        document.getElementById('tituloForm').innerText = 'Editar Registro #' + e.id;
    }

    async function borrar(id) {
        if (confirm('¿Confirmas dar de baja a este elemento del personal?')) {
            await eliminarEmpleado(id);
            if (typeof socket !== 'undefined' && socket.readyState === WebSocket.OPEN) {
                socket.send('actualizar_empleados');
            }
            cargarTabla();
        }
    }
</script>
@endsection