<?php

namespace App\Enums;

enum PuestoEmpleado: string
{
    case Vendedor = 'Vendedor';
    case Almacen = 'Almacén';
    case Administracion = 'Administración';
    case Gerente = 'Gerente';

    /**
     * Puede dar de alta empleados y editar el catálogo.
     */
    public function puedeAdministrar(): bool
    {
        return in_array($this, [self::Gerente, self::Administracion], true);
    }

    /**
     * Puede ver y ajustar existencias.
     */
    public function puedeGestionarInventario(): bool
    {
        return $this->puedeAdministrar() || $this === self::Almacen;
    }
}
