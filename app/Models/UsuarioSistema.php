<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class UsuarioSistema extends Authenticatable
{
    use HasRoles, Notifiable;

    protected string $guard_name = 'web';

    public const PERMISOS_DOCENTE = [
        'gestionar_cursos' => 'Gestionar cursos',
        'gestionar_alumnos' => 'Gestionar alumnos matriculados',
        'gestionar_web' => 'Gestionar web institucional',
        'gestionar_usuarios' => 'Gestionar usuarios y permisos',
        'atender_reclamaciones' => 'Atender reclamaciones',
    ];

    public const PERMISOS_SECRETARIA = [
        'resumen' => 'Resumen',
        'pendientes' => 'Pagos pendientes',
        'historial' => 'Historial de pagos',
        'acceso' => 'Pago aprobado y acceso',
        'validar_observar' => 'Validar/Observar pagos',
        'atender_reclamaciones' => 'Atender reclamaciones',
    ];

    public const PERMISOS_ALUMNO = [
        'aula_virtual' => 'Aula virtual',
        'ver_recursos' => 'Ver recursos',
        'entregar_tareas' => 'Entregar tareas',
    ];

    protected $table = 'usuarios_sistema';

    protected $primaryKey = 'id_usuario';

    protected $fillable = [
        'id_persona',
        'correo_usuario',
        'contrasena',
        'password_temporal',
        'rol',
        'estado_cuenta',
        'debe_cambiar_contrasena',
        'permisos',
        'correo_enviado_at',
    ];

    protected $hidden = [
        'contrasena',
        'password_temporal',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'contrasena' => 'hashed',
            'debe_cambiar_contrasena' => 'boolean',
            'permisos' => 'array',
            'correo_enviado_at' => 'datetime',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function permisosPorDefecto(string $rol): array
    {
        return match ($rol) {
            'secretaria' => array_keys(self::PERMISOS_SECRETARIA),
            'alumno' => array_keys(self::PERMISOS_ALUMNO),
            'docente' => array_keys(self::PERMISOS_DOCENTE),
            default => [],
        };
    }

    /**
     * @return array<int, string>
     */
    public function permisosIniciales(): array
    {
        if (is_array($this->permisos)) {
            return array_values(array_intersect(self::permisosPorDefecto($this->rol), $this->permisos));
        }

        return self::permisosPorDefecto($this->rol);
    }

    /** @param list<string> $permisos */
    public function sincronizarAcceso(array $permisos): void
    {
        $this->syncRoles([$this->rol]);
        $this->syncPermissions($permisos);
    }

    public function getAuthPassword(): string
    {
        return $this->contrasena;
    }

    public function getAuthPasswordName(): string
    {
        return 'contrasena';
    }

    public function getEmailForPasswordReset(): string
    {
        return $this->correo_usuario;
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }
}
