<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Docente extends Model
{
    protected $table = 'docentes';

    protected $primaryKey = 'id_docente';

    protected $fillable = [
        'id_persona',
        'especialidad',
    ];

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }

    public function cursos(): HasMany
    {
        return $this->hasMany(Curso::class, 'id_docente', 'id_docente');
    }

    public function infoInstitucional(): HasOne
    {
        return $this->hasOne(InfoInstitucional::class, 'id_docente', 'id_docente');
    }

    public function avisosWeb(): HasMany
    {
        return $this->hasMany(AvisoWeb::class, 'id_docente', 'id_docente');
    }

    public function galeriaEventos(): HasMany
    {
        return $this->hasMany(GaleriaEvento::class, 'id_docente', 'id_docente');
    }
}
