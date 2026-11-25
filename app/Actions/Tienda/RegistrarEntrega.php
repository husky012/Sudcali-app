<?php

namespace App\Actions\Tienda;

use App\Enums\EstadoEnvio;
use App\Models\Empleado;
use App\Models\Entrega;
use App\Models\Venta;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class RegistrarEntrega
{
    /**
     * Marca el pedido como entregado con su foto y borra la ubicación de quien lo llevó.
     *
     * @throws ValidationException
     */
    public function handle(Venta $pedido, Empleado $empleado, UploadedFile $foto): Entrega
    {
        $ruta = $foto->store('entregas', 'local');

        if ($ruta === false) {
            throw ValidationException::withMessages(['foto' => 'No se pudo guardar la foto. Intenta de nuevo.']);
        }

        try {
            return DB::transaction(function () use ($pedido, $empleado, $ruta) {
                $bloqueado = Venta::query()->lockForUpdate()->findOrFail($pedido->id);

                if ($bloqueado->estado_envio !== EstadoEnvio::Enviado) {
                    throw ValidationException::withMessages([
                        'foto' => 'Solo se puede entregar un pedido que va en camino.',
                    ]);
                }

                $bloqueado->update(['estado_envio' => EstadoEnvio::Entregado]);

                $entrega = $bloqueado->entrega()->updateOrCreate([], [
                    'empleado_id' => $empleado->id,
                    'foto' => $ruta,
                    'entregado_en' => now(),
                ]);

                $entrega->olvidarUbicacion();

                return $entrega;
            });
        } catch (Throwable $error) {
            Storage::disk('local')->delete($ruta);

            throw $error;
        }
    }
}
