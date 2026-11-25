<?php

namespace App\Http\Controllers\Tienda;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImagenProductoController extends Controller
{
    /**
     * Entrega la imagen de un producto guardada fuera de la carpeta pública.
     */
    public function __invoke(string $archivo): StreamedResponse
    {
        $ruta = "productos/{$archivo}";

        abort_unless(Storage::disk('local')->exists($ruta), 404);

        return Storage::disk('local')->response($ruta, headers: ['Cache-Control' => 'public, max-age=86400']);
    }
}
