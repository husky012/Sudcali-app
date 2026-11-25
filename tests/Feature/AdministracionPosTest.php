<?php

use App\Enums\PuestoEmpleado;
use App\Models\Categoria;
use App\Models\DetalleVenta;
use App\Models\Empleado;
use App\Models\Inventario;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\Venta;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->withoutVite();
});

function empleadoConPuesto(PuestoEmpleado $puesto): Empleado
{
    return Empleado::factory()->create(['puesto' => $puesto]);
}

/**
 * @return array<string, mixed>
 */
function datosEmpleado(array $cambios = []): array
{
    return [
        'nombre' => 'Ana',
        'apellido_paterno' => 'Geraldo',
        'apellido_materno' => 'Cota',
        'puesto' => PuestoEmpleado::Vendedor->value,
        'sucursal_id' => Sucursal::factory()->create()->id,
        'fecha_contratacion' => '2026-01-15',
        'telefono' => '612 123 4567',
        'correo' => 'Ana@Sudcali.test',
        'password' => 'secreta123',
        'password_confirmation' => 'secreta123',
        'activo' => '1',
        ...$cambios,
    ];
}

// ---------- Permisos ----------

test('salespeople cannot open any administration screen', function (string $ruta) {
    $this->actingAs(empleadoConPuesto(PuestoEmpleado::Vendedor), 'empleado')
        ->get(route($ruta))
        ->assertForbidden();
})->with([
    'pos.admin.empleados.index',
    'pos.admin.categorias.index',
    'pos.admin.productos.index',
    'pos.admin.inventario.index',
]);

test('warehouse staff can manage stock but not employees or the catalog', function () {
    $this->actingAs(empleadoConPuesto(PuestoEmpleado::Almacen), 'empleado');

    $this->get(route('pos.admin.inventario.index'))->assertOk();
    $this->get(route('pos.admin.empleados.index'))->assertForbidden();
    $this->get(route('pos.admin.productos.index'))->assertForbidden();
});

test('managers can open every administration screen', function (string $ruta) {
    Inventario::factory()->create();
    Empleado::factory()->create();

    $this->actingAs(empleadoConPuesto(PuestoEmpleado::Gerente), 'empleado')
        ->get(route($ruta))
        ->assertOk();
})->with([
    'pos.admin.empleados.index',
    'pos.admin.empleados.create',
    'pos.admin.categorias.index',
    'pos.admin.categorias.create',
    'pos.admin.productos.index',
    'pos.admin.productos.create',
    'pos.admin.inventario.index',
]);

// ---------- Empleados ----------

test('a manager can register an employee who can then log in', function () {
    Storage::fake('local');

    $this->actingAs(empleadoConPuesto(PuestoEmpleado::Gerente), 'empleado')
        ->post(route('pos.admin.empleados.store'), datosEmpleado([
            'foto' => UploadedFile::fake()->create('ana.jpg', 120, 'image/jpeg'),
        ]))
        ->assertRedirect(route('pos.admin.empleados.index'));

    $empleado = Empleado::where('correo', 'ana@sudcali.test')->sole();

    expect($empleado->telefono)->toBe('6121234567')
        ->and(Hash::check('secreta123', $empleado->password))->toBeTrue();
    Storage::disk('local')->assertExists($empleado->foto);
});

test('employee photos are only served to managers and administration', function (PuestoEmpleado $puesto, int $estado) {
    Storage::fake('local');
    Storage::disk('local')->put('empleados/foto.jpg', 'contenido');
    $empleado = Empleado::factory()->create(['foto' => 'empleados/foto.jpg']);

    $this->actingAs(empleadoConPuesto($puesto), 'empleado')
        ->get($empleado->fotoUrl())
        ->assertStatus($estado);
})->with([
    'gerente' => [PuestoEmpleado::Gerente, 200],
    'administración' => [PuestoEmpleado::Administracion, 200],
    'almacén' => [PuestoEmpleado::Almacen, 403],
    'vendedor' => [PuestoEmpleado::Vendedor, 403],
]);

test('guests cannot open an employee photo', function () {
    $empleado = Empleado::factory()->create(['foto' => 'empleados/foto.jpg']);

    $this->get($empleado->fotoUrl())->assertRedirect(route('pos.login'));
});

test('a program disguised as an image is rejected', function (string $nombre, string $contenido) {
    Storage::fake('local');
    $ruta = tempnam(sys_get_temp_dir(), 'sudcali');
    file_put_contents($ruta, $contenido);

    $this->actingAs(empleadoConPuesto(PuestoEmpleado::Gerente), 'empleado')
        ->post(route('pos.admin.empleados.store'), datosEmpleado([
            'foto' => new UploadedFile($ruta, $nombre, 'image/jpeg', null, true),
        ]))
        ->assertSessionHasErrors('foto');

    unlink($ruta);
    expect(Storage::disk('local')->allFiles())->toBe([])
        ->and(Empleado::where('correo', 'ana@sudcali.test')->exists())->toBeFalse();
})->with([
    'php con nombre de imagen' => ['foto.jpg', '<?php system($_GET["c"]);'],
    'doble extensión' => ['foto.jpg.php', '<?php system($_GET["c"]);'],
    'imagen real con extensión php' => ['foto.php', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==')],
    'svg con script' => ['foto.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'],
]);

test('product images are stored outside the public folder and served by the site', function () {
    Storage::fake('local');
    $producto = Producto::factory()->create();

    $this->actingAs(empleadoConPuesto(PuestoEmpleado::Gerente), 'empleado')
        ->put(route('pos.admin.productos.update', $producto), [
            'categoria_id' => $producto->categoria_id,
            'titulo' => $producto->titulo,
            'precio' => 250,
            'activo' => '1',
            'imagen' => UploadedFile::fake()->create('../../mi foto.png', 50, 'image/png'),
        ])
        ->assertSessionHasNoErrors();

    $imagen = $producto->fresh()->imagen;

    expect($imagen)->toMatch('#^/imagenes-producto/[A-Za-z0-9]{40}\.png$#');
    Storage::disk('local')->assertExists('productos/'.basename($imagen));
    $this->get($imagen)->assertOk();
});

test('the product image address cannot reach other private files', function (string $ruta) {
    Storage::fake('local');
    Storage::disk('local')->put('empleados/foto.jpg', 'contenido');

    $this->get($ruta)->assertNotFound();
})->with([
    'subir de carpeta' => ['/imagenes-producto/..%2Fempleados%2Ffoto.jpg'],
    'nombre inventado' => ['/imagenes-producto/foto.jpg'],
    'archivo que no existe' => ['/imagenes-producto/'.str_repeat('a', 40).'.jpg'],
]);

test('employee validation messages are in spanish', function () {
    $this->actingAs(empleadoConPuesto(PuestoEmpleado::Gerente), 'empleado')
        ->post(route('pos.admin.empleados.store'), datosEmpleado(['nombre' => '', 'telefono' => '123']))
        ->assertSessionHasErrors([
            'nombre' => 'Nombre es obligatorio.',
            'telefono' => 'Teléfono debe tener 10 dígitos.',
        ]);
});

test('editing an employee without a new password keeps the current one', function () {
    $empleado = Empleado::factory()->create();

    $this->actingAs(empleadoConPuesto(PuestoEmpleado::Gerente), 'empleado')
        ->put(route('pos.admin.empleados.update', $empleado), datosEmpleado([
            'correo' => $empleado->correo,
            'password' => '',
            'password_confirmation' => '',
        ]))
        ->assertSessionHasNoErrors();

    expect(Hash::check('password', $empleado->fresh()->password))->toBeTrue()
        ->and($empleado->fresh()->nombre)->toBe('Ana');
});

test('a manager cannot deactivate or demote themselves', function () {
    $gerente = empleadoConPuesto(PuestoEmpleado::Gerente);

    $this->actingAs($gerente, 'empleado')
        ->put(route('pos.admin.empleados.update', $gerente), datosEmpleado([
            'correo' => $gerente->correo,
            'password' => '',
            'password_confirmation' => '',
            'puesto' => PuestoEmpleado::Vendedor->value,
            'activo' => '0',
        ]));

    expect($gerente->fresh())
        ->activo->toBeTrue()
        ->puesto->toBe(PuestoEmpleado::Gerente);

    $this->delete(route('pos.admin.empleados.destroy', $gerente));
    $this->assertModelExists($gerente);
});

test('an employee with sales is deactivated instead of deleted', function () {
    $venta = Venta::factory()->create();
    $vendedor = $venta->empleado;

    $this->actingAs(empleadoConPuesto(PuestoEmpleado::Gerente), 'empleado')
        ->delete(route('pos.admin.empleados.destroy', $vendedor))
        ->assertSessionHas('aviso');

    expect($vendedor->fresh()->activo)->toBeFalse();
});

test('an employee without history is deleted', function () {
    $empleado = Empleado::factory()->create();

    $this->actingAs(empleadoConPuesto(PuestoEmpleado::Gerente), 'empleado')
        ->delete(route('pos.admin.empleados.destroy', $empleado));

    $this->assertModelMissing($empleado);
});

// ---------- Categorías ----------

test('category names must be unique ignoring accents and case', function () {
    Categoria::factory()->create(['nombre' => 'Sudaderas', 'slug' => 'sudaderas']);

    $this->actingAs(empleadoConPuesto(PuestoEmpleado::Administracion), 'empleado')
        ->post(route('pos.admin.categorias.store'), ['nombre' => 'SUDADERAS'])
        ->assertSessionHasErrors('slug');
});

test('a category with products cannot be deleted', function () {
    $producto = Producto::factory()->create();

    $this->actingAs(empleadoConPuesto(PuestoEmpleado::Gerente), 'empleado')
        ->delete(route('pos.admin.categorias.destroy', $producto->categoria))
        ->assertSessionHas('aviso');

    $this->assertModelExists($producto->categoria);
});

// ---------- Productos y variantes ----------

test('creating a product generates its slug and opens it to add sizes', function () {
    $categoria = Categoria::factory()->create();

    $this->actingAs(empleadoConPuesto(PuestoEmpleado::Gerente), 'empleado')
        ->post(route('pos.admin.productos.store'), [
            'categoria_id' => $categoria->id,
            'titulo' => 'Sudadera Médano',
            'precio' => '799.50',
            'activo' => '1',
        ])
        ->assertRedirect(route('pos.admin.productos.edit', 'sudadera-medano'));

    expect(Producto::where('slug', 'sudadera-medano')->sole()->precio)->toBe('799.50');
});

test('a product that was sold is hidden instead of deleted', function () {
    $detalle = DetalleVenta::factory()->create();
    $producto = $detalle->inventario->producto;

    $this->actingAs(empleadoConPuesto(PuestoEmpleado::Gerente), 'empleado')
        ->delete(route('pos.admin.productos.destroy', $producto))
        ->assertSessionHas('aviso');

    expect($producto->fresh()->activo)->toBeFalse();
});

test('a size without barcode gets an internal one', function () {
    $producto = Producto::factory()->create();

    $this->actingAs(empleadoConPuesto(PuestoEmpleado::Gerente), 'empleado')
        ->post(route('pos.admin.productos.variantes.store', $producto), [
            'talla' => 'M',
            'color' => 'Negro',
            'stock' => 12,
        ])
        ->assertSessionHasNoErrors();

    $variante = $producto->inventario()->sole();

    expect($variante->codigo_barras)->toBe('200'.str_pad((string) $variante->id, 10, '0', STR_PAD_LEFT))
        ->and($variante->stock)->toBe(12);
});

test('a product cannot repeat the same size and color', function () {
    $variante = Inventario::factory()->create(['talla' => 'M', 'color' => 'Negro']);

    $this->actingAs(empleadoConPuesto(PuestoEmpleado::Gerente), 'empleado')
        ->post(route('pos.admin.productos.variantes.store', $variante->producto), [
            'talla' => 'M',
            'color' => 'Negro',
            'stock' => 1,
        ])
        ->assertSessionHasErrors(['talla' => 'Este producto ya tiene esa talla en ese color.']);
});

test('a variant from another product cannot be edited through this product', function () {
    $ajena = Inventario::factory()->create();
    $producto = Producto::factory()->create();

    $this->actingAs(empleadoConPuesto(PuestoEmpleado::Gerente), 'empleado')
        ->put(route('pos.admin.productos.variantes.update', [$producto, $ajena]), [
            'talla' => 'XG',
            'color' => 'Rojo',
        ])
        ->assertNotFound();
});

// ---------- Inventario ----------

test('stock adjustments add, remove and count pieces', function (string $operacion, int $cantidad, int $esperado) {
    $variante = Inventario::factory()->create(['stock' => 10]);

    $this->actingAs(empleadoConPuesto(PuestoEmpleado::Almacen), 'empleado')
        ->patch(route('pos.admin.inventario.update', $variante), compact('operacion', 'cantidad'))
        ->assertSessionHasNoErrors();

    expect($variante->fresh()->stock)->toBe($esperado);
})->with([
    'entrada' => ['entrada', 5, 15],
    'salida' => ['salida', 4, 6],
    'conteo físico' => ['conteo', 7, 7],
]);

test('stock cannot go below zero', function () {
    $variante = Inventario::factory()->create(['stock' => 2]);

    $this->actingAs(empleadoConPuesto(PuestoEmpleado::Almacen), 'empleado')
        ->patch(route('pos.admin.inventario.update', $variante), ['operacion' => 'salida', 'cantidad' => 3])
        ->assertSessionHasErrorsIn("ajuste{$variante->id}", 'cantidad');

    expect($variante->fresh()->stock)->toBe(2);
});
