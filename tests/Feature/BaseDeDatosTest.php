<?php

use App\Enums\EstadoEnvio;
use App\Enums\OrigenVenta;
use App\Models\DetalleVenta;
use App\Models\Empleado;
use App\Models\Inventario;
use App\Models\Producto;
use App\Models\Venta;
use Database\Seeders\CatalogoSeeder;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\QueryException;

test('the seeder loads every catalog image as a product with stock', function () {
    $this->seed(DatabaseSeeder::class);

    $totalImagenes = collect(['camisas', 'gorras', 'buff'])
        ->sum(fn (string $carpeta) => count(glob(public_path("imagenes/{$carpeta}/*.jpeg"))));

    expect(Producto::count())->toBe($totalImagenes)
        ->and(Producto::doesntHave('inventario')->exists())->toBeFalse()
        ->and(Empleado::where('correo', 'gerente@sudcali.test')->exists())->toBeTrue();
});

test('the seeder can run twice without duplicating the catalog', function () {
    $this->seed(DatabaseSeeder::class);
    $productos = Producto::count();
    $variantes = Inventario::count();

    $this->seed(CatalogoSeeder::class);

    expect(Producto::count())->toBe($productos)
        ->and(Inventario::count())->toBe($variantes);
});

test('a product cannot have the same size and color twice', function () {
    $variante = Inventario::factory()->create(['talla' => 'M', 'color' => 'Negro']);

    Inventario::factory()->for($variante->producto)->create(['talla' => 'M', 'color' => 'Negro']);
})->throws(QueryException::class);

test('a variant that was already sold cannot be deleted', function () {
    $detalle = DetalleVenta::factory()->create();

    $detalle->inventario->delete();
})->throws(QueryException::class);

test('deleting a sale removes its line items', function () {
    $detalle = DetalleVenta::factory()->create();

    $detalle->venta->delete();

    $this->assertModelMissing($detalle);
});

test('an online sale belongs to the customer who owns the shipping address', function () {
    $venta = Venta::factory()->enLinea()->create();

    expect($venta->origen)->toBe(OrigenVenta::EnLinea)
        ->and($venta->estado_envio)->toBe(EstadoEnvio::Pendiente)
        ->and($venta->cliente_id)->toBe($venta->direccionEnvio->cliente_id)
        ->and($venta->empleado_id)->toBeNull()
        ->and($venta->total)->toBe(number_format($venta->subtotal + $venta->costo_envio, 2, '.', ''));
});
