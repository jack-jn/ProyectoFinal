<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Apoderado extends Model
{
    protected $table = 'apoderados';

    protected $primaryKey = 'id_apoderado';

    protected $fillable = [
        'id_persona',
        'parentesco',
    ];

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }

    public function alumnos(): HasMany
    {
        return $this->hasMany(Alumno::class, 'id_apoderado', 'id_apoderado');
    }
}
