<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Curso extends Model
{
    protected $table = 'cursos';

    protected $primaryKey = 'id_curso';

    protected $fillable = [
        'id_docente',
        'id_instrumento',
        'codigo_curso',
        'nombre_curso',
        'nivel',
        'modalidad',
        'costo',
        'cupos',
        'imagen_url',
        'estado_habilitado',
    ];

    protected function casts(): array
    {
        return [
            'costo' => 'decimal:2',
            'cupos' => 'integer',
            'estado_habilitado' => 'boolean',
        ];
    }

    public function docente(): BelongsTo
    {
        return $this->belongsTo(Docente::class, 'id_docente', 'id_docente');
    }

    public function instrumento(): BelongsTo
    {
        return $this->belongsTo(Instrumento::class, 'id_instrumento', 'id_instrumento');
    }

    public function detalleHorarios(): HasMany
    {
        return $this->hasMany(DetalleCursoHorario::class, 'id_curso', 'id_curso');
    }

    public function detalleMatriculas(): HasMany
    {
        return $this->hasMany(DetalleMatricula::class, 'id_curso', 'id_curso');
    }

    public function sesiones(): HasMany
    {
        return $this->hasMany(Sesion::class, 'id_curso', 'id_curso')->orderBy('numero_orden');
    }

    public function getMatriculadosActivosAttribute(): int
    {
        if (array_key_exists('matriculados_activos_count', $this->attributes)) {
            return (int) $this->attributes['matriculados_activos_count'];
        }

        return $this->detalleMatriculas()->where('estado_acceso', 'Activo')->count();
    }

    public function getCuposDisponiblesAttribute(): int
    {
        return max(0, $this->cupos - $this->matriculados_activos);
    }
}
