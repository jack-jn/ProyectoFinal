<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Matricula extends Model
{
    protected $table = 'matriculas';

    protected $primaryKey = 'id_matricula';

    protected $fillable = [
        'id_alumno',
        'codigo_matricula',
        'fecha_matricula',
        'monto_total',
        'estado_matricula',
    ];

    protected function casts(): array
    {
        return [
            'fecha_matricula' => 'datetime',
            'monto_total' => 'decimal:2',
        ];
    }

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class, 'id_alumno', 'id_alumno');
    }

    public function detalleMatriculas(): HasMany
    {
        return $this->hasMany(DetalleMatricula::class, 'id_matricula', 'id_matricula');
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'id_matricula', 'id_matricula');
    }
}
