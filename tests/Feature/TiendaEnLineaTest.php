<?php

use App\Enums\EstadoEnvio;
use App\Enums\OrigenVenta;
use App\Enums\PuestoEmpleado;
use App\Models\Cliente;
use App\Models\DireccionCliente;
use App\Models\Empleado;
use App\Models\Inventario;
use App\Models\Producto;
use App\Models\Venta;

beforeEach(function () {
    $this->withoutVite();
});

function tallaEnVenta(int $stock = 5, float $precio = 250): Inventario
{
    return Inventario::factory()
        ->for(Producto::factory()->state(['precio' => $precio]))
        ->create(['stock' => $stock]);
}

// ---------- Acceso con pestañas ----------

test('the login page shows the customer tab by default and the employee tab for the POS', function () {
    $pestanaSeleccionada = fn (string $pestana) => '/id="pestana-'.$pestana.'"[^>]*aria-selected="true"/';

    $tienda = $this->get(route('login'))
        ->assertOk()
        ->assertSee('Entrar a la tienda')
        ->assertSee('Entrar al punto de venta');

    expect($tienda->getContent())->toMatch($pestanaSeleccionada('cliente'));

    $punto = $this->get(route('pos.login'))->assertOk();

    expect($punto->getContent())->toMatch($pestanaSeleccionada('empleado'));
});

test('customers can register and are logged in', function () {
    $this->post(route('registro.store'), [
        'nombre' => 'Lucía',
        'apellido_paterno' => 'Amador',
        'correo' => 'Lucia@Ejemplo.com',
        'telefono' => '612 555 1234',
        'password' => 'secreta123',
        'password_confirmation' => 'secreta123',
        'terminos' => '1',
    ])->assertRedirect(route('home'));

    $this->assertAuthenticatedAs(Cliente::where('correo', 'lucia@ejemplo.com')->sole(), 'cliente');
});

test('registration requires accepting the terms', function () {
    $this->post(route('registro.store'), [
        'nombre' => 'Lucía',
        'apellido_paterno' => 'Amador',
        'correo' => 'lucia@ejemplo.com',
        'password' => 'secreta123',
        'password_confirmation' => 'secreta123',
    ])->assertSessionHasErrors(['terminos' => 'Debes aceptar los términos y condiciones.']);

    $this->assertGuest('cliente');
});

test('customers can log in from the customer tab', function () {
    $cliente = Cliente::factory()->create();

    $this->post(route('tienda.login.store'), ['correo' => $cliente->correo, 'password' => 'password'])
        ->assertRedirect(route('home'));

    $this->assertAuthenticatedAs($cliente, 'cliente');
    $this->assertGuest('empleado');
});

test('wrong customer credentials show the error only on the customer tab', function () {
    $cliente = Cliente::factory()->create();

    $this->from(route('login'))
        ->post(route('tienda.login.store'), ['correo' => $cliente->correo, 'password' => 'incorrecta'])
        ->assertSessionHasErrorsIn('cliente', ['correo' => 'Correo o contraseña incorrectos.']);

    $this->assertGuest('cliente');
});

test('an employee account cannot log in through the customer tab', function () {
    $empleado = Empleado::factory()->create();

    $this->post(route('tienda.login.store'), ['correo' => $empleado->correo, 'password' => 'password'])
        ->assertSessionHasErrorsIn('cliente', 'correo');

    $this->assertGuest('cliente');
});

// ---------- Catálogo ----------

test('the catalog lists only visible products and filters by category', function () {
    $playera = Producto::factory()->create(['titulo' => 'Playera Malecón']);
    $oculta = Producto::factory()->inactivo()->create(['titulo' => 'Playera Oculta']);
    $gorra = Producto::factory()->create(['titulo' => 'Gorra Balandra']);

    $this->get(route('catalogo'))
        ->assertOk()
        ->assertSee('Playera Malecón')
        ->assertSee('Gorra Balandra')
        ->assertDontSee('Playera Oculta');

    $this->get(route('catalogo', ['categoria' => $gorra->categoria->slug]))
        ->assertSee('Gorra Balandra')
        ->assertDontSee('Playera Malecón');
});

test('the home page and product page render real products', function () {
    $variante = tallaEnVenta();

    $this->get(route('home'))->assertOk()->assertSee($variante->producto->titulo);

    $this->get(route('producto.detalle', $variante->producto))
        ->assertOk()
        ->assertSee($variante->producto->titulo)
        ->assertSee($variante->talla);
});

test('hidden products return not found', function () {
    $this->get(route('producto.detalle', Producto::factory()->inactivo()->create()))->assertNotFound();
});

// ---------- Carrito ----------

test('guests can add items to the cart without exceeding stock', function () {
    $variante = tallaEnVenta(stock: 2);

    $this->post(route('carrito.agregar'), ['inventario_id' => $variante->id, 'cantidad' => 5])
        ->assertRedirect(route('carrito'));

    expect(session('carrito'))->toBe([$variante->id => 2]);

    $this->get(route('carrito'))->assertOk()->assertSee($variante->producto->titulo)->assertSee('$500.00');
});

test('sold out sizes cannot be added to the cart', function () {
    $variante = tallaEnVenta(stock: 0);

    $this->post(route('carrito.agregar'), ['inventario_id' => $variante->id, 'cantidad' => 1])
        ->assertSessionHasErrors('inventario_id');

    expect(session('carrito'))->toBeNull();
});

test('cart quantities can be changed and items removed', function () {
    $variante = tallaEnVenta(stock: 10);
    $this->withSession(['carrito' => [$variante->id => 1]]);

    $this->patch(route('carrito.actualizar', $variante), ['cantidad' => 3]);
    expect(session('carrito'))->toBe([$variante->id => 3]);

    $this->delete(route('carrito.quitar', $variante->id));
    expect(session('carrito'))->toBe([]);
});

test('shipping is free from 1000 pesos', function () {
    $variante = tallaEnVenta(stock: 10, precio: 500);

    $this->withSession(['carrito' => [$variante->id => 1]])
        ->get(route('carrito'))
        ->assertSee('$99.00 MXN');

    $this->withSession(['carrito' => [$variante->id => 2]])
        ->get(route('carrito'))
        ->assertSee('Gratis');
});

// ---------- Compra ----------

test('guests must log in as customers before checking out', function () {
    $this->get(route('checkout.create'))->assertRedirect(route('login'));
});

test('a customer checks out with a new address and the order discounts stock', function () {
    $cliente = Cliente::factory()->create();
    $variante = tallaEnVenta(stock: 5, precio: 250);

    $this->actingAs($cliente, 'cliente')
        ->withSession(['carrito' => [$variante->id => 2]])
        ->get(route('checkout.create'))
        ->assertOk();

    $respuesta = $this->post(route('checkout.store'), [
        'direccion_id' => 'nueva',
        'calle_numero' => 'Revolución 123',
        'colonia' => 'Centro',
        'codigo_postal' => '23000',
        'metodo_pago' => 'transferencia',
    ]);

    $pedido = Venta::sole();
    $respuesta->assertRedirect(route('pedidos.show', $pedido));

    expect($pedido)
        ->origen->toBe(OrigenVenta::EnLinea)
        ->estado_envio->toBe(EstadoEnvio::Pendiente)
        ->cliente_id->toBe($cliente->id)
        ->subtotal->toBe('500.00')
        ->costo_envio->toBe('99.00')
        ->total->toBe('599.00')
        ->and($pedido->direccionEnvio->es_principal)->toBeTrue()
        ->and($variante->fresh()->stock)->toBe(3)
        ->and(session('carrito'))->toBeNull();
});

test('checkout fails if stock ran out after adding to the cart', function () {
    $cliente = Cliente::factory()->create();
    $direccion = DireccionCliente::factory()->for($cliente)->create();
    $variante = tallaEnVenta(stock: 1);

    $this->actingAs($cliente, 'cliente')
        ->withSession(['carrito' => [$variante->id => 2]])
        ->post(route('checkout.store'), ['direccion_id' => $direccion->id, 'metodo_pago' => 'efectivo'])
        ->assertSessionHasErrors('carrito');

    expect(Venta::count())->toBe(0)
        ->and($variante->fresh()->stock)->toBe(1);
});

test('a customer cannot ship to someone else\'s address', function () {
    $ajena = DireccionCliente::factory()->create();
    $variante = tallaEnVenta();

    $this->actingAs(Cliente::factory()->create(), 'cliente')
        ->withSession(['carrito' => [$variante->id => 1]])
        ->post(route('checkout.store'), ['direccion_id' => $ajena->id, 'metodo_pago' => 'efectivo'])
        ->assertSessionHasErrors('direccion_id');

    expect(Venta::count())->toBe(0);
});

test('shipping is limited to Baja California Sur postal codes', function () {
    $variante = tallaEnVenta();

    $this->actingAs(Cliente::factory()->create(), 'cliente')
        ->withSession(['carrito' => [$variante->id => 1]])
        ->post(route('checkout.store'), [
            'direccion_id' => 'nueva',
            'calle_numero' => 'Reforma 1',
            'colonia' => 'Juárez',
            'codigo_postal' => '06600',
            'metodo_pago' => 'efectivo',
        ])
        ->assertSessionHasErrors('codigo_postal');
});

// ---------- Mis pedidos ----------

test('customers see their own orders but not other customers orders', function () {
    $pedido = Venta::factory()->enLinea()->create();
    $ajeno = Venta::factory()->enLinea()->create();

    $this->actingAs($pedido->cliente, 'cliente');

    $this->get(route('pedidos.index'))->assertOk()->assertSee('#'.str_pad($pedido->id, 6, '0', STR_PAD_LEFT));
    $this->get(route('pedidos.show', $pedido))->assertOk()->assertSee('Recibido');
    $this->get(route('pedidos.show', $ajeno))->assertNotFound();
});

// ---------- Pedidos en línea desde el POS ----------

test('staff can move an online order forward', function () {
    $pedido = Venta::factory()->enLinea()->create();

    $this->actingAs(Empleado::factory()->create(['puesto' => PuestoEmpleado::Almacen]), 'empleado')
        ->get(route('pos.admin.pedidos.index'))
        ->assertOk()
        ->assertSee('#'.str_pad($pedido->id, 6, '0', STR_PAD_LEFT));

    $this->patch(route('pos.admin.pedidos.update', $pedido), ['estado_envio' => 'preparando'])
        ->assertSessionHasNoErrors();

    expect($pedido->fresh()->estado_envio)->toBe(EstadoEnvio::Preparando);
});

test('cancelling an online order returns its pieces to stock', function () {
    $variante = tallaEnVenta(stock: 3);
    $pedido = Venta::factory()->enLinea()->create();
    $pedido->detalles()->create(['inventario_id' => $variante->id, 'cantidad' => 2, 'precio_unitario' => 250, 'subtotal' => 500]);

    $this->actingAs(Empleado::factory()->create(['puesto' => PuestoEmpleado::Gerente]), 'empleado')
        ->patch(route('pos.admin.pedidos.update', $pedido), ['estado_envio' => 'cancelado']);

    expect($pedido->fresh()->estado_envio)->toBe(EstadoEnvio::Cancelado)
        ->and($variante->fresh()->stock)->toBe(5);

    $this->patch(route('pos.admin.pedidos.update', $pedido), ['estado_envio' => 'cancelado'])
        ->assertSessionHasErrors('estado_envio');

    expect($variante->fresh()->stock)->toBe(5);
});

test('salespeople cannot manage online orders', function () {
    $this->actingAs(Empleado::factory()->create(['puesto' => PuestoEmpleado::Vendedor]), 'empleado')
        ->get(route('pos.admin.pedidos.index'))
        ->assertForbidden();
});
