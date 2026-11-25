<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleCursoHorario extends Model
{
    protected $table = 'detalle_curso_horarios';

    protected $primaryKey = 'id_detalle_horario';

    protected $fillable = [
        'id_curso',
        'id_dia',
        'hora_inicio',
        'hora_fin',
    ];

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'id_curso', 'id_curso');
    }

    public function diaSemana(): BelongsTo
    {
        return $this->belongsTo(DiaSemana::class, 'id_dia', 'id_dia');
    }
}
