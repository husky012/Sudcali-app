@extends('layouts.pos')

@php($esNueva = ! $categoria->exists)

@section('titulo', $esNueva ? 'Nueva categoría' : 'Editar categoría')

@section('contenido')
<section class="seccion-pos-angosta">
    <a href="{{ route('pos.admin.categorias.index') }}" class="enlace-volver-pos">← Categorías</a>

    <header class="encabezado-pagina-pos">
        <h2>{{ $esNueva ? 'Nueva categoría' : 'Editar «'.$categoria->nombre.'»' }}</h2>
        <p>Por ejemplo: Playeras, Gorras, Buffs, Sudaderas.</p>
    </header>

    <form method="POST"
          action="{{ $esNueva ? route('pos.admin.categorias.store') : route('pos.admin.categorias.update', $categoria) }}"
          class="formulario-admin-pos bloque-formulario-pos">
        @csrf
        @unless ($esNueva)
            @method('PUT')
        @endunless

        <div class="grupo-campo">
            <label for="nombre">Nombre *</label>
            <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $categoria->nombre) }}" required maxlength="60" autofocus autocomplete="off">
            @include('pos.partials.campo-error', ['campo' => 'nombre'])
            @include('pos.partials.campo-error', ['campo' => 'slug'])
        </div>

        <div class="acciones-formulario-pos">
            <a href="{{ route('pos.admin.categorias.index') }}" class="boton-secundario-pos boton-enlace-pos">Cancelar</a>
            <button type="submit" class="boton-login">{{ $esNueva ? 'Crear categoría' : 'Guardar cambios' }}</button>
        </div>
    </form>
</section>
@endsection
