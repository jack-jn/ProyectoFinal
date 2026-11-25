<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiaSemana extends Model
{
    protected $table = 'dias_semana';

    protected $primaryKey = 'id_dia';

    protected $fillable = [
        'nombre_dia',
    ];

    public function detalleCursoHorarios(): HasMany
    {
        return $this->hasMany(DetalleCursoHorario::class, 'id_dia', 'id_dia');
    }
}
