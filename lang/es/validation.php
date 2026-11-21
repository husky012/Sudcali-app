<?php

/*
| Mensajes de validación en español para las reglas que usa Sudcali.
| Cualquier regla no listada aquí cae al inglés (APP_FALLBACK_LOCALE).
*/

return [
    'accepted' => 'Debes aceptar :attribute.',
    'after_or_equal' => ':Attribute debe ser una fecha igual o posterior a :date.',
    'alpha_num' => ':Attribute solo puede contener letras y números.',
    'array' => ':Attribute debe ser una lista.',
    'before_or_equal' => ':Attribute debe ser una fecha igual o anterior a :date.',
    'boolean' => ':Attribute debe ser verdadero o falso.',
    'confirmed' => 'La confirmación de :attribute no coincide.',
    'date' => ':Attribute no es una fecha válida.',
    'decimal' => ':Attribute debe tener :decimal decimales.',
    'digits' => ':Attribute debe tener :digits dígitos.',
    'distinct' => ':Attribute tiene un valor duplicado.',
    'email' => ':Attribute debe ser un correo válido.',
    'enum' => 'El valor elegido en :attribute no es válido.',
    'exists' => 'El valor elegido en :attribute no existe.',
    'file' => ':Attribute debe ser un archivo.',
    'image' => ':Attribute debe ser una imagen.',
    'in' => 'El valor elegido en :attribute no es válido.',
    'integer' => ':Attribute debe ser un número entero.',
    'max' => [
        'array' => ':Attribute no puede tener más de :max elementos.',
        'file' => ':Attribute no puede pesar más de :max kilobytes.',
        'numeric' => ':Attribute no puede ser mayor que :max.',
        'string' => ':Attribute no puede tener más de :max caracteres.',
    ],
    'min' => [
        'array' => ':Attribute debe tener al menos :min elementos.',
        'file' => ':Attribute debe pesar al menos :min kilobytes.',
        'numeric' => ':Attribute debe ser al menos :min.',
        'string' => ':Attribute debe tener al menos :min caracteres.',
    ],
    'numeric' => ':Attribute debe ser un número.',
    'password' => [
        'letters' => ':Attribute debe contener al menos una letra.',
        'mixed' => ':Attribute debe contener mayúsculas y minúsculas.',
        'numbers' => ':Attribute debe contener al menos un número.',
        'symbols' => ':Attribute debe contener al menos un símbolo.',
        'uncompromised' => ':Attribute apareció en una filtración de datos. Elige otra.',
    ],
    'prohibited' => ':Attribute no está permitido.',
    'required' => ':Attribute es obligatorio.',
    'required_if' => ':Attribute es obligatorio cuando :other es :value.',
    'string' => ':Attribute debe ser texto.',
    'unique' => 'Ya existe un registro con ese :attribute.',
    'uploaded' => 'No se pudo subir :attribute.',

    /*
    | Nombres legibles de los campos.
    */
    'attributes' => [
        'nombre' => 'nombre',
        'apellido_paterno' => 'apellido paterno',
        'apellido_materno' => 'apellido materno',
        'correo' => 'correo',
        'password' => 'contraseña',
        'telefono' => 'teléfono',
        'puesto' => 'puesto',
        'sucursal_id' => 'sucursal',
        'fecha_contratacion' => 'fecha de contratación',
        'foto' => 'foto',
        'categoria_id' => 'categoría',
        'titulo' => 'nombre del producto',
        'descripcion' => 'descripción',
        'precio' => 'precio',
        'imagen' => 'imagen',
        'talla' => 'talla',
        'color' => 'color',
        'codigo_barras' => 'código de barras',
        'stock' => 'existencias',
        'cantidad' => 'cantidad',
        'operacion' => 'operación',
        'monto_inicial' => 'fondo inicial',
        'monto_final_real' => 'efectivo contado',
        'monto_recibido' => 'efectivo recibido',
        'metodo_pago' => 'método de pago',
        'articulos' => 'artículos',
    ],
];
