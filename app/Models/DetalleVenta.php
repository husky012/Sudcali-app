<?php

namespace App\Models;

use Database\Factories\DetalleVentaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $venta_id
 * @property int $inventario_id
 * @property int $cantidad
 * @property string $precio_unitario Precio al momento de la venta.
 * @property string $subtotal
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Venta $venta
 * @property-read Inventario $inventario
 */
#[Fillable(['venta_id', 'inventario_id', 'cantidad', 'precio_unitario', 'subtotal'])]
class DetalleVenta extends Model
{
    /** @use HasFactory<DetalleVentaFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Venta, $this>
     */
    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class);
    }

    /**
     * @return BelongsTo<Inventario, $this>
     */
    public function inventario(): BelongsTo
    {
        return $this->belongsTo(Inventario::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cantidad' => 'integer',
            'precio_unitario' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }
}
