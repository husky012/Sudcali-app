<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CatalogoSeeder extends Seeder
{
    /**
     * Catálogo base: una categoría por carpeta de imágenes en public/imagenes.
     *
     * @var array<int, array{nombre: string, carpeta: string, prefijo: string, precio: int, descripcion: string, tallas: array<int, string>}>
     */
    private const CATEGORIAS = [
        [
            'nombre' => 'Playeras',
            'carpeta' => 'camisas',
            'prefijo' => 'Playera Sudcali',
            'precio' => 250,
            'descripcion' => 'Algodón 100% premium. Diseñada localmente en La Paz. Corte unisex.',
            'tallas' => ['CH', 'M', 'G', 'XG'],
        ],
        [
            'nombre' => 'Gorras',
            'carpeta' => 'gorras',
            'prefijo' => 'Gorra Sudcali',
            'precio' => 650,
            'descripcion' => 'Gorra con bordado Sudcali. Ajustable, ideal para el día de malecón.',
            'tallas' => ['Única'],
        ],
        [
            'nombre' => 'Buffs',
            'carpeta' => 'buff',
            'prefijo' => 'Buff Sudcali',
            'precio' => 180,
            'descripcion' => 'Buff multiusos con protección solar para playa, pesca y bici.',
            'tallas' => ['Única'],
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $consecutivo = 0;

        foreach (self::CATEGORIAS as $datos) {
            $categoria = Categoria::updateOrCreate(
                ['slug' => Str::slug($datos['nombre'])],
                ['nombre' => $datos['nombre']],
            );

            $imagenes = collect(File::files(public_path('imagenes/'.$datos['carpeta'])))
                ->map(fn ($archivo) => $archivo->getFilename())
                ->sort(SORT_NATURAL)
                ->values();

            foreach ($imagenes as $indice => $imagen) {
                $titulo = $datos['prefijo'].' #'.($indice + 1);

                $producto = Producto::updateOrCreate(
                    ['slug' => Str::slug($titulo)],
                    [
                        'categoria_id' => $categoria->id,
                        'titulo' => $titulo,
                        'descripcion' => $datos['descripcion'],
                        'precio' => $datos['precio'],
                        'imagen' => '/imagenes/'.$datos['carpeta'].'/'.$imagen,
                        'activo' => true,
                    ],
                );

                foreach ($datos['tallas'] as $talla) {
                    $consecutivo++;

                    $producto->inventario()->updateOrCreate(
                        ['talla' => $talla, 'color' => 'Único'],
                        [
                            'codigo_barras' => '750'.str_pad((string) $consecutivo, 10, '0', STR_PAD_LEFT),
                            'stock' => 10,
                        ],
                    );
                }
            }
        }
    }
}
