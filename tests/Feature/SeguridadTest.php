<?php

use App\Actions\Tienda\Captcha;
use App\Enums\PuestoEmpleado;
use App\Models\Cliente;
use App\Models\Empleado;
use Illuminate\Support\Facades\Log;
use Mockery\MockInterface;

beforeEach(function () {
    $this->withoutVite();
});

/**
 * Datos válidos del registro con la respuesta del captcha ya guardada en sesión.
 *
 * @param  array<string, mixed>  $cambios
 * @return array<string, mixed>
 */
function registroValido(array $cambios = []): array
{
    return [
        'nombre' => 'Lucía',
        'apellido_paterno' => 'Amador',
        'correo' => 'lucia@ejemplo.com',
        'password' => 'secreta123',
        'password_confirmation' => 'secreta123',
        'terminos' => '1',
        'captcha' => '11',
        ...$cambios,
    ];
}

/**
 * Sustituye el canal "seguridad" por un espía para revisar lo que se registra.
 */
function espiarBitacora(): MockInterface
{
    $bitacora = Mockery::spy();
    Log::shouldReceive('channel')->with('seguridad')->andReturn($bitacora);

    return $bitacora;
}

// ---------- A02: cabeceras de seguridad ----------

test('every web response carries the security headers', function () {
    $respuesta = $this->get(route('home'))
        ->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'DENY')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeaderMissing('X-Powered-By');

    expect($respuesta->headers->get('Content-Security-Policy'))
        ->toContain("frame-ancestors 'none'")
        ->toContain("object-src 'none'")
        ->toContain("form-action 'self'");
});

test('hsts is only sent over https', function () {
    $this->get(route('home'))->assertHeaderMissing('Strict-Transport-Security');

    $this->get(str_replace('http://', 'https://', route('home')))
        ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
});

// ---------- A01: /empleados ----------

test('guests are sent to the pos login from the staff page', function () {
    $this->get(route('empleados'))->assertRedirect(route('pos.login'));
});

test('only managers and administration can open the staff page', function (PuestoEmpleado $puesto, int $estado) {
    $this->actingAs(Empleado::factory()->create(['puesto' => $puesto]), 'empleado')
        ->get(route('empleados'))
        ->assertStatus($estado);
})->with([
    'gerente' => [PuestoEmpleado::Gerente, 200],
    'administración' => [PuestoEmpleado::Administracion, 200],
    'almacén' => [PuestoEmpleado::Almacen, 403],
    'vendedor' => [PuestoEmpleado::Vendedor, 403],
]);

test('store customers cannot open the staff page', function () {
    $this->actingAs(Cliente::factory()->create(), 'cliente')
        ->get(route('empleados'))
        ->assertRedirect(route('pos.login'));
});

test('the staff page gets a signed short lived websocket token', function () {
    $this->freezeTime();
    config(['services.websocket.url' => 'ws://localhost:3000', 'services.websocket.secret' => 'secreto-de-prueba']);
    $gerente = Empleado::factory()->create(['puesto' => PuestoEmpleado::Gerente]);

    $vista = $this->actingAs($gerente, 'empleado')->get(route('empleados'))->assertOk();

    $carga = $gerente->id.'.'.now()->addMinutes(30)->getTimestamp();
    $vista->assertSee($carga.'.'.hash_hmac('sha256', $carga, 'secreto-de-prueba'));
});

test('the staff page does not connect to the websocket when it is not configured', function () {
    config(['services.websocket.url' => null, 'services.websocket.secret' => null]);

    $this->actingAs(Empleado::factory()->create(['puesto' => PuestoEmpleado::Gerente]), 'empleado')
        ->get(route('empleados'))
        ->assertOk()
        ->assertSee('"token":null', false);
});

test('logging out of the pos clears the browser cache and local storage', function () {
    $this->actingAs(Empleado::factory()->create(), 'empleado')
        ->post(route('pos.logout'))
        ->assertRedirect(route('pos.login'))
        ->assertHeader('Clear-Site-Data', '"cache", "storage"');
});

// ---------- A07: registro con captcha y límite ----------

test('the registration form asks a captcha question', function () {
    $this->get(route('registro'))
        ->assertOk()
        ->assertSee('Verificación: ¿Cuánto es')
        ->assertSessionHas(Captcha::CLAVE_SESION);
});

test('registration is rejected with a missing or wrong captcha', function (array $datos) {
    $this->withSession([Captcha::CLAVE_SESION => 11])
        ->post(route('registro.store'), registroValido($datos))
        ->assertSessionHasErrors('captcha');

    $this->assertGuest('cliente');
    expect(Cliente::count())->toBe(0);
})->with([
    'sin respuesta' => [['captcha' => '']],
    'respuesta incorrecta' => [['captcha' => '12']],
    'texto' => [['captcha' => 'once']],
]);

test('a captcha answer cannot be reused', function () {
    $this->withSession([Captcha::CLAVE_SESION => 11])
        ->post(route('registro.store'), registroValido(['correo' => 'no-valido']))
        ->assertSessionHasErrors('correo');

    $this->post(route('registro.store'), registroValido())
        ->assertSessionHasErrors('captcha');

    expect(Cliente::count())->toBe(0);
});

test('registration is rejected when the honeypot field is filled', function () {
    $this->withSession([Captcha::CLAVE_SESION => 11])
        ->post(route('registro.store'), registroValido(['sitio_web' => 'https://spam.example']))
        ->assertSessionHasErrors('sitio_web');

    expect(Cliente::count())->toBe(0);
});

test('registration is limited to five attempts per minute per ip', function () {
    foreach (range(1, 5) as $intento) {
        $this->post(route('registro.store'), registroValido(['captcha' => '']));
    }

    $this->withSession([Captcha::CLAVE_SESION => 11])
        ->post(route('registro.store'), registroValido())
        ->assertSessionHasErrors(['correo' => 'Demasiados intentos de registro. Espera unos minutos e inténtalo de nuevo.']);

    expect(Cliente::count())->toBe(0);
});

// ---------- A09: bitácora de seguridad ----------

test('failed logins are logged without the password', function () {
    $bitacora = espiarBitacora();
    $empleado = Empleado::factory()->create();

    $this->post(route('pos.login.store'), ['correo' => $empleado->correo, 'password' => 'incorrecta-123']);

    $bitacora->shouldHaveReceived('log')->with('warning', 'login_fallido', Mockery::on(
        fn (array $contexto) => $contexto['guard'] === 'empleado'
            && $contexto['correo'] === $empleado->correo
            && ! in_array('incorrecta-123', $contexto, true),
    ));
});

test('the lockout after too many attempts is logged', function () {
    $empleado = Empleado::factory()->create();

    foreach (range(1, 5) as $intento) {
        $this->post(route('pos.login.store'), ['correo' => $empleado->correo, 'password' => 'incorrecta']);
    }

    $bitacora = espiarBitacora();
    $this->post(route('pos.login.store'), ['correo' => $empleado->correo, 'password' => 'incorrecta']);

    $bitacora->shouldHaveReceived('log')->with('alert', 'login_bloqueado', Mockery::on(
        fn (array $contexto) => $contexto['correo'] === $empleado->correo,
    ));
});

test('denied access to an admin screen is logged', function () {
    $vendedor = Empleado::factory()->create(['puesto' => PuestoEmpleado::Vendedor]);
    $bitacora = espiarBitacora();

    $this->actingAs($vendedor, 'empleado')->get(route('pos.admin.empleados.index'))->assertForbidden();

    $bitacora->shouldHaveReceived('log')->with('warning', 'acceso_denegado', Mockery::on(
        fn (array $contexto) => $contexto['empleado_id'] === $vendedor->id && $contexto['permiso'] === 'administrar',
    ));
});
