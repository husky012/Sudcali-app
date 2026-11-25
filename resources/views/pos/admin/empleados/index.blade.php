@extends('layouts.pos')

@section('titulo', 'Empleados')

@section('contenido')
<section class="seccion-pos-ancha seccion-admin-pos">
    <header class="encabezado-admin-pos">
        <div class="encabezado-pagina-pos">
            <h2>Empleados</h2>
            <p>{{ $empleados->total() }} {{ $empleados->total() === 1 ? 'empleado' : 'empleados' }}</p>
        </div>
        <a href="{{ route('pos.admin.empleados.create') }}" class="boton-login boton-enlace-pos boton-compacto-pos">+ Nuevo empleado</a>
    </header>

    <form method="GET" class="filtros-admin-pos" role="search">
        <label class="solo-lector" for="buscar">Buscar</label>
        <input type="search" id="buscar" name="buscar" value="{{ $filtros['buscar'] ?? '' }}" placeholder="Nombre, correo o teléfono">

        <label class="solo-lector" for="puesto">Puesto</label>
        <select id="puesto" name="puesto">
            <option value="">Todos los puestos</option>
            @foreach ($puestos as $puesto)
                <option value="{{ $puesto->value }}" @selected(($filtros['puesto'] ?? '') === $puesto->value)>{{ $puesto->value }}</option>
            @endforeach
        </select>

        <label class="solo-lector" for="sucursal">Sucursal</label>
        <select id="sucursal" name="sucursal">
            <option value="">Todas las sucursales</option>
            @foreach ($sucursales as $sucursal)
                <option value="{{ $sucursal->id }}" @selected((int) ($filtros['sucursal'] ?? 0) === $sucursal->id)>{{ $sucursal->nombre }}</option>
            @endforeach
        </select>

        <label class="solo-lector" for="estado">Estado</label>
        <select id="estado" name="estado">
            <option value="">Activos e inactivos</option>
            <option value="activos" @selected(($filtros['estado'] ?? '') === 'activos')>Solo activos</option>
            <option value="inactivos" @selected(($filtros['estado'] ?? '') === 'inactivos')>Solo inactivos</option>
        </select>

        <button type="submit" class="boton-secundario-pos">Filtrar</button>
        @if (array_filter($filtros))
            <a href="{{ route('pos.admin.empleados.index') }}" class="enlace-limpiar-pos">Limpiar</a>
        @endif
    </form>

    @if ($empleados->isEmpty())
        <div class="mensaje-catalogo-vacio"><p>No hay empleados con esos filtros.</p></div>
    @else
        <div class="tabla-desplazable-pos">
            <table class="tabla-pos">
                <thead>
                    <tr>
                        <th scope="col">Empleado</th>
                        <th scope="col">Puesto</th>
                        <th scope="col">Sucursal</th>
                        <th scope="col">Contacto</th>
                        <th scope="col">Estado</th>
                        <th scope="col"><span class="solo-lector">Acciones</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($empleados as $empleado)
                        <tr @class(['fila-inactiva-pos' => ! $empleado->activo])>
                            <td>
                                <div class="celda-persona-pos">
                                    @if ($empleado->fotoUrl())
                                        <img src="{{ $empleado->fotoUrl() }}" alt="" class="avatar-pos">
                                    @else
                                        <span class="avatar-pos avatar-iniciales-pos" aria-hidden="true">{{ mb_substr($empleado->nombre, 0, 1) }}{{ mb_substr($empleado->apellido_paterno, 0, 1) }}</span>
                                    @endif
                                    <div>
                                        <strong>{{ $empleado->nombreCompleto() }}</strong>
                                        <small>Desde {{ $empleado->fecha_contratacion->format('d/m/Y') }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $empleado->puesto->value }}</td>
                            <td>{{ $empleado->sucursal->nombre }}</td>
                            <td>
                                {{ $empleado->correo }}<br>
                                <small>{{ $empleado->telefono }}</small>
                            </td>
                            <td>
                                <span @class(['insignia-pago', 'insignia-activa-pos' => $empleado->activo, 'insignia-inactiva-pos' => ! $empleado->activo])>
                                    {{ $empleado->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="acciones-tabla-pos">
                                <a href="{{ route('pos.admin.empleados.edit', $empleado) }}">Editar</a>
                                @unless ($empleado->is(auth('empleado')->user()))
                                    <form method="POST" action="{{ route('pos.admin.empleados.destroy', $empleado) }}"
                                          data-confirmar="¿Eliminar a {{ $empleado->nombreCompleto() }}? Si ya tiene ventas solo se dará de baja.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="boton-peligro-texto-pos">Eliminar</button>
                                    </form>
                                @endunless
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @include('pos.partials.paginacion', ['paginador' => $empleados])
    @endif
</section>
@endsection
