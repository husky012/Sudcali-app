<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class SaludController extends Controller
{
    /**
     * Estado del nodo para el balanceador: comprueba que la base de datos conteste.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $nodo = config('services.nodo');

        try {
            DB::select('select 1');
        } catch (Throwable $error) {
            report($error);

            return response()->json(['nodo' => $nodo, 'bd' => 'caida'], 503);
        }

        return response()->json([
            'nodo' => $nodo,
            'bd' => 'ok',
            'ip_cliente' => $request->ip(),
        ]);
    }
}
