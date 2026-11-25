<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TareaAsignada extends Model
{
    protected $table = 'tareas_asignadas';

    protected $primaryKey = 'id_tarea';

    protected $fillable = [
        'id_sesion',
        'titulo_tarea',
        'descripcion',
        'fecha_limite',
    ];

    protected function casts(): array
    {
        return [
            'fecha_limite' => 'date',
        ];
    }

    public function sesion(): BelongsTo
    {
        return $this->belongsTo(Sesion::class, 'id_sesion', 'id_sesion');
    }

    public function recursos(): HasMany
    {
        return $this->hasMany(Recurso::class, 'id_tarea', 'id_tarea');
    }

    public function entregas(): HasMany
    {
        return $this->hasMany(DetalleEntregaTarea::class, 'id_tarea', 'id_tarea');
    }
}
