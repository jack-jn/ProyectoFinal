<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleEntregaTarea extends Model
{
    protected $table = 'detalle_entrega_tareas';

    protected $primaryKey = 'id_detalle_entrega';

    protected $fillable = [
        'id_tarea',
        'id_detalle_matricula',
        'video_evidencia',
        'estado_revision',
        'comentario_docente',
        'fecha_entrega',
        'fecha_revision',
    ];

    protected function casts(): array
    {
        return [
            'fecha_entrega' => 'datetime',
            'fecha_revision' => 'datetime',
        ];
    }

    public function tareaAsignada(): BelongsTo
    {
        return $this->belongsTo(TareaAsignada::class, 'id_tarea', 'id_tarea');
    }

    public function detalleMatricula(): BelongsTo
    {
        return $this->belongsTo(DetalleMatricula::class, 'id_detalle_matricula', 'id_detalle_matricula');
    }
}
