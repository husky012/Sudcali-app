<?php

namespace App\Models;

use App\Enums\EstadoCorte;
use App\Enums\PuestoEmpleado;
use Database\Factories\EmpleadoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $sucursal_id
 * @property string $nombre
 * @property string $apellido_paterno
 * @property string|null $apellido_materno
 * @property PuestoEmpleado $puesto
 * @property Carbon $fecha_contratacion
 * @property string|null $foto
 * @property string $telefono
 * @property string $correo
 * @property string $password
 * @property bool $activo
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Sucursal $sucursal
 * @property-read Collection<int, Venta> $ventas
 * @property-read Collection<int, CorteCaja> $cortesCaja
 * @property-read CorteCaja|null $corteAbierto
 */
#[Fillable(['sucursal_id', 'nombre', 'apellido_paterno', 'apellido_materno', 'puesto', 'fecha_contratacion', 'foto', 'telefono', 'correo', 'password', 'activo'])]
#[Hidden(['password', 'remember_token'])]
class Empleado extends Authenticatable
{
    /** @use HasFactory<EmpleadoFactory> */
    use HasFactory;

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
     * @return HasMany<CorteCaja, $this>
     */
    public function cortesCaja(): HasMany
    {
        return $this->hasMany(CorteCaja::class);
    }

    /**
     * Corte de caja que el empleado tiene abierto en este momento.
     *
     * @return HasOne<CorteCaja, $this>
     */
    public function corteAbierto(): HasOne
    {
        return $this->hasOne(CorteCaja::class)
            ->where('estado', EstadoCorte::Abierto)
            ->latestOfMany('fecha_apertura');
    }

    /**
     * Nombre completo para mostrar en tickets y encabezados.
     */
    public function nombreCompleto(): string
    {
        return trim("{$this->nombre} {$this->apellido_paterno} {$this->apellido_materno}");
    }

    /**
     * URL pública de la foto o null si no tiene.
     */
    public function fotoUrl(): ?string
    {
        return $this->foto ? asset('storage/'.$this->foto) : null;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'puesto' => PuestoEmpleado::class,
            'fecha_contratacion' => 'date',
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }
}
