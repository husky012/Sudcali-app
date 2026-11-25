<?php

namespace App\Models;

use App\Enums\EstadoEnvio;
use App\Enums\MetodoPago;
use App\Enums\OrigenVenta;
use Database\Factories\VentaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * Venta en tienda (empleado + corte de caja) o en línea (cliente + dirección de envío).
 *
 * @property int $id
 * @property OrigenVenta $origen
 * @property int|null $empleado_id
 * @property int|null $cliente_id
 * @property int|null $corte_caja_id
 * @property int|null $direccion_envio_id
 * @property string $subtotal
 * @property string $costo_envio
 * @property string $total
 * @property MetodoPago $metodo_pago
 * @property EstadoEnvio|null $estado_envio
 * @property Carbon|null $created_at Fecha de la venta.
 * @property Carbon|null $updated_at
 * @property-read Empleado|null $empleado
 * @property-read Cliente|null $cliente
 * @property-read CorteCaja|null $corteCaja
 * @property-read DireccionCliente|null $direccionEnvio
 * @property-read Collection<int, DetalleVenta> $detalles
 * @property-read Entrega|null $entrega
 */
#[Fillable(['origen', 'empleado_id', 'cliente_id', 'corte_caja_id', 'direccion_envio_id', 'subtotal', 'costo_envio', 'total', 'metodo_pago', 'estado_envio'])]
class Venta extends Model
{
    /** @use HasFactory<VentaFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Empleado, $this>
     */
    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }

    /**
     * @return BelongsTo<Cliente, $this>
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * @return BelongsTo<CorteCaja, $this>
     */
    public function corteCaja(): BelongsTo
    {
        return $this->belongsTo(CorteCaja::class);
    }

    /**
     * @return BelongsTo<DireccionCliente, $this>
     */
    public function direccionEnvio(): BelongsTo
    {
        return $this->belongsTo(DireccionCliente::class, 'direccion_envio_id');
    }

    /**
     * @return HasMany<DetalleVenta, $this>
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleVenta::class);
    }

    /**
     * @return HasOne<Entrega, $this>
     */
    public function entrega(): HasOne
    {
        return $this->hasOne(Entrega::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'origen' => OrigenVenta::class,
            'subtotal' => 'decimal:2',
            'costo_envio' => 'decimal:2',
            'total' => 'decimal:2',
            'metodo_pago' => MetodoPago::class,
            'estado_envio' => EstadoEnvio::class,
        ];
    }
}
