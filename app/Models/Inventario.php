<?php

namespace App\Models;

use Database\Factories\InventarioFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Variante vendible de un producto (talla + color) con su stock.
 *
 * @property int $id
 * @property int $producto_id
 * @property string $talla
 * @property string $color
 * @property string $codigo_barras
 * @property int $stock
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Producto $producto
 * @property-read Collection<int, DetalleVenta> $detallesVenta
 */
#[Fillable(['producto_id', 'talla', 'color', 'codigo_barras', 'stock'])]
class Inventario extends Model
{
    /** @use HasFactory<InventarioFactory> */
    use HasFactory;

    protected $table = 'inventario';

    /**
     * @return BelongsTo<Producto, $this>
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    /**
     * @return HasMany<DetalleVenta, $this>
     */
    public function detallesVenta(): HasMany
    {
        return $this->hasMany(DetalleVenta::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'stock' => 'integer',
        ];
    }
}
