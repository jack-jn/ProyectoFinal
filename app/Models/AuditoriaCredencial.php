<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditoriaCredencial extends Model
{
    protected $table = 'auditoria_credenciales';

    protected $primaryKey = 'id_auditoria';

    protected $fillable = [
        'id_usuario_secretaria',
        'id_usuario_alumno',
        'accion',
        'fecha_accion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_accion' => 'datetime',
        ];
    }

    public function secretariaUsuario(): BelongsTo
    {
        return $this->belongsTo(UsuarioSistema::class, 'id_usuario_secretaria', 'id_usuario');
    }

    public function alumnoUsuario(): BelongsTo
    {
        return $this->belongsTo(UsuarioSistema::class, 'id_usuario_alumno', 'id_usuario');
    }
}
