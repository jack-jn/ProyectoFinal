<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Sesion extends Model
{
    protected $table = 'sesiones';

    protected $primaryKey = 'id_sesion';

    protected $fillable = [
        'id_curso',
        'numero_orden',
        'titulo_sesion',
    ];

    protected function casts(): array
    {
        return [
            'numero_orden' => 'integer',
        ];
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'id_curso', 'id_curso');
    }

    public function lecciones(): HasMany
    {
        return $this->hasMany(Leccion::class, 'id_sesion', 'id_sesion')->orderBy('numero_orden');
    }

    public function tareaAsignada(): HasOne
    {
        return $this->hasOne(TareaAsignada::class, 'id_sesion', 'id_sesion');
    }

    public function completadas(): HasMany
    {
        return $this->hasMany(DetalleSesionCompletada::class, 'id_sesion', 'id_sesion');
    }
}
