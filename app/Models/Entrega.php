<?php

namespace App\Models;

use App\Actions\Tienda\CalcularRuta;
use Database\Factories\EntregaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Reparto de un pedido en línea: última ubicación de quien lo lleva y foto de la entrega.
 *
 * Solo se guarda la última ubicación, no el recorrido, y se borra cuando el pedido deja de ir en camino.
 *
 * @property int $id
 * @property int $venta_id
 * @property int|null $empleado_id Quien lleva el pedido.
 * @property string|null $latitud
 * @property string|null $longitud
 * @property int|null $precision_metros
 * @property Carbon|null $ubicacion_en
 * @property string|null $foto Ruta en el disco privado.
 * @property Carbon|null $entregado_en
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Venta $venta
 * @property-read Empleado|null $empleado
 */
#[Fillable(['venta_id', 'empleado_id', 'latitud', 'longitud', 'precision_metros', 'ubicacion_en', 'foto', 'entregado_en'])]
class Entrega extends Model
{
    /** @use HasFactory<EntregaFactory> */
    use HasFactory;

    /** Minutos que se muestra una ubicación después de recibirla. */
    public const MINUTOS_VIGENCIA = 10;

    /**
     * @return BelongsTo<Venta, $this>
     */
    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class);
    }

    /**
     * @return BelongsTo<Empleado, $this>
     */
    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }

    /**
     * Ubicación reciente para el mapa, o null si no hay o ya es vieja.
     *
     * @return array{latitud: float, longitud: float, precision: int|null, segundos: int}|null
     */
    public function ubicacionVigente(): ?array
    {
        if ($this->latitud === null || $this->longitud === null || $this->ubicacion_en === null) {
            return null;
        }

        if ($this->ubicacion_en->lt(now()->subMinutes(self::MINUTOS_VIGENCIA))) {
            return null;
        }

        return [
            'latitud' => (float) $this->latitud,
            'longitud' => (float) $this->longitud,
            'precision' => $this->precision_metros,
            'segundos' => (int) $this->ubicacion_en->diffInSeconds(now()),
        ];
    }

    /**
     * Borra la ubicación guardada y la ruta que parte de ella.
     */
    public function olvidarUbicacion(): void
    {
        $this->update(['latitud' => null, 'longitud' => null, 'precision_metros' => null, 'ubicacion_en' => null]);

        CalcularRuta::olvidar($this->venta_id);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitud' => 'decimal:7',
            'longitud' => 'decimal:7',
            'precision_metros' => 'integer',
            'ubicacion_en' => 'datetime',
            'entregado_en' => 'datetime',
        ];
    }
}
