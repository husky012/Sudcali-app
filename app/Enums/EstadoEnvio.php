<?php

namespace App\Enums;

enum EstadoEnvio: string
{
    case Pendiente = 'pendiente';
    case Preparando = 'preparando';
    case Enviado = 'enviado';
    case Entregado = 'entregado';
    case Cancelado = 'cancelado';
}
