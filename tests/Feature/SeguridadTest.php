<?php

use App\Actions\Tienda\Captcha;
use App\Enums\PuestoEmpleado;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Producto;
use App\Support\BitacoraSeguridad;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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

test('every web response carries the security headers', function (string $ruta, int $estado) {
    $respuesta = $this->get($ruta)
        ->assertStatus($estado)
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Permissions-Policy')
        ->assertHeader('X-Frame-Options', 'DENY')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeaderMissing('X-Powered-By');

    expect($respuesta->headers->get('Content-Security-Policy'))
        ->toContain("frame-ancestors 'none'")
        ->toContain("object-src 'none'")
        ->toContain("form-action 'self'");
})->with([
    'página' => ['/tienda', 200],
    'redirección' => ['/empleados', 302],
    'página que no existe' => ['/no-existe-esta-pagina', 404],
]);

test('the content security policy does not allow inline scripts', function () {
    $politica = $this->get(route('home'))->headers->get('Content-Security-Policy');
    preg_match('/script-src ([^;]+)/', $politica, $scripts);

    expect($scripts[1])->toStartWith("'self'")
        ->not->toContain('unsafe-inline')
        ->not->toContain('unsafe-eval');
});

test('no view has scripts or event handlers written inside the html', function () {
    $vistas = File::allFiles(resource_path('views'));

    foreach ($vistas as $vista) {
        expect($vista->getContents())
            ->not->toMatch('/<script(?![^>]*\bsrc=)/i', $vista->getRelativePathname())
            ->not->toMatch('/\son[a-z]+\s*=\s*["\']/i', $vista->getRelativePathname());
    }
});

test('hsts is only sent over https', function () {
    $this->get(route('home'))->assertHeaderMissing('Strict-Transport-Security');

    $this->get(str_replace('http://', 'https://', route('home')))
        ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
});

// ---------- A04: HTTPS y balanceador ----------

test('in production http requests are redirected to https', function (string $metodo, int $estado) {
    app()->detectEnvironment(fn () => 'production');
    config(['app.url' => 'https://sudcali.example']);

    $this->call($metodo, 'http://otro-sitio.example/catalogo?buscar=gorra')
        ->assertStatus($estado)
        ->assertHeader('Location', 'https://sudcali.example/catalogo?buscar=gorra');
})->with([
    'ver una página' => ['GET', 301],
    'enviar un formulario' => ['POST', 308],
]);

test('the health check answers over http so the balancer can use it', function () {
    app()->detectEnvironment(fn () => 'production');

    $this->get('http://localhost/up')->assertOk();
});

test('the trusted balancer can announce https', function () {
    app()->detectEnvironment(fn () => 'production');
    config(['trustedproxy.proxies' => '10.0.0.5']);

    $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.5'])
        ->withHeaders(['X-Forwarded-Proto' => 'https'])
        ->get('http://localhost/tienda')
        ->assertOk()
        ->assertHeader('Strict-Transport-Security');
});

test('nobody else can pretend to be the balancer', function () {
    app()->detectEnvironment(fn () => 'production');
    config(['trustedproxy.proxies' => '10.0.0.5']);

    $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.9'])
        ->withHeaders(['X-Forwarded-Proto' => 'https'])
        ->get('http://localhost/tienda')
        ->assertStatus(301)
        ->assertHeaderMissing('Strict-Transport-Security');
});

// ---------- A05: salida codificada ----------

test('a search with a script is shown as text', function () {
    $this->get(route('catalogo', ['buscar' => '<script>alert(1)</script>']))
        ->assertOk()
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
});

test('a sql injection in the search is treated as plain text', function () {
    $producto = Producto::factory()->create();

    $this->get(route('catalogo', ['buscar' => "zzz' OR 1=1 --"]))
        ->assertOk()
        ->assertDontSee($producto->titulo);
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
        ->assertDontSee('data-websocket-token', false);
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

test('registering with a taken email does not reveal that the account exists', function () {
    $cliente = Cliente::factory()->create();

    $this->withSession([Captcha::CLAVE_SESION => 11])
        ->post(route('registro.store'), registroValido(['correo' => $cliente->correo]))
        ->assertSessionHasErrors(['correo' => 'No pudimos crear la cuenta con esos datos. Revísalos o intenta iniciar sesión.']);

    $this->assertGuest('cliente');
});

test('the login message is the same for a wrong password and for an unknown email', function (string $ruta, string $modelo, string $bolsa, string $mensaje) {
    $cuenta = $modelo::factory()->create();

    foreach ([$cuenta->correo, 'nadie@ejemplo.com'] as $correo) {
        $this->post(route($ruta), ['correo' => $correo, 'password' => 'incorrecta-123'])
            ->assertSessionHasErrors(['correo' => $mensaje], null, $bolsa);
    }
})->with([
    'cliente' => ['tienda.login.store', Cliente::class, 'cliente', 'Correo o contraseña incorrectos.'],
    'empleado' => ['pos.login.store', Empleado::class, 'empleado', 'Correo o contraseña incorrectos, o el empleado está inactivo.'],
]);

// ---------- A09: bitácora de seguridad ----------

test('failed logins are logged without the password or personal data', function () {
    $bitacora = espiarBitacora();
    $empleado = Empleado::factory()->create();

    $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.9'])
        ->post(route('pos.login.store'), ['correo' => $empleado->correo, 'password' => 'incorrecta-123']);

    $bitacora->shouldHaveReceived('log')->with('warning', 'login_fallido', Mockery::on(
        fn (array $contexto) => $contexto['guard'] === 'empleado'
            && $contexto['cuenta'] === BitacoraSeguridad::huella($empleado->correo)
            && $contexto['red'] === '203.0.113.0'
            && ! Str::contains(json_encode($contexto), ['incorrecta-123', $empleado->correo, '203.0.113.9']),
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
        fn (array $contexto) => $contexto['cuenta'] === BitacoraSeguridad::huella($empleado->correo),
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
