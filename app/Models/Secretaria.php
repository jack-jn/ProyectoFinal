<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Secretaria extends Model
{
    protected $table = 'secretarias';

    protected $primaryKey = 'id_secretaria';

    protected $fillable = [
        'id_persona',
        'codigo_secretaria',
        'turno_atencion',
    ];

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }

    public function pagosValidados(): HasMany
    {
        return $this->hasMany(Pago::class, 'id_secretaria', 'id_secretaria');
    }
}
