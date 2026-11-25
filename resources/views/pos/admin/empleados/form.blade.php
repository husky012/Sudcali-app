@extends('layouts.pos')

@php($esNuevo = ! $empleado->exists)
@php($esUnoMismo = $empleado->exists && $empleado->is(auth('empleado')->user()))

@section('titulo', $esNuevo ? 'Nuevo empleado' : 'Editar empleado')

@section('contenido')
<section class="seccion-pos-formulario">
    <a href="{{ route('pos.admin.empleados.index') }}" class="enlace-volver-pos">← Empleados</a>

    <header class="encabezado-pagina-pos">
        <h2>{{ $esNuevo ? 'Nuevo empleado' : $empleado->nombreCompleto() }}</h2>
        <p>{{ $esNuevo ? 'Da de alta a un integrante del equipo.' : 'Actualiza sus datos de contacto, puesto o acceso.' }}</p>
    </header>

    <form method="POST"
          action="{{ $esNuevo ? route('pos.admin.empleados.store') : route('pos.admin.empleados.update', $empleado) }}"
          enctype="multipart/form-data"
          class="formulario-admin-pos">
        @csrf
        @unless ($esNuevo)
            @method('PUT')
        @endunless

        <fieldset class="bloque-formulario-pos">
            <legend>Datos personales</legend>

            <div class="rejilla-campos-pos">
                <div class="grupo-campo">
                    <label for="nombre">Nombre(s) *</label>
                    <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $empleado->nombre) }}" required maxlength="100" autocomplete="off">
                    @include('pos.partials.campo-error', ['campo' => 'nombre'])
                </div>
                <div class="grupo-campo">
                    <label for="apellido_paterno">Apellido paterno *</label>
                    <input type="text" id="apellido_paterno" name="apellido_paterno" value="{{ old('apellido_paterno', $empleado->apellido_paterno) }}" required maxlength="100" autocomplete="off">
                    @include('pos.partials.campo-error', ['campo' => 'apellido_paterno'])
                </div>
                <div class="grupo-campo">
                    <label for="apellido_materno">Apellido materno</label>
                    <input type="text" id="apellido_materno" name="apellido_materno" value="{{ old('apellido_materno', $empleado->apellido_materno) }}" maxlength="100" autocomplete="off">
                    @include('pos.partials.campo-error', ['campo' => 'apellido_materno'])
                </div>
                <div class="grupo-campo">
                    <label for="telefono">Teléfono (10 dígitos) *</label>
                    <input type="tel" id="telefono" name="telefono" value="{{ old('telefono', $empleado->telefono) }}" required inputmode="numeric" pattern="[0-9 \-]{10,14}" placeholder="6121234567">
                    @include('pos.partials.campo-error', ['campo' => 'telefono'])
                </div>
            </div>

            <div class="grupo-campo campo-foto-pos">
                <label for="foto">Foto</label>
                <div class="vista-foto-pos">
                    <img id="vistaPreviaFoto" src="{{ $empleado->fotoUrl() }}" alt="Foto actual" @if (! $empleado->fotoUrl()) hidden @endif>
                    <input type="file" id="foto" data-vista-previa="vistaPreviaFoto" name="foto" accept="image/*">
                </div>
                <p class="ayuda-campo-pos">JPG o PNG, máximo 2 MB.</p>
                @include('pos.partials.campo-error', ['campo' => 'foto'])
            </div>
        </fieldset>

        <fieldset class="bloque-formulario-pos">
            <legend>Puesto</legend>

            <div class="rejilla-campos-pos">
                <div class="grupo-campo">
                    <label for="puesto">Puesto *</label>
                    <select id="puesto" name="puesto" required @disabled($esUnoMismo)>
                        @foreach ($puestos as $puesto)
                            <option value="{{ $puesto->value }}" @selected(old('puesto', $empleado->puesto?->value) === $puesto->value)>{{ $puesto->value }}</option>
                        @endforeach
                    </select>
                    @if ($esUnoMismo)
                        <input type="hidden" name="puesto" value="{{ $empleado->puesto->value }}">
                        <p class="ayuda-campo-pos">No puedes cambiar tu propio puesto.</p>
                    @endif
                    @include('pos.partials.campo-error', ['campo' => 'puesto'])
                </div>
                <div class="grupo-campo">
                    <label for="sucursal_id">Sucursal *</label>
                    <select id="sucursal_id" name="sucursal_id" required>
                        @foreach ($sucursales as $sucursal)
                            <option value="{{ $sucursal->id }}" @selected((int) old('sucursal_id', $empleado->sucursal_id) === $sucursal->id)>{{ $sucursal->nombre }}</option>
                        @endforeach
                    </select>
                    @include('pos.partials.campo-error', ['campo' => 'sucursal_id'])
                </div>
                <div class="grupo-campo">
                    <label for="fecha_contratacion">Fecha de contratación *</label>
                    <input type="date" id="fecha_contratacion" name="fecha_contratacion" value="{{ old('fecha_contratacion', $empleado->fecha_contratacion?->format('Y-m-d')) }}" max="{{ today()->format('Y-m-d') }}" required>
                    @include('pos.partials.campo-error', ['campo' => 'fecha_contratacion'])
                </div>
            </div>
        </fieldset>

        <fieldset class="bloque-formulario-pos">
            <legend>Acceso al punto de venta</legend>

            <div class="rejilla-campos-pos">
                <div class="grupo-campo campo-ancho-pos">
                    <label for="correo">Correo *</label>
                    <input type="email" id="correo" name="correo" value="{{ old('correo', $empleado->correo) }}" required maxlength="255" autocomplete="off">
                    @include('pos.partials.campo-error', ['campo' => 'correo'])
                </div>
                <div class="grupo-campo">
                    <label for="password">{{ $esNuevo ? 'Contraseña *' : 'Nueva contraseña' }}</label>
                    <input type="password" id="password" name="password" @required($esNuevo) minlength="8" autocomplete="new-password">
                    @unless ($esNuevo)
                        <p class="ayuda-campo-pos">Déjala vacía para conservar la actual.</p>
                    @endunless
                    @include('pos.partials.campo-error', ['campo' => 'password'])
                </div>
                <div class="grupo-campo">
                    <label for="password_confirmation">Confirmar contraseña</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" @required($esNuevo) minlength="8" autocomplete="new-password">
                </div>
            </div>

            <label class="interruptor-pos">
                <input type="hidden" name="activo" value="0">
                <input type="checkbox" name="activo" value="1" @checked(old('activo', $empleado->activo)) @disabled($esUnoMismo)>
                @if ($esUnoMismo)
                    <input type="hidden" name="activo" value="1">
                @endif
                <span>Activo (puede iniciar sesión en el POS)</span>
            </label>
        </fieldset>

        <div class="acciones-formulario-pos">
            <a href="{{ route('pos.admin.empleados.index') }}" class="boton-secundario-pos boton-enlace-pos">Cancelar</a>
            <button type="submit" class="boton-login">{{ $esNuevo ? 'Dar de alta' : 'Guardar cambios' }}</button>
        </div>
    </form>
</section>
@endsection

@push('scripts')
<script src="{{ asset('js/vista-previa.js') }}" defer></script>
@endpush
