<?php

beforeEach(function () {
    $this->withoutVite();
});

test('the health check reports the node and the database', function () {
    config(['services.nodo' => '2']);

    $this->get('/salud')
        ->assertOk()
        ->assertExactJson(['nodo' => '2', 'bd' => 'ok', 'ip_cliente' => '127.0.0.1'])
        ->assertHeader('X-Servidor', '2')
        ->assertCookieMissing(config('session.cookie'));
});

test('the health check fails when the database does not answer', function () {
    $conexion = config('database.default');
    config([
        'database.default' => 'rota',
        'database.connections.rota' => ['driver' => 'sqlite', 'database' => base_path('no-existe.sqlite')],
    ]);

    $respuesta = $this->get('/salud');

    config(['database.default' => $conexion]);

    $respuesta->assertStatus(503)
        ->assertExactJson(['nodo' => config('services.nodo'), 'bd' => 'caida']);
});

test('the health check shows the visitor address announced by the balancer', function () {
    config(['trustedproxy.proxies' => '172.28.0.10']);

    $this->withServerVariables(['REMOTE_ADDR' => '172.28.0.10'])
        ->withHeaders(['X-Forwarded-For' => '203.0.113.9'])
        ->get('/salud')
        ->assertJsonPath('ip_cliente', '203.0.113.9');
});

test('the health check answers over http in production so the balancer can use it', function () {
    app()->detectEnvironment(fn () => 'production');

    $this->get('http://localhost/salud')->assertOk();
});

test('pages do not name the server when no node is configured', function () {
    config(['services.nodo' => null]);

    $this->get(route('login'))->assertHeaderMissing('X-Servidor');
});
