<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pago extends Model
{
    protected $table = 'pagos';

    protected $primaryKey = 'id_pago';

    protected $fillable = [
        'id_matricula',
        'id_secretaria',
        'metodo_pago',
        'numero_operacion',
        'comprobante_url',
        'monto_pagado',
        'fecha_pago',
        'estado_validacion',
        'observacion',
        'fecha_validacion',
    ];

    protected function casts(): array
    {
        return [
            'monto_pagado' => 'decimal:2',
            'fecha_pago' => 'datetime',
            'fecha_validacion' => 'datetime',
        ];
    }

    public function matricula(): BelongsTo
    {
        return $this->belongsTo(Matricula::class, 'id_matricula', 'id_matricula');
    }

    public function secretaria(): BelongsTo
    {
        return $this->belongsTo(Secretaria::class, 'id_secretaria', 'id_secretaria');
    }
}
