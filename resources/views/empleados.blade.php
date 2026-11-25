@extends('layouts.app')

@section('titulo', 'Sudcali - Catálogo de Personal')

@section('contenido')
<style>
    /* Variables de respaldo (por si no están definidas en tu CSS principal) */
    :root {
        --color-primario: #3b82f6;
        --color-primario-hover: #2563eb;
        --color-peligro: #ef4444;
        --color-peligro-hover: #dc2626;
    }

    .seccion-empleados {
        padding: 2rem;
        max-width: 1400px;
        margin: 0 auto;
        color: #f8fafc;
        font-family: system-ui, -apple-system, sans-serif;
    }

    .encabezado-seccion {
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--color-borde-oscuro, #334155);
    }

    .encabezado-seccion h2 {
        margin: 0 0 0.5rem 0;
        font-size: 1.75rem;
        font-weight: 600;
    }

    .encabezado-seccion p {
        margin: 0;
        color: var(--color-texto-secundario, #94a3b8);
    }

    /* Layout Principal: 1 columna en móviles, 2 en escritorio */
    .grid-empleados-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 2rem;
        align-items: start;
    }

    @media (min-width: 1024px) {
        .grid-empleados-layout {
            grid-template-columns: 350px minmax(0, 1fr);
        }
    }

    /* Tarjetas (Formulario y Gráfico) */
    .tarjeta-panel {
        background: var(--color-fondo-tarjeta, #1e293b);
        border: 1px solid var(--color-borde-oscuro, #334155);
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    .tarjeta-panel h3 {
        margin-top: 0;
        margin-bottom: 1.5rem;
        font-size: 1.25rem;
        font-weight: 500;
        border-bottom: 1px solid var(--color-borde-oscuro, #334155);
        padding-bottom: 0.75rem;
    }

    /* Estilos de Formulario */
    .grupo-campo {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        margin-bottom: 1.25rem;
    }

    .grupo-campo label {
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--color-texto-secundario, #94a3b8);
    }

    .input-estilizado {
        width: 100%;
        padding: 0.75rem 1rem;
        background: var(--color-fondo-superficie, #0f172a);
        color: #fff;
        border: 1px solid var(--color-borde-oscuro, #334155);
        border-radius: 8px;
        font-size: 0.95rem;
        transition: all 0.2s ease;
        box-sizing: border-box;
    }

    .input-estilizado:focus {
        outline: none;
        border-color: var(--color-primario);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
    }

    input[type="file"].input-estilizado {
        padding: 0.5rem;
    }

    /* Botones */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.2s ease;
        border: none;
    }

    .btn-primario {
        background: var(--color-primario);
        color: white;
        width: 100%;
        margin-top: 1rem;
    }

    .btn-primario:hover {
        background: var(--color-primario-hover);
        transform: translateY(-1px);
    }

    .btn-secundario {
        background: var(--color-fondo-superficie, #0f172a);
        color: #fff;
        border: 1px solid var(--color-borde-oscuro, #334155);
    }

    .btn-secundario:hover {
        border-color: var(--color-primario);
        color: var(--color-primario);
    }

    /* Barra de Búsqueda */
    .barra-busqueda {
        display: flex;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    /* Tabla Estilizada */
    .contenedor-tabla {
        background: var(--color-fondo-tarjeta, #1e293b);
        border: 1px solid var(--color-borde-oscuro, #334155);
        border-radius: 12px;
        overflow-x: auto;
    }

    .tabla-moderna {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .tabla-moderna th {
        background: var(--color-fondo-superficie, #0f172a);
        padding: 1rem;
        font-weight: 600;
        font-size: 0.875rem;
        color: var(--color-texto-secundario, #94a3b8);
        border-bottom: 1px solid var(--color-borde-oscuro, #334155);
    }

    .tabla-moderna td {
        padding: 1rem;
        border-bottom: 1px solid var(--color-borde-oscuro, #334155);
        vertical-align: middle;
    }

    .tabla-moderna tbody tr:hover {
        background: rgba(255, 255, 255, 0.02);
    }

    .tabla-moderna tbody tr:last-child td {
        border-bottom: none;
    }

    /* Foto de perfil en tabla */
    .foto-perfil {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--color-borde-oscuro, #334155);
    }

    /* Acciones de Tabla */
    .btn-accion {
        background: none;
        border: none;
        border-radius: 6px;
        padding: 0.4rem 0.75rem;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: background 0.2s;
    }

    .btn-editar {
        color: var(--color-primario);
    }
    
    .btn-editar:hover {
        background: rgba(59, 130, 246, 0.1);
    }

    .btn-baja {
        color: var(--color-peligro);
        margin-left: 0.25rem;
    }

    .btn-baja:hover {
        background: rgba(239, 68, 68, 0.1);
    }
</style>

<section class="seccion-empleados" id="catalogoPersonal"
         @if ($websocketUrl && $websocketToken) data-websocket-url="{{ $websocketUrl }}" data-websocket-token="{{ $websocketToken }}" @endif>
    <header class="encabezado-seccion">
        <h2>Catálogo de Personal & Vendedores</h2>
    </header>

    <div class="grid-empleados-layout">
        
        <!-- Columna Izquierda: Formulario -->
        <div class="tarjeta-panel">
            <h3 id="tituloForm">Nuevo Registro</h3>
            <form id="formEmpleado">
                <input type="hidden" id="empId">
                
                <div class="grupo-campo">
                    <label for="empNombre">Nombre Completo *</label>
                    <input type="text" id="empNombre" class="input-estilizado" required placeholder="Ej. Juan Pérez">
                </div>

                <div class="grupo-campo">
                    <label for="empEmail">Correo Electrónico *</label>
                    <input type="email" id="empEmail" class="input-estilizado" required placeholder="juan@sudcali.com">
                </div>

                <div class="grupo-campo">
                    <label for="empPuesto">Puesto / Área *</label>
                    <select id="empPuesto" class="input-estilizado" required>
                        <option value="Vendedor">Vendedor</option>
                        <option value="Almacén">Almacén</option>
                        <option value="Administración">Administración</option>
                        <option value="Gerente">Gerente</option>
                    </select>
                </div>

                <div class="grupo-campo">
                    <label for="empSucursal">Sucursal *</label>
                    <input type="text" id="empSucursal" class="input-estilizado" value="La Paz Malecón" required>
                </div>

                <div class="grupo-campo">
                    <label for="empEstatus">Estatus *</label>
                    <select id="empEstatus" class="input-estilizado" required>
                        <option value="Activo">Activo</option>
                        <option value="Inactivo">Inactivo</option>
                    </select>
                </div>

                <div class="grupo-campo">
                    <label for="empFecha">Fecha de Contratación *</label>
                    <input type="date" id="empFecha" class="input-estilizado" required>
                </div>

                <div class="grupo-campo">
                    <label for="empFoto">Fotografía (Binario) *</label>
                    <input type="file" id="empFoto" class="input-estilizado" accept="image/*">
                </div>

                <button type="submit" class="btn btn-primario">Guardar Personal</button>
            </form>
        </div>

        <!-- Columna Derecha: Búsqueda, Gráfico y Tabla -->
        <div class="contenedor-lista-grafico">
            
            <!-- Gráfico con Canvas -->
            <div class="tarjeta-panel" style="margin-bottom: 1.5rem;">
                <h3>Distribución por Puesto (Canvas API)</h3>
                <canvas id="graficoPuestos" width="400" height="120" style="width: 100%;"></canvas>
            </div>

            <!-- Búsqueda usando Índice -->
            <div class="barra-busqueda">
                <select id="filtroPuesto" class="input-estilizado" style="flex: 1;">
                    <option value="">-- Buscar por Índice (Todos los Puestos) --</option>
                    <option value="Vendedor">Vendedor</option>
                    <option value="Almacén">Almacén</option>
                    <option value="Administración">Administración</option>
                    <option value="Gerente">Gerente</option>
                </select>
                <button type="button" id="btnBuscar" class="btn btn-secundario">Filtrar</button>
            </div>

            <!-- Tabla de Empleados -->
            <div class="contenedor-tabla">
                <table class="tabla-moderna">
                    <thead>
                        <tr>
                            <th>Foto</th>
                            <th>Nombre / Correo</th>
                            <th>Puesto</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
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

<script src="{{ asset('js/empleados-db.js') }}"></script>
<script src="{{ asset('js/empleados.js') }}"></script>
@endsection