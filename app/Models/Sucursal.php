<?php

namespace App\Models;

use Database\Factories\SucursalFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $nombre
 * @property string $direccion
 * @property string|null $telefono
 * @property bool $activa
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Empleado> $empleados
 * @property-read Collection<int, CorteCaja> $cortesCaja
 */
#[Fillable(['nombre', 'direccion', 'telefono', 'activa'])]
class Sucursal extends Model
{
    /** @use HasFactory<SucursalFactory> */
    use HasFactory;

    protected $table = 'sucursales';

    /**
     * @return HasMany<Empleado, $this>
     */
    public function empleados(): HasMany
    {
        return $this->hasMany(Empleado::class);
    }

    /**
     * @return HasMany<CorteCaja, $this>
     */
    public function cortesCaja(): HasMany
    {
        return $this->hasMany(CorteCaja::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'activa' => 'boolean',
        ];
    }
}
