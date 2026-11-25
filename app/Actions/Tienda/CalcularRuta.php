<?php

namespace App\Actions\Tienda;

use App\Models\Venta;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Camino por calles desde quien lleva el pedido hasta el domicilio.
 *
 * Al servicio de rutas solo se le envían dos puntos: sin nombre, dirección ni número de pedido.
 */
class CalcularRuta
{
    /** Se vuelve a pedir la ruta si hubo este avance… */
    private const METROS_MINIMOS = 40;

    /** …y pasó este tiempo desde la consulta anterior. */
    private const SEGUNDOS_ENTRE_CONSULTAS = 15;

    /**
     * @return array{puntos: list<array{float, float}>, metros: int, segundos: int|null, aproximada: bool}|null
     */
    public function handle(Venta $pedido): ?array
    {
        $ubicacion = $pedido->entrega?->ubicacionVigente();
        $destino = $pedido->direccionEnvio?->coordenadas();

        if ($ubicacion === null || $destino === null) {
            return null;
        }

        $origen = [$ubicacion['latitud'], $ubicacion['longitud']];
        $clave = self::clave($pedido->id);
        $guardada = Cache::get($clave);

        if (! is_array($guardada) || $guardada['destino'] !== $destino) {
            $guardada = null;
        }

        if ($guardada !== null && $this->metros($guardada['origen'], $origen) < self::METROS_MINIMOS) {
            return $guardada['ruta'];
        }

        // Una consulta a la vez por pedido, aunque pregunten los dos nodos.
        if (Cache::add($clave.':turno', true, self::SEGUNDOS_ENTRE_CONSULTAS)) {
            $ruta = $this->consultar($origen, $destino);

            if ($ruta !== null) {
                Cache::put($clave, ['origen' => $origen, 'destino' => $destino, 'ruta' => $ruta], now()->addMinutes(10));

                return $ruta;
            }
        }

        return $guardada['ruta'] ?? $this->lineaRecta($origen, $destino);
    }

    /**
     * Clave de la ruta guardada de un pedido.
     */
    public static function clave(int $pedidoId): string
    {
        return "ruta-pedido:{$pedidoId}";
    }

    /**
     * Borra la ruta guardada, que contiene la ubicación de quien lleva el pedido.
     */
    public static function olvidar(int $pedidoId): void
    {
        Cache::forget(self::clave($pedidoId));
        Cache::forget(self::clave($pedidoId).':turno');
    }

    /**
     * Pide la ruta al servicio OSRM. Devuelve null si falla, para no detener el seguimiento.
     *
     * @param  array{float, float}  $origen
     * @param  array{float, float}  $destino
     * @return array{puntos: list<array{float, float}>, metros: int, segundos: int|null, aproximada: bool}|null
     */
    private function consultar(array $origen, array $destino): ?array
    {
        $url = config('services.rutas.url');

        if (! $url) {
            return null;
        }

        try {
            $respuesta = Http::connectTimeout(2)->timeout(4)->acceptJson()->get(
                sprintf('%s/route/v1/driving/%F,%F;%F,%F', rtrim($url, '/'), $origen[1], $origen[0], $destino[1], $destino[0]),
                ['overview' => 'full', 'geometries' => 'geojson'],
            );
        } catch (ConnectionException) {
            return null;
        }

        $ruta = $respuesta->successful() ? $respuesta->json('routes.0') : null;
        $coordenadas = $ruta['geometry']['coordinates'] ?? null;

        if (! is_array($coordenadas) || count($coordenadas) < 2 || ! is_numeric($ruta['distance'] ?? null) || ! is_numeric($ruta['duration'] ?? null)) {
            return null;
        }

        $puntos = [];

        foreach ($coordenadas as $punto) {
            if (! is_array($punto) || ! is_numeric($punto[0] ?? null) || ! is_numeric($punto[1] ?? null)) {
                return null;
            }

            $puntos[] = [round((float) $punto[1], 5), round((float) $punto[0], 5)];
        }

        return [
            'puntos' => $puntos,
            'metros' => (int) round((float) $ruta['distance']),
            'segundos' => (int) round((float) $ruta['duration']),
            'aproximada' => false,
        ];
    }

    /**
     * Respaldo cuando el servicio de rutas no contesta.
     *
     * @param  array{float, float}  $origen
     * @param  array{float, float}  $destino
     * @return array{puntos: list<array{float, float}>, metros: int, segundos: int|null, aproximada: bool}
     */
    private function lineaRecta(array $origen, array $destino): array
    {
        return [
            'puntos' => [$origen, $destino],
            'metros' => (int) round($this->metros($origen, $destino)),
            'segundos' => null,
            'aproximada' => true,
        ];
    }

    /**
     * Distancia en línea recta (fórmula del semiverseno).
     *
     * @param  array{float, float}  $a
     * @param  array{float, float}  $b
     */
    private function metros(array $a, array $b): float
    {
        $latitud = deg2rad($b[0] - $a[0]);
        $longitud = deg2rad($b[1] - $a[1]);
        $semiverseno = sin($latitud / 2) ** 2 + cos(deg2rad($a[0])) * cos(deg2rad($b[0])) * sin($longitud / 2) ** 2;

        return 6371000 * 2 * asin(min(1, sqrt($semiverseno)));
    }
}
