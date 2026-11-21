<?php

namespace App\Models;

use App\Enums\EstadoCorte;
use Database\Factories\CorteCajaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $empleado_id
 * @property int $sucursal_id
 * @property Carbon $fecha_apertura
 * @property Carbon|null $fecha_cierre
 * @property string $monto_inicial
 * @property string $ventas_efectivo
 * @property string $ventas_tarjeta
 * @property string|null $monto_final_esperado
 * @property string|null $monto_final_real
 * @property string|null $diferencia
 * @property EstadoCorte $estado
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Empleado $empleado
 * @property-read Sucursal $sucursal
 * @property-read Collection<int, Venta> $ventas
 */
#[Fillable(['empleado_id', 'sucursal_id', 'fecha_apertura', 'fecha_cierre', 'monto_inicial', 'ventas_efectivo', 'ventas_tarjeta', 'monto_final_esperado', 'monto_final_real', 'diferencia', 'estado'])]
class CorteCaja extends Model
{
    /** @use HasFactory<CorteCajaFactory> */
    use HasFactory;

    protected $table = 'cortes_caja';

    /**
     * @return BelongsTo<Empleado, $this>
     */
    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }

    /**
     * @return BelongsTo<Sucursal, $this>
     */
    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    /**
     * @return HasMany<Venta, $this>
     */
    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_apertura' => 'datetime',
            'fecha_cierre' => 'datetime',
            'monto_inicial' => 'decimal:2',
            'ventas_efectivo' => 'decimal:2',
            'ventas_tarjeta' => 'decimal:2',
            'monto_final_esperado' => 'decimal:2',
            'monto_final_real' => 'decimal:2',
            'diferencia' => 'decimal:2',
            'estado' => EstadoCorte::class,
        ];
    }
}
