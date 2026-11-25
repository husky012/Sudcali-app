<?php

use App\Enums\EstadoEnvio;
use App\Enums\PuestoEmpleado;
use App\Models\DireccionCliente;
use App\Models\Empleado;
use App\Models\Entrega;
use App\Models\Venta;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->withoutVite();
    Storage::fake('local');
    Http::preventStrayRequests();
});

function repartidor(PuestoEmpleado $puesto = PuestoEmpleado::Almacen): Empleado
{
    return Empleado::factory()->create(['puesto' => $puesto]);
}

function pedidoEnCamino(): Venta
{
    return Venta::factory()->enLinea()->create(['estado_envio' => EstadoEnvio::Enviado]);
}

// ---------- Ubicación ----------

test('the customer sees the latest location of the order on its way', function () {
    $pedido = pedidoEnCamino();
    $empleado = repartidor();

    foreach ([[24.10, -110.30], [24.142233, -110.312211]] as [$latitud, $longitud]) {
        $this->actingAs($empleado, 'empleado')
            ->putJson(route('pos.admin.pedidos.ubicacion', $pedido), ['latitud' => $latitud, 'longitud' => $longitud, 'precision' => 12.7])
            ->assertOk();
    }

    expect(Entrega::count())->toBe(1);

    $this->actingAs($pedido->cliente, 'cliente')
        ->getJson(route('pedidos.seguimiento', $pedido))
        ->assertOk()
        ->assertJson([
            'estado' => 'enviado',
            'ubicacion' => ['latitud' => 24.142233, 'longitud' => -110.312211, 'precision' => 12],
        ]);
});

test('an old location is not shown', function () {
    $entrega = Entrega::factory()->create(['ubicacion_en' => now()->subMinutes(Entrega::MINUTOS_VIGENCIA + 1)]);

    $this->actingAs($entrega->venta->cliente, 'cliente')
        ->getJson(route('pedidos.seguimiento', $entrega->venta))
        ->assertOk()
        ->assertJsonPath('ubicacion', null);
});

test('a customer cannot follow the order of someone else', function (string $ruta) {
    $entrega = Entrega::factory()->entregada()->create();
    Storage::disk('local')->put($entrega->foto, 'contenido');
    $otro = pedidoEnCamino()->cliente;

    $this->actingAs($otro, 'cliente')
        ->get(route($ruta, $entrega->venta))
        ->assertNotFound();
})->with(['pedidos.seguimiento', 'pedidos.foto']);

test('a salesperson cannot use the delivery screen', function () {
    $pedido = pedidoEnCamino();
    $this->actingAs(repartidor(PuestoEmpleado::Vendedor), 'empleado');

    $this->get(route('pos.admin.pedidos.reparto', $pedido))->assertForbidden();
    $this->putJson(route('pos.admin.pedidos.ubicacion', $pedido), ['latitud' => 24.1, 'longitud' => -110.3])->assertForbidden();
    $this->deleteJson(route('pos.admin.pedidos.ubicacion.olvidar', $pedido))->assertForbidden();
    $this->post(route('pos.admin.pedidos.entrega', $pedido), ['foto' => UploadedFile::fake()->create('foto.jpg', 50, 'image/jpeg')])->assertForbidden();
    $this->get(route('pos.admin.pedidos.foto', $pedido))->assertForbidden();

    expect(Entrega::count())->toBe(0);
});

test('the location is only accepted while the order is on its way', function (EstadoEnvio $estado) {
    $pedido = Venta::factory()->enLinea()->create(['estado_envio' => $estado]);

    $this->actingAs(repartidor(), 'empleado')
        ->putJson(route('pos.admin.pedidos.ubicacion', $pedido), ['latitud' => 24.1, 'longitud' => -110.3])
        ->assertConflict();

    expect(Entrega::count())->toBe(0);
})->with([EstadoEnvio::Preparando, EstadoEnvio::Entregado, EstadoEnvio::Cancelado]);

test('an impossible location is rejected', function (array $datos, string $campo) {
    $this->actingAs(repartidor(), 'empleado')
        ->putJson(route('pos.admin.pedidos.ubicacion', pedidoEnCamino()), $datos)
        ->assertUnprocessable()
        ->assertJsonValidationErrorFor($campo);
})->with([
    'latitud fuera de rango' => [['latitud' => 95, 'longitud' => -110.3], 'latitud'],
    'longitud fuera de rango' => [['latitud' => 24.1, 'longitud' => -200], 'longitud'],
    'texto' => [['latitud' => '<script>', 'longitud' => -110.3], 'latitud'],
    'sin longitud' => [['latitud' => 24.1], 'longitud'],
]);

test('the courier can stop sharing the location', function () {
    $entrega = Entrega::factory()->create();

    $this->actingAs(repartidor(), 'empleado')
        ->deleteJson(route('pos.admin.pedidos.ubicacion.olvidar', $entrega->venta))
        ->assertNoContent();

    expect($entrega->fresh()->latitud)->toBeNull()
        ->and($entrega->fresh()->longitud)->toBeNull();
});

// ---------- Camino al domicilio ----------

function pedidoConDomicilio(): Venta
{
    $direccion = DireccionCliente::factory()->ubicada()->create();

    return Venta::factory()->enLinea()->create(['direccion_envio_id' => $direccion->id, 'estado_envio' => EstadoEnvio::Enviado]);
}

/**
 * @return array<string, mixed>
 */
function respuestaDeRutas(): array
{
    return ['code' => 'Ok', 'routes' => [[
        'distance' => 2284.4,
        'duration' => 249.2,
        'geometry' => ['coordinates' => [[-110.312237, 24.142233], [-110.31, 24.135], [-110.3085, 24.1265]]],
    ]]];
}

test('the route to the address is drawn for the courier and the customer', function () {
    Http::fake(['rutas.test/*' => Http::response(respuestaDeRutas())]);
    $pedido = pedidoConDomicilio();
    $camino = [
        'puntos' => [[24.14223, -110.31224], [24.135, -110.31], [24.1265, -110.3085]],
        'metros' => 2284,
        'segundos' => 249,
        'aproximada' => false,
    ];

    $this->actingAs(repartidor(), 'empleado')
        ->putJson(route('pos.admin.pedidos.ubicacion', $pedido), ['latitud' => 24.142233, 'longitud' => -110.312211])
        ->assertOk()
        ->assertExactJson(['destino' => [24.1265, -110.3085], 'ruta' => $camino]);

    $this->actingAs($pedido->cliente, 'cliente')
        ->getJson(route('pedidos.seguimiento', $pedido))
        ->assertOk()
        ->assertJson(['destino' => [24.1265, -110.3085], 'ruta' => $camino]);

    // La segunda consulta usa la ruta guardada, y solo salen las coordenadas.
    Http::assertSentCount(1);
    Http::assertSent(fn ($peticion) => str_starts_with($peticion->url(), 'https://rutas.test/route/v1/driving/-110.312211,24.142233;-110.308500,24.126500?')
        && $peticion->body() === '');
});

test('the route is requested again only after moving and waiting', function () {
    Http::fake(['rutas.test/*' => Http::response(respuestaDeRutas())]);
    $pedido = pedidoConDomicilio();
    $this->actingAs(repartidor(), 'empleado');
    $avisar = fn (float $latitud) => $this->putJson(route('pos.admin.pedidos.ubicacion', $pedido), ['latitud' => $latitud, 'longitud' => -110.312211])->assertOk();

    $avisar(24.142233);
    $avisar(24.135000);
    Http::assertSentCount(1);

    $this->travel(20)->seconds();
    $avisar(24.142233);
    Http::assertSentCount(1);

    $avisar(24.135000);
    Http::assertSentCount(2);
});

test('without the routing service a straight line is drawn', function (Closure $respuesta) {
    Http::fake(['rutas.test/*' => $respuesta]);
    $pedido = pedidoConDomicilio();

    $this->actingAs(repartidor(), 'empleado')
        ->putJson(route('pos.admin.pedidos.ubicacion', $pedido), ['latitud' => 24.142233, 'longitud' => -110.312211])
        ->assertOk()
        ->assertJsonPath('ruta.aproximada', true)
        ->assertJsonPath('ruta.puntos', [[24.142233, -110.312211], [24.1265, -110.3085]])
        ->assertJsonPath('ruta.metros', 1790);
})->with([
    'sin conexión' => [fn () => fn () => throw new ConnectionException('Sin conexión')],
    'error del servicio' => [fn () => fn () => Http::response('', 503)],
    'respuesta sin ruta' => [fn () => fn () => Http::response(['code' => 'NoRoute', 'routes' => []])],
    'coordenadas inválidas' => [fn () => fn () => Http::response(['routes' => [['distance' => 1, 'duration' => 1, 'geometry' => ['coordinates' => [['a', 'b'], [1, 2]]]]]])],
]);

test('there is no route until the address is marked on the map', function () {
    $entrega = Entrega::factory()->create();

    $this->actingAs($entrega->venta->cliente, 'cliente')
        ->getJson(route('pedidos.seguimiento', $entrega->venta))
        ->assertOk()
        ->assertJsonPath('destino', null)
        ->assertJsonPath('ruta', null);

    Http::assertNothingSent();
});

test('delivering also forgets the route', function () {
    Http::fake(['rutas.test/*' => Http::response(respuestaDeRutas())]);
    $pedido = pedidoConDomicilio();
    $this->actingAs(repartidor(), 'empleado')
        ->putJson(route('pos.admin.pedidos.ubicacion', $pedido), ['latitud' => 24.142233, 'longitud' => -110.312211])
        ->assertOk();
    expect(Cache::has("ruta-pedido:{$pedido->id}"))->toBeTrue();

    $this->post(route('pos.admin.pedidos.entrega', $pedido), ['foto' => UploadedFile::fake()->create('foto.jpg', 50, 'image/jpeg')])
        ->assertSessionHasNoErrors();

    expect(Cache::has("ruta-pedido:{$pedido->id}"))->toBeFalse();
});

test('the customer marks the address and cannot mark the one of someone else', function () {
    $pedido = pedidoEnCamino();
    $ajeno = pedidoEnCamino();
    $punto = ['latitud' => 24.1265, 'longitud' => -110.3085];
    $this->actingAs($pedido->cliente, 'cliente');

    $this->putJson(route('pedidos.domicilio', $pedido), $punto)->assertNoContent();
    $this->putJson(route('pedidos.domicilio', $ajeno), $punto)->assertNotFound();

    expect($pedido->direccionEnvio->fresh()->coordenadas())->toBe([24.1265, -110.3085])
        ->and($ajeno->direccionEnvio->fresh()->coordenadas())->toBeNull();
});

test('an address outside Baja California Sur is rejected', function (float $latitud, float $longitud) {
    $pedido = pedidoEnCamino();

    $this->actingAs($pedido->cliente, 'cliente')
        ->putJson(route('pedidos.domicilio', $pedido), ['latitud' => $latitud, 'longitud' => $longitud])
        ->assertUnprocessable();

    expect($pedido->direccionEnvio->fresh()->coordenadas())->toBeNull();
})->with(['Ciudad de México' => [19.43, -99.13], 'otro continente' => [24.14, 110.31]]);

test('the address of a delivered order cannot be moved', function () {
    $pedido = Venta::factory()->enLinea()->create(['estado_envio' => EstadoEnvio::Entregado]);

    $this->actingAs($pedido->cliente, 'cliente')
        ->putJson(route('pedidos.domicilio', $pedido), ['latitud' => 24.1265, 'longitud' => -110.3085])
        ->assertConflict();
});

test('the courier marks the address only when the customer has not', function () {
    $sinMarcar = pedidoEnCamino();
    $marcado = pedidoConDomicilio();
    $punto = ['latitud' => 24.15, 'longitud' => -110.31];
    $this->actingAs(repartidor(), 'empleado');

    $this->putJson(route('pos.admin.pedidos.domicilio', $sinMarcar), $punto)->assertNoContent();
    $this->putJson(route('pos.admin.pedidos.domicilio', $marcado), $punto)->assertConflict();

    expect($sinMarcar->direccionEnvio->fresh()->coordenadas())->toBe([24.15, -110.31])
        ->and($marcado->direccionEnvio->fresh()->coordenadas())->toBe([24.1265, -110.3085]);

    $this->actingAs(repartidor(PuestoEmpleado::Vendedor), 'empleado')
        ->putJson(route('pos.admin.pedidos.domicilio', $sinMarcar), $punto)
        ->assertForbidden();
});

// ---------- Foto de la entrega ----------

test('delivering with a photo finishes the order and forgets the location', function () {
    $entrega = Entrega::factory()->create();
    $pedido = $entrega->venta;
    $empleado = repartidor();

    $this->actingAs($empleado, 'empleado')
        ->post(route('pos.admin.pedidos.entrega', $pedido), ['foto' => UploadedFile::fake()->create('../../casa.jpg', 300, 'image/jpeg')])
        ->assertRedirect(route('pos.admin.pedidos.index'))
        ->assertSessionHasNoErrors();

    $entrega->refresh();
    expect($pedido->fresh()->estado_envio)->toBe(EstadoEnvio::Entregado)
        ->and($entrega->empleado_id)->toBe($empleado->id)
        ->and($entrega->entregado_en)->not->toBeNull()
        ->and($entrega->latitud)->toBeNull()
        ->and($entrega->foto)->toMatch('/^entregas\/[A-Za-z0-9]{40}\.jpg$/');
    Storage::disk('local')->assertExists($entrega->foto);

    $this->get(route('pos.admin.pedidos.foto', $pedido))->assertOk();
    $this->get(route('pos.admin.pedidos.index'))->assertSee('Ver foto de entrega');

    $this->actingAs($pedido->cliente, 'cliente');
    $this->get(route('pedidos.show', $pedido))->assertOk()->assertSee(route('pedidos.foto', $pedido));
    $this->get(route('pedidos.foto', $pedido))->assertOk()->assertHeader('Cache-Control', 'no-store, private');
});

test('the photo is required and a program disguised as a photo is rejected', function (mixed $foto) {
    $pedido = pedidoEnCamino();

    $this->actingAs(repartidor(), 'empleado')
        ->post(route('pos.admin.pedidos.entrega', $pedido), ['foto' => $foto])
        ->assertSessionHasErrors('foto');

    expect($pedido->fresh()->estado_envio)->toBe(EstadoEnvio::Enviado)
        ->and(Storage::disk('local')->allFiles())->toBe([]);
})->with([
    'sin foto' => [fn () => null],
    'php con nombre de imagen' => [fn () => new UploadedFile(base_path('artisan'), 'foto.jpg', 'image/jpeg', null, true)],
    'demasiado grande' => [fn () => UploadedFile::fake()->create('foto.jpg', 3000, 'image/jpeg')],
]);

test('an order that is not on its way cannot be delivered with a photo', function (EstadoEnvio $estado) {
    $pedido = Venta::factory()->enLinea()->create(['estado_envio' => $estado]);

    $this->actingAs(repartidor(), 'empleado')
        ->post(route('pos.admin.pedidos.entrega', $pedido), ['foto' => UploadedFile::fake()->create('foto.jpg', 50, 'image/jpeg')])
        ->assertSessionHasErrors('foto');

    expect($pedido->fresh()->estado_envio)->toBe($estado)
        ->and(Entrega::count())->toBe(0)
        ->and(Storage::disk('local')->allFiles())->toBe([]);
})->with([EstadoEnvio::Pendiente, EstadoEnvio::Entregado, EstadoEnvio::Cancelado]);

test('marking an order as delivered without a photo also forgets the location', function () {
    $entrega = Entrega::factory()->create();

    $this->actingAs(repartidor(), 'empleado')
        ->patch(route('pos.admin.pedidos.update', $entrega->venta), ['estado_envio' => 'entregado'])
        ->assertSessionHasNoErrors();

    expect($entrega->fresh()->latitud)->toBeNull();
});

test('a store sale has no delivery', function () {
    $venta = Venta::factory()->create();
    $this->actingAs(repartidor(), 'empleado');

    $this->get(route('pos.admin.pedidos.reparto', $venta))->assertNotFound();
    $this->putJson(route('pos.admin.pedidos.ubicacion', $venta), ['latitud' => 24.1, 'longitud' => -110.3])->assertNotFound();
    $this->post(route('pos.admin.pedidos.entrega', $venta), ['foto' => UploadedFile::fake()->create('foto.jpg', 50, 'image/jpeg')])->assertNotFound();
});

// ---------- Permisos del navegador ----------

test('camera, location and map images are only allowed where they are used', function () {
    $pedido = pedidoEnCamino();
    $mapa = 'https://tile.openstreetmap.org';

    $reparto = $this->actingAs(repartidor(), 'empleado')->get(route('pos.admin.pedidos.reparto', $pedido))->assertOk();
    $cliente = $this->actingAs($pedido->cliente, 'cliente')->get(route('pedidos.show', $pedido))->assertOk();
    $tienda = $this->get(route('home'))->assertOk();

    expect($reparto->headers->get('Permissions-Policy'))->toContain('camera=(self)', 'geolocation=(self)')
        ->and($reparto->headers->get('Content-Security-Policy'))->toContain($mapa)
        ->and($cliente->headers->get('Permissions-Policy'))->toContain('camera=()', 'geolocation=()')
        ->and($cliente->headers->get('Content-Security-Policy'))->toContain($mapa)
        ->and($tienda->headers->get('Permissions-Policy'))->toContain('camera=()', 'geolocation=()')
        ->and($tienda->headers->get('Content-Security-Policy'))->not->toContain($mapa);
});
