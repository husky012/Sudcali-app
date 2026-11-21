@extends('layouts.pos')

@php($esNuevo = ! $producto->exists)

@section('titulo', $esNuevo ? 'Nuevo producto' : 'Editar producto')

@section('contenido')
<section class="seccion-pos-formulario">
    <a href="{{ route('pos.admin.productos.index') }}" class="enlace-volver-pos">← Productos</a>

    <header class="encabezado-pagina-pos">
        <h2>{{ $esNuevo ? 'Nuevo producto' : $producto->titulo }}</h2>
        <p>{{ $esNuevo ? 'Después de guardarlo podrás agregar sus tallas y colores.' : 'Datos generales, tallas y códigos de barras.' }}</p>
    </header>

    @if ($categorias->isEmpty())
        <p class="alerta-pos alerta-aviso">
            Primero <a href="{{ route('pos.admin.categorias.create') }}">crea una categoría</a> para poder registrar productos.
        </p>
    @endif

    <form method="POST"
          action="{{ $esNuevo ? route('pos.admin.productos.store') : route('pos.admin.productos.update', $producto) }}"
          enctype="multipart/form-data"
          class="formulario-admin-pos">
        @csrf
        @unless ($esNuevo)
            @method('PUT')
        @endunless

        <fieldset class="bloque-formulario-pos">
            <legend>Datos generales</legend>

            <div class="rejilla-producto-pos">
                <div class="grupo-campo campo-foto-pos">
                    <span class="etiqueta-campo-pos">Imagen</span>
                    <img id="vistaPreviaImagen" class="vista-imagen-producto-pos" src="{{ $producto->imagen }}" alt="Imagen del producto" @if (! $producto->imagen) hidden @endif>
                    <label for="imagen" class="solo-lector">Subir imagen</label>
                    <input type="file" id="imagen" name="imagen" accept="image/*">
                    <p class="ayuda-campo-pos">JPG o PNG, máximo 4 MB.</p>
                    @include('pos.partials.campo-error', ['campo' => 'imagen'])
                </div>

                <div class="rejilla-campos-pos">
                    <div class="grupo-campo campo-ancho-pos">
                        <label for="titulo">Nombre del producto *</label>
                        <input type="text" id="titulo" name="titulo" value="{{ old('titulo', $producto->titulo) }}" required maxlength="120" autocomplete="off">
                        @include('pos.partials.campo-error', ['campo' => 'titulo'])
                        @include('pos.partials.campo-error', ['campo' => 'slug'])
                    </div>
                    <div class="grupo-campo">
                        <label for="categoria_id">Categoría *</label>
                        <select id="categoria_id" name="categoria_id" required>
                            <option value="">Elige una…</option>
                            @foreach ($categorias as $categoria)
                                <option value="{{ $categoria->id }}" @selected((int) old('categoria_id', $producto->categoria_id) === $categoria->id)>{{ $categoria->nombre }}</option>
                            @endforeach
                        </select>
                        @include('pos.partials.campo-error', ['campo' => 'categoria_id'])
                    </div>
                    <div class="grupo-campo">
                        <label for="precio">Precio (MXN) *</label>
                        <input type="number" id="precio" name="precio" class="campo-numero-pos" value="{{ old('precio', $producto->precio) }}" required min="1" step="0.01" inputmode="decimal">
                        @include('pos.partials.campo-error', ['campo' => 'precio'])
                    </div>
                    <div class="grupo-campo campo-ancho-pos">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion" rows="3" maxlength="2000">{{ old('descripcion', $producto->descripcion) }}</textarea>
                        @include('pos.partials.campo-error', ['campo' => 'descripcion'])
                    </div>
                </div>
            </div>

            <label class="interruptor-pos">
                <input type="hidden" name="activo" value="0">
                <input type="checkbox" name="activo" value="1" @checked(old('activo', $producto->activo))>
                <span>Visible en la tienda y en el punto de venta</span>
            </label>
        </fieldset>

        <div class="acciones-formulario-pos">
            <a href="{{ route('pos.admin.productos.index') }}" class="boton-secundario-pos boton-enlace-pos">Cancelar</a>
            <button type="submit" class="boton-login">{{ $esNuevo ? 'Crear y agregar tallas' : 'Guardar cambios' }}</button>
        </div>
    </form>

    @unless ($esNuevo)
        <section class="bloque-formulario-pos" aria-labelledby="tituloVariantes">
            <div class="encabezado-bloque-pos">
                <h3 id="tituloVariantes">Tallas y colores</h3>
                <p>Cada combinación tiene su propio código de barras y existencias. Las existencias se ajustan en
                    <a href="{{ route('pos.admin.inventario.index', ['buscar' => $producto->titulo]) }}">Inventario</a>.</p>
            </div>

            @if ($producto->inventario->isNotEmpty())
                <div class="tabla-desplazable-pos">
                    <table class="tabla-pos">
                        <thead>
                            <tr>
                                <th scope="col">Talla</th>
                                <th scope="col">Color</th>
                                <th scope="col">Código de barras</th>
                                <th scope="col" class="numero">Existencias</th>
                                <th scope="col"><span class="solo-lector">Acciones</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($producto->inventario as $variante)
                                <tr>
                                    <td colspan="3">
                                        <form id="formVariante{{ $variante->id }}" method="POST"
                                              action="{{ route('pos.admin.productos.variantes.update', [$producto, $variante]) }}"
                                              class="fila-edicion-variante-pos">
                                            @csrf
                                            @method('PUT')
                                            <label class="solo-lector" for="talla{{ $variante->id }}">Talla</label>
                                            <input type="text" id="talla{{ $variante->id }}" name="talla" value="{{ $variante->talla }}" required maxlength="20">
                                            <label class="solo-lector" for="color{{ $variante->id }}">Color</label>
                                            <input type="text" id="color{{ $variante->id }}" name="color" value="{{ $variante->color }}" required maxlength="50">
                                            <label class="solo-lector" for="codigo{{ $variante->id }}">Código de barras</label>
                                            <input type="text" id="codigo{{ $variante->id }}" name="codigo_barras" value="{{ $variante->codigo_barras }}" maxlength="50">
                                            <button type="submit" class="boton-secundario-pos">Guardar</button>
                                        </form>
                                    </td>
                                    <td class="numero">{{ $variante->stock }}</td>
                                    <td class="acciones-tabla-pos">
                                        @if ($variante->detalles_venta_count === 0)
                                            <form method="POST" action="{{ route('pos.admin.productos.variantes.destroy', [$producto, $variante]) }}"
                                                  onsubmit="return confirm('¿Eliminar la talla {{ addslashes($variante->talla) }} ({{ addslashes($variante->color) }})?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="boton-peligro-texto-pos">Eliminar</button>
                                            </form>
                                        @else
                                            <span class="texto-deshabilitado-pos" title="Ya tiene ventas">Con ventas</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="ticket-vacio-pos">Este producto aún no tiene tallas. Agrega la primera abajo.</p>
            @endif

            @if ($errors->hasAny(['talla', 'color', 'codigo_barras', 'stock']))
                <div class="alerta-pos alerta-error" role="alert">
                    <ul>
                        @foreach ($errors->only(['talla', 'color', 'codigo_barras', 'stock']) as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('pos.admin.productos.variantes.store', $producto) }}" class="formulario-nueva-variante-pos">
                @csrf
                <div class="grupo-campo">
                    <label for="nuevaTalla">Talla *</label>
                    <input type="text" id="nuevaTalla" name="talla" list="tallasComunes" required maxlength="20" placeholder="CH, M, G, XG, Única">
                    <datalist id="tallasComunes">
                        @foreach (['CH', 'M', 'G', 'XG', 'XXG', 'Única'] as $talla)
                            <option value="{{ $talla }}">
                        @endforeach
                    </datalist>
                </div>
                <div class="grupo-campo">
                    <label for="nuevoColor">Color *</label>
                    <input type="text" id="nuevoColor" name="color" value="Único" required maxlength="50">
                </div>
                <div class="grupo-campo">
                    <label for="nuevoCodigo">Código de barras</label>
                    <input type="text" id="nuevoCodigo" name="codigo_barras" maxlength="50" placeholder="Se genera si lo dejas vacío">
                </div>
                <div class="grupo-campo">
                    <label for="nuevoStock">Existencias iniciales *</label>
                    <input type="number" id="nuevoStock" name="stock" value="0" required min="0" step="1">
                </div>
                <button type="submit" class="boton-login">Agregar talla</button>
            </form>
        </section>
    @endunless
</section>
@endsection

@push('scripts')
<script>
    (() => {
        const entrada = document.getElementById('imagen');
        const vista = document.getElementById('vistaPreviaImagen');

        entrada.addEventListener('change', () => {
            const [archivo] = entrada.files;
            if (!archivo) return;
            vista.src = URL.createObjectURL(archivo);
            vista.hidden = false;
        });
    })();
</script>
@endpush
