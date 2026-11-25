<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Persona extends Model
{
    protected $table = 'personas';

    protected $primaryKey = 'id_persona';

    protected $fillable = [
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'dni',
        'fecha_nacimiento',
        'correo',
        'celular',
    ];

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
        ];
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombres} {$this->apellido_paterno} {$this->apellido_materno}");
    }

    public function usuarioSistema(): HasOne
    {
        return $this->hasOne(UsuarioSistema::class, 'id_persona', 'id_persona');
    }

    public function docente(): HasOne
    {
        return $this->hasOne(Docente::class, 'id_persona', 'id_persona');
    }

    public function secretaria(): HasOne
    {
        return $this->hasOne(Secretaria::class, 'id_persona', 'id_persona');
    }

    public function apoderado(): HasOne
    {
        return $this->hasOne(Apoderado::class, 'id_persona', 'id_persona');
    }

    public function alumno(): HasOne
    {
        return $this->hasOne(Alumno::class, 'id_persona', 'id_persona');
    }
}
