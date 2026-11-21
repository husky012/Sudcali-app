<?php

namespace App\Models;

use Database\Factories\DireccionClienteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $cliente_id
 * @property string $calle_numero
 * @property string $colonia
 * @property string $codigo_postal
 * @property string $ciudad
 * @property string $estado
 * @property string|null $referencias
 * @property bool $es_principal
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Cliente $cliente
 * @property-read Collection<int, Venta> $ventas
 */
#[Fillable(['cliente_id', 'calle_numero', 'colonia', 'codigo_postal', 'ciudad', 'estado', 'referencias', 'es_principal'])]
class DireccionCliente extends Model
{
    /** @use HasFactory<DireccionClienteFactory> */
    use HasFactory;

    protected $table = 'direcciones_cliente';

    /**
     * @return BelongsTo<Cliente, $this>
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * @return HasMany<Venta, $this>
     */
    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'direccion_envio_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'es_principal' => 'boolean',
        ];
    }
}
