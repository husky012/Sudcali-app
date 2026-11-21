<?php

use App\Enums\EstadoCorte;
use App\Models\CorteCaja;
use App\Models\Empleado;
use App\Models\Inventario;
use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;

beforeEach(function () {
    $this->withoutVite();
});

/**
 * Empleado con una caja abierta y fondo de $500.
 */
function cajeroConCajaAbierta(): CorteCaja
{
    return CorteCaja::factory()->create(['monto_inicial' => 500]);
}

/**
 * Variante en existencia de un producto con precio fijo.
 */
function varianteConStock(int $stock = 5, float $precio = 250): Inventario
{
    return Inventario::factory()
        ->for(Producto::factory()->state(['precio' => $precio]))
        ->create(['stock' => $stock]);
}

test('the app opens on the employee login', function () {
    $this->get('/')->assertRedirect(route('pos.login'));
});

test('an employee who is already logged in skips the login screen', function () {
    $this->actingAs(Empleado::factory()->create(), 'empleado')
        ->get(route('pos.login'))
        ->assertRedirect(route('pos.inicio'));
});

test('guests are sent to the employee login', function () {
    $this->get(route('pos.ventas.create'))->assertRedirect(route('pos.login'));
});

test('a logged in customer cannot use the point of sale', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('pos.ventas.create'))
        ->assertRedirect(route('pos.login'));
});

test('active employees can log in', function () {
    $empleado = Empleado::factory()->create();

    $this->post(route('pos.login.store'), [
        'correo' => $empleado->correo,
        'password' => 'password',
    ])->assertRedirect(route('pos.inicio'));

    $this->assertAuthenticatedAs($empleado, 'empleado');
});

test('inactive employees cannot log in', function () {
    $empleado = Empleado::factory()->create(['activo' => false]);

    $this->post(route('pos.login.store'), [
        'correo' => $empleado->correo,
        'password' => 'password',
    ])->assertSessionHasErrorsIn('empleado', 'correo');

    $this->assertGuest('empleado');
});

test('login validation explains each problem in spanish', function (array $datos, string $campo, string $mensaje) {
    $this->from(route('pos.login'))
        ->post(route('pos.login.store'), $datos)
        ->assertRedirect(route('pos.login'))
        ->assertSessionHasErrorsIn('empleado', [$campo => $mensaje]);

    $this->assertGuest('empleado');
})->with([
    'missing email' => [['password' => 'password'], 'correo', 'Escribe tu correo de empleado.'],
    'malformed email' => [['correo' => 'gerente', 'password' => 'password'], 'correo', 'Escribe un correo válido, por ejemplo nombre@sudcali.test.'],
    'missing password' => [['correo' => 'gerente@sudcali.test'], 'password', 'Escribe tu contraseña.'],
]);

test('login ignores letter case and surrounding spaces in the email', function () {
    $empleado = Empleado::factory()->create(['correo' => 'cajero@sudcali.test']);

    $this->post(route('pos.login.store'), [
        'correo' => '  CAJERO@Sudcali.test ',
        'password' => 'password',
    ])->assertRedirect(route('pos.inicio'));

    $this->assertAuthenticatedAs($empleado, 'empleado');
});

test('login is locked with a readable message after five failed attempts', function () {
    $empleado = Empleado::factory()->create();
    $intento = ['correo' => $empleado->correo, 'password' => 'incorrecta'];

    foreach (range(1, 5) as $numero) {
        $this->post(route('pos.login.store'), $intento);
    }

    $this->post(route('pos.login.store'), [...$intento, 'password' => 'password'])
        ->assertSessionHasErrorsIn('empleado', 'correo');

    expect(session('errors')->getBag('empleado')->first('correo'))->toStartWith('Demasiados intentos fallidos');

    $this->assertGuest('empleado');
});

test('an employee must open the cash register before selling', function () {
    $this->actingAs(Empleado::factory()->create(), 'empleado')
        ->get(route('pos.ventas.create'))
        ->assertRedirect(route('pos.caja.create'));
});

test('opening the cash register creates a session in the employee branch', function () {
    $empleado = Empleado::factory()->create();

    $this->actingAs($empleado, 'empleado')
        ->post(route('pos.caja.store'), ['monto_inicial' => 750])
        ->assertRedirect(route('pos.ventas.create'));

    expect($empleado->fresh()->corteAbierto)
        ->sucursal_id->toBe($empleado->sucursal_id)
        ->monto_inicial->toBe('750.00')
        ->estado->toBe(EstadoCorte::Abierto);
});

test('the sale screen renders for an employee with an open register', function () {
    $corte = cajeroConCajaAbierta();

    $this->actingAs($corte->empleado, 'empleado')
        ->get(route('pos.ventas.create'))
        ->assertOk()
        ->assertSee('Venta actual');
});

test('a cash sale uses database prices, discounts stock and adds to the register', function () {
    $corte = cajeroConCajaAbierta();
    $variante = varianteConStock(stock: 5, precio: 250);

    $respuesta = $this->actingAs($corte->empleado, 'empleado')
        ->post(route('pos.ventas.store'), [
            'articulos' => [['inventario_id' => $variante->id, 'cantidad' => 2, 'precio_unitario' => 1]],
            'metodo_pago' => 'efectivo',
            'monto_recibido' => 600,
        ]);

    $venta = Venta::sole();

    $respuesta->assertRedirect(route('pos.ventas.show', $venta))
        ->assertSessionHas('cambio', 100.0);

    expect($venta->total)->toBe('500.00')
        ->and($venta->empleado_id)->toBe($corte->empleado_id)
        ->and($venta->detalles->sole()->precio_unitario)->toBe('250.00')
        ->and($variante->fresh()->stock)->toBe(3)
        ->and($corte->fresh()->ventas_efectivo)->toBe('500.00');
});

test('a card sale is added to the card total', function () {
    $corte = cajeroConCajaAbierta();
    $variante = varianteConStock(precio: 650);

    $this->actingAs($corte->empleado, 'empleado')
        ->post(route('pos.ventas.store'), [
            'articulos' => [['inventario_id' => $variante->id, 'cantidad' => 1]],
            'metodo_pago' => 'tarjeta',
        ])
        ->assertSessionHasNoErrors();

    expect($corte->fresh())
        ->ventas_tarjeta->toBe('650.00')
        ->ventas_efectivo->toBe('0.00');
});

test('a sale cannot exceed the available stock', function () {
    $corte = cajeroConCajaAbierta();
    $variante = varianteConStock(stock: 1);

    $this->actingAs($corte->empleado, 'empleado')
        ->post(route('pos.ventas.store'), [
            'articulos' => [['inventario_id' => $variante->id, 'cantidad' => 2]],
            'metodo_pago' => 'tarjeta',
        ])
        ->assertSessionHasErrors('articulos.0.cantidad');

    expect(Venta::count())->toBe(0)
        ->and($variante->fresh()->stock)->toBe(1);
});

test('a cash sale requires enough cash to cover the total', function () {
    $corte = cajeroConCajaAbierta();
    $variante = varianteConStock(precio: 250);

    $this->actingAs($corte->empleado, 'empleado')
        ->post(route('pos.ventas.store'), [
            'articulos' => [['inventario_id' => $variante->id, 'cantidad' => 1]],
            'metodo_pago' => 'efectivo',
            'monto_recibido' => 200,
        ])
        ->assertSessionHasErrors('monto_recibido');

    expect(Venta::count())->toBe(0);
});

test('closing the register records the difference against expected cash', function () {
    $corte = cajeroConCajaAbierta();
    $corte->update(['ventas_efectivo' => 1000, 'ventas_tarjeta' => 300]);

    $this->actingAs($corte->empleado, 'empleado')
        ->put(route('pos.caja.update'), ['monto_final_real' => 1480])
        ->assertRedirect(route('pos.caja.show', $corte));

    expect($corte->fresh())
        ->estado->toBe(EstadoCorte::Cerrado)
        ->monto_final_esperado->toBe('1500.00')
        ->diferencia->toBe('-20.00');
});

test('every point of sale screen renders through a full shift', function () {
    $this->get(route('pos.login'))->assertOk()->assertSee('Entrar al punto de venta');

    $empleado = Empleado::factory()->create();
    $this->actingAs($empleado, 'empleado');

    $this->get(route('pos.caja.create'))->assertOk()->assertSee('Abrir caja');
    $this->post(route('pos.caja.store'), ['monto_inicial' => 500]);

    $variante = varianteConStock();
    $this->post(route('pos.ventas.store'), [
        'articulos' => [['inventario_id' => $variante->id, 'cantidad' => 1]],
        'metodo_pago' => 'efectivo',
        'monto_recibido' => 300,
    ]);
    $venta = Venta::sole();

    $this->get(route('pos.ventas.show', $venta))->assertOk()->assertSee($variante->producto->titulo);
    $this->get(route('pos.ventas.index'))->assertOk()->assertSee('Ver ticket');
    $this->get(route('pos.caja.edit'))->assertOk()->assertSee('$750.00');

    $this->put(route('pos.caja.update'), ['monto_final_real' => 750]);

    $this->get(route('pos.caja.show', $venta->corte_caja_id))->assertOk()->assertSee('Corte de caja');
});

test('employees cannot see tickets from another branch', function () {
    $venta = Venta::factory()->create();
    $otraSucursal = Empleado::factory()->create();

    $this->actingAs($otraSucursal, 'empleado')
        ->get(route('pos.ventas.show', $venta))
        ->assertForbidden();
});

test('inventory search matches barcodes and hides inactive products', function () {
    $corte = cajeroConCajaAbierta();
    $variante = varianteConStock();
    Inventario::factory()->for(Producto::factory()->inactivo())->create();

    $this->actingAs($corte->empleado, 'empleado')
        ->getJson(route('pos.inventario.buscar', ['buscar' => $variante->codigo_barras]))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $variante->id);

    $this->actingAs($corte->empleado, 'empleado')
        ->getJson(route('pos.inventario.buscar'))
        ->assertJsonCount(1, 'data');
});
