<?php

namespace App\Models;

use Database\Factories\ClienteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $nombre
 * @property string $apellido_paterno
 * @property string|null $apellido_materno
 * @property string|null $telefono
 * @property string $correo
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at Fecha de registro.
 * @property Carbon|null $updated_at
 * @property-read Collection<int, DireccionCliente> $direcciones
 * @property-read Collection<int, Venta> $ventas
 */
#[Fillable(['nombre', 'apellido_paterno', 'apellido_materno', 'telefono', 'correo', 'password'])]
#[Hidden(['password', 'remember_token'])]
class Cliente extends Authenticatable
{
    /** @use HasFactory<ClienteFactory> */
    use HasFactory;

    /**
     * @return HasMany<DireccionCliente, $this>
     */
    public function direcciones(): HasMany
    {
        return $this->hasMany(DireccionCliente::class);
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
            'password' => 'hashed',
        ];
    }
}
