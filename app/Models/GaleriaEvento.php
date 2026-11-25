<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GaleriaEvento extends Model
{
    protected $table = 'galeria_eventos';

    protected $primaryKey = 'id_galeria';

    protected $fillable = [
        'id_docente',
        'titulo_evento',
        'tipo_multimedia',
        'archivo_url',
    ];

    public function docente(): BelongsTo
    {
        return $this->belongsTo(Docente::class, 'id_docente', 'id_docente');
    }
}
