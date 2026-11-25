<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoRecurso extends Model
{
    protected $table = 'tipos_recurso';

    protected $primaryKey = 'id_tipo_recurso';

    protected $fillable = [
        'nombre_tipo',
    ];

    public function recursos(): HasMany
    {
        return $this->hasMany(Recurso::class, 'id_tipo_recurso', 'id_tipo_recurso');
    }
}
