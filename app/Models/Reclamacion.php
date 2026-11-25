<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reclamacion extends Model
{
    protected $table = 'reclamaciones';

    protected $primaryKey = 'id_reclamacion';

    protected $fillable = [
        'codigo',
        'tipo',
        'nombres',
        'apellidos',
        'dni',
        'telefono',
        'correo',
        'domicilio',
        'servicio',
        'monto_involucrado',
        'fecha_incidente',
        'descripcion',
        'pedido_consumidor',
        'estado',
        'respuesta',
        'atendido_por',
        'fecha_atencion',
    ];

    protected function casts(): array
    {
        return [
            'monto_involucrado' => 'decimal:2',
            'fecha_incidente' => 'date',
            'fecha_atencion' => 'datetime',
        ];
    }

    public static function generarSiguienteCodigo(): string
    {
        $ultimoId = (int) (self::query()->max('id_reclamacion') ?? 0);

        return 'REC-'.str_pad((string) ($ultimoId + 1), 4, '0', STR_PAD_LEFT);
    }

    public function atendidoPor(): BelongsTo
    {
        return $this->belongsTo(UsuarioSistema::class, 'atendido_por', 'id_usuario');
    }
}
