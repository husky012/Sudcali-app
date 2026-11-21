<?php

use App\Actions\Reportes\CalcularEstadisticas;
use App\Enums\EstadoEnvio;
use App\Enums\PuestoEmpleado;
use App\Models\DetalleVenta;
use App\Models\Empleado;
use App\Models\Inventario;
use App\Models\Producto;
use App\Models\Venta;
use Carbon\CarbonImmutable;
use Database\Seeders\CatalogoSeeder;
use Database\Seeders\SucursalEmpleadoSeeder;
use Database\Seeders\VentasDemoSeeder;

beforeEach(function () {
    $this->withoutVite();
    $this->travelTo(CarbonImmutable::parse('2026-11-20 12:00'));
});

function gerente(): Empleado
{
    return Empleado::factory()->create(['puesto' => PuestoEmpleado::Gerente]);
}

/**
 * Venta con un renglón de un producto concreto en una fecha dada.
 */
function ventaDe(Producto $producto, int $piezas, string $fecha, array $estado = []): Venta
{
    $variante = Inventario::factory()->for($producto)->create();
    $importe = (float) $producto->precio * $piezas;

    $venta = Venta::factory()->create(['subtotal' => $importe, 'total' => $importe, ...$estado]);
    DetalleVenta::factory()->for($venta)->for($variante)->create([
        'cantidad' => $piezas,
        'precio_unitario' => $producto->precio,
        'subtotal' => $importe,
    ]);
    $venta->forceFill(['created_at' => $fecha])->saveQuietly();

    return $venta;
}

test('only managers and administration can see statistics', function (PuestoEmpleado $puesto, int $estado) {
    $this->actingAs(Empleado::factory()->create(['puesto' => $puesto]), 'empleado')
        ->get(route('pos.admin.estadisticas.index'))
        ->assertStatus($estado);
})->with([
    'gerente' => [PuestoEmpleado::Gerente, 200],
    'administración' => [PuestoEmpleado::Administracion, 200],
    'almacén' => [PuestoEmpleado::Almacen, 403],
    'vendedor' => [PuestoEmpleado::Vendedor, 403],
]);

test('the dashboard renders every chart with demo data', function () {
    $this->seed([CatalogoSeeder::class, SucursalEmpleadoSeeder::class, VentasDemoSeeder::class]);

    $this->actingAs(gerente(), 'empleado')
        ->get(route('pos.admin.estadisticas.index', ['periodo' => 'ultimos_30']))
        ->assertOk()
        ->assertSee('Ventas por día')
        ->assertSee('Ventas por semana')
        ->assertSee('Ventas por mes')
        ->assertSee('Lo más vendido')
        ->assertSee('Lo menos vendido')
        ->assertSee('<svg', false);
});

test('best and worst sellers are ranked by pieces in the period', function () {
    $estrella = Producto::factory()->create(['titulo' => 'Playera Estrella', 'precio' => 250]);
    $normal = Producto::factory()->create(['titulo' => 'Gorra Normal', 'precio' => 650]);
    $sinVentas = Producto::factory()->create(['titulo' => 'Buff Olvidado']);

    ventaDe($estrella, 5, '2026-11-10 12:00');
    ventaDe($normal, 1, '2026-11-11 12:00');
    ventaDe($normal, 9, '2026-10-01 12:00'); // fuera del periodo

    $estadisticas = app(CalcularEstadisticas::class)->handle(
        CarbonImmutable::parse('2026-11-01'),
        CarbonImmutable::parse('2026-11-20'),
    );

    expect(array_column($estadisticas['masVendidos'], 'etiqueta'))->toBe(['Playera Estrella', 'Gorra Normal'])
        ->and($estadisticas['menosVendidos'][0]['etiqueta'])->toBe('Buff Olvidado')
        ->and($estadisticas['menosVendidos'][0]['valor'])->toBe(0)
        ->and($estadisticas['indicadores']['ingresos']['valor'])->toBe(1900.0)
        ->and($estadisticas['indicadores']['piezas']['valor'])->toBe(6);
});

test('cancelled online orders do not count as sales', function () {
    $producto = Producto::factory()->create(['precio' => 300]);
    ventaDe($producto, 1, '2026-11-15 12:00');
    ventaDe($producto, 2, '2026-11-15 13:00', ['origen' => 'en_linea', 'estado_envio' => EstadoEnvio::Cancelado, 'corte_caja_id' => null, 'empleado_id' => null]);

    $estadisticas = app(CalcularEstadisticas::class)->handle(
        CarbonImmutable::parse('2026-11-01'),
        CarbonImmutable::parse('2026-11-20'),
    );

    expect($estadisticas['indicadores']['ventas']['valor'])->toBe(1)
        ->and($estadisticas['indicadores']['ingresos']['valor'])->toBe(300.0);
});

test('weekly totals group monday to sunday', function () {
    $producto = Producto::factory()->create(['precio' => 100]);
    ventaDe($producto, 1, '2026-11-16 09:00'); // lunes
    ventaDe($producto, 2, '2026-11-20 18:00'); // viernes, misma semana
    ventaDe($producto, 4, '2026-11-15 18:00'); // domingo, semana anterior

    $semanas = app(CalcularEstadisticas::class)->handle(
        CarbonImmutable::parse('2026-11-01'),
        CarbonImmutable::parse('2026-11-20'),
    )['porSemana'];

    expect(end($semanas)['valor'])->toBe(300.0)
        ->and($semanas[count($semanas) - 2]['valor'])->toBe(400.0);
});

test('a custom period needs a valid range', function () {
    $this->actingAs(gerente(), 'empleado')
        ->get(route('pos.admin.estadisticas.index', [
            'periodo' => 'personalizado',
            'desde' => '2026-11-10',
            'hasta' => '2026-11-01',
        ]))
        ->assertSessionHasErrors('hasta');
});

test('sales export to a valid excel workbook with the period data', function () {
    $producto = Producto::factory()->create(['titulo' => 'Playera Exportada', 'precio' => 250]);
    ventaDe($producto, 2, '2026-11-12 12:00');

    $respuesta = $this->actingAs(gerente(), 'empleado')
        ->get(route('pos.admin.estadisticas.exportar', ['reporte' => 'ventas', 'periodo' => 'este_mes']))
        ->assertOk()
        ->assertDownload('sudcali-ventas-2026-11-01-a-2026-11-20.xlsx');

    $zip = new ZipArchive;
    expect($zip->open($respuesta->getFile()->getPathname()))->toBeTrue();

    $libro = $zip->getFromName('xl/workbook.xml');
    $articulos = $zip->getFromName('xl/worksheets/sheet2.xml');
    $zip->close();

    expect($libro)->toContain('name="Ventas"')->toContain('name="Artículos vendidos"')
        ->and($articulos)->toContain('Playera Exportada')
        ->and(simplexml_load_string($articulos))->not->toBeFalse();
});

test('catalog and employee exports download as excel', function (string $reporte) {
    Inventario::factory()->create();

    $this->actingAs(gerente(), 'empleado')
        ->get(route('pos.admin.estadisticas.exportar', $reporte))
        ->assertOk()
        ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
        ->assertDownload("sudcali-{$reporte}-2026-11-20.xlsx");
})->with(['catalogo', 'empleados']);

test('employee export never includes passwords', function () {
    $respuesta = $this->actingAs(gerente(), 'empleado')
        ->get(route('pos.admin.estadisticas.exportar', 'empleados'));

    $zip = new ZipArchive;
    $zip->open($respuesta->getFile()->getPathname());
    $hoja = $zip->getFromName('xl/worksheets/sheet1.xml');
    $zip->close();

    expect($hoja)->not->toContain('$2y$')->not->toContain('Contraseña');
});
