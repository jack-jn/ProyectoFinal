<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DetalleMatricula extends Model
{
    protected $table = 'detalle_matriculas';

    protected $primaryKey = 'id_detalle_matricula';

    protected $fillable = [
        'id_matricula',
        'id_curso',
        'fecha_inscripcion',
        'precio_cobrado',
        'estado_acceso',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inscripcion' => 'datetime',
            'precio_cobrado' => 'decimal:2',
        ];
    }

    public function matricula(): BelongsTo
    {
        return $this->belongsTo(Matricula::class, 'id_matricula', 'id_matricula');
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'id_curso', 'id_curso');
    }

    public function sesionesCompletadas(): HasMany
    {
        return $this->hasMany(DetalleSesionCompletada::class, 'id_detalle_matricula', 'id_detalle_matricula');
    }

    public function entregasTareas(): HasMany
    {
        return $this->hasMany(DetalleEntregaTarea::class, 'id_detalle_matricula', 'id_detalle_matricula');
    }
}
