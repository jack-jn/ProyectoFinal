<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Instrumento extends Model
{
    protected $table = 'instrumentos';

    protected $primaryKey = 'id_instrumento';

    protected $fillable = [
        'nombre_instrumento',
        'icono',
    ];

    public function cursos(): HasMany
    {
        return $this->hasMany(Curso::class, 'id_instrumento', 'id_instrumento');
    }
}
