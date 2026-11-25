<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Leccion extends Model
{
    protected $table = 'lecciones';

    protected $primaryKey = 'id_leccion';

    protected $fillable = [
        'id_sesion',
        'codigo_leccion',
        'numero_orden',
        'titulo_leccion',
        'descripcion',
    ];

    protected function casts(): array
    {
        return [
            'numero_orden' => 'integer',
        ];
    }

    public function sesion(): BelongsTo
    {
        return $this->belongsTo(Sesion::class, 'id_sesion', 'id_sesion');
    }

    public function recursos(): HasMany
    {
        return $this->hasMany(Recurso::class, 'id_leccion', 'id_leccion');
    }
}
