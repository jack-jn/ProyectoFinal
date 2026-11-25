<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InfoInstitucional extends Model
{
    protected $table = 'info_institucional';

    protected $primaryKey = 'id_info';

    protected $fillable = [
        'id_docente',
        'quienes_somos',
        'telefono_contacto',
        'correo_contacto',
        'direccion',
        'hora_apertura',
        'hora_cierre',
        'fecha_actualizado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_actualizado' => 'datetime',
        ];
    }

    public function docente(): BelongsTo
    {
        return $this->belongsTo(Docente::class, 'id_docente', 'id_docente');
    }
}
