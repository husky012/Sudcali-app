@php
    $etiquetas = [
        'pendiente' => 'Pendiente',
        'preparando' => 'Preparando',
        'enviado' => 'En camino',
        'entregado' => 'Entregado',
        'cancelado' => 'Cancelado',
    ];
@endphp
<span class="insignia-estado estado-{{ $estado?->value ?? 'tienda' }}">{{ $estado ? $etiquetas[$estado->value] : 'Venta en tienda' }}</span>
