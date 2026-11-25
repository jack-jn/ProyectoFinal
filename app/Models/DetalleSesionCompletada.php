<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleSesionCompletada extends Model
{
    protected $table = 'detalle_sesion_completadas';

    protected $primaryKey = 'id_detalle_sesion';

    protected $fillable = [
        'id_detalle_matricula',
        'id_sesion',
        'fecha_completada',
    ];

    protected function casts(): array
    {
        return [
            'fecha_completada' => 'datetime',
        ];
    }

    public function detalleMatricula(): BelongsTo
    {
        return $this->belongsTo(DetalleMatricula::class, 'id_detalle_matricula', 'id_detalle_matricula');
    }

    public function sesion(): BelongsTo
    {
        return $this->belongsTo(Sesion::class, 'id_sesion', 'id_sesion');
    }
}
