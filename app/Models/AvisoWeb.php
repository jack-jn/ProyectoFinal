<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvisoWeb extends Model
{
    protected $table = 'avisos_web';

    protected $primaryKey = 'id_aviso';

    protected $fillable = [
        'id_docente',
        'titulo_aviso',
        'texto_aviso',
        'fecha_publicacion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_publicacion' => 'datetime',
        ];
    }

    public function docente(): BelongsTo
    {
        return $this->belongsTo(Docente::class, 'id_docente', 'id_docente');
    }
}
