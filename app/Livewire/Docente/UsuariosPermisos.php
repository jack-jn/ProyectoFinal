<?php

namespace App\Livewire\Docente;

use App\Models\Alumno;
use App\Models\AuditoriaCredencial;
use App\Models\Curso;
use App\Models\Instrumento;
use App\Models\Persona;
use App\Models\Secretaria;
use App\Models\UsuarioSistema;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Spatie\Permission\PermissionRegistrar;

class UsuariosPermisos extends Component
{
    public string $pestanaActiva = 'secretaria';

    public string $busqueda = '';

    public string $filtroEstado = 'todos';

    public string $filtroInstrumento = 'all';

    public string $filtroNivel = 'all';

    public string $filtroCurso = 'all';

    public string $nombres = '';

    public string $apellido_paterno = '';

    public string $apellido_materno = '';

    public string $dni = '';

    public string $correo = '';

    public string $celular = '';

    public string $clave = '';

    public bool $mostrarClave = false;

    public string $rol = 'secretaria';

    /** @var list<string> */
    public array $permisosInicialesCrear = [];

    /** @var array<int, list<string>> */
    public array $permisosSeleccionados = [];

    /** @var array<int, string> */
    #[Locked]
    public array $passwordsVisibles = [];

    public bool $modalCredencialesAbierto = false;

    #[Locked]
    public string $credencialUsuarioNombre = '';

    #[Locked]
    public string $credencialUsuarioCorreo = '';

    #[Locked]
    public string $credencialUsuarioRol = '';

    #[Locked]
    public ?string $credencialClaveTemporal = null;

    public ?string $mensaje = null;

    public function boot(): void
    {
        $usuario = Auth::user();

        abort_unless($usuario?->rol === 'docente'
            && $usuario->estado_cuenta === 'Activo'
            && $usuario->can('gestionar_usuarios'), 403);
    }

    public function mount(): void
    {
        $this->permisosInicialesCrear = UsuarioSistema::permisosPorDefecto($this->rol);
        $this->permisosSeleccionados = $this->cuentas()
            ->mapWithKeys(fn (UsuarioSistema $usuario) => [
                $usuario->id_usuario => $usuario->permissions->pluck('name')->all(),
            ])->all();
    }

    public function cambiarPestana(string $pestana): void
    {
        if (in_array($pestana, ['secretaria', 'alumnos', 'crear'], true)) {
            $this->pestanaActiva = $pestana;
            $this->resetErrorBag();
        }
    }

    public function updatedFiltroInstrumento(): void
    {
        $this->filtroCurso = 'all';
    }

    public function seleccionarInstrumento(string $instrumento): void
    {
        $this->filtroInstrumento = $instrumento;
        $this->filtroCurso = 'all';
    }

    public function limpiarFiltros(): void
    {
        $this->busqueda = '';
        $this->filtroEstado = 'todos';
        $this->filtroInstrumento = 'all';
        $this->filtroNivel = 'all';
        $this->filtroCurso = 'all';
    }

    public function updatedRol(string $valor): void
    {
        $this->permisosInicialesCrear = UsuarioSistema::permisosPorDefecto($valor);
        $this->resetErrorBag('permisosInicialesCrear.*');
    }

    public function alternarMostrarClave(): void
    {
        $this->mostrarClave = ! $this->mostrarClave;
    }

    public function crearUsuario(): void
    {
        $this->mensaje = null;
        $this->correo = strtolower(trim($this->correo));
        $this->celular = preg_replace('/[\s-]+/', '', trim($this->celular)) ?? '';

        $datos = $this->validate([
            'nombres' => ['required', 'string', 'max:100'],
            'apellido_paterno' => ['required', 'string', 'max:100'],
            'apellido_materno' => ['required', 'string', 'max:100'],
            'dni' => ['required', 'digits:8', Rule::notIn(['00000000'])],
            'correo' => ['required', 'email', 'max:255', Rule::unique('usuarios_sistema', 'correo_usuario')],
            'celular' => ['nullable', 'digits:9'],
            'clave' => ['required', 'string', 'min:8', 'max:64'],
            'rol' => ['required', Rule::in(['secretaria', 'alumno'])],
            'permisosInicialesCrear' => ['present', 'array'],
            'permisosInicialesCrear.*' => ['string', Rule::in(UsuarioSistema::permisosPorDefecto($this->rol))],
        ], [
            'required' => 'Este campo es obligatorio.',
            'dni.digits' => 'El DNI debe tener exactamente 8 dígitos numéricos.',
            'dni.not_in' => 'Ingresa un DNI válido.',
            'correo.email' => 'Ingresa un correo electrónico válido.',
            'correo.unique' => 'Este correo ya se encuentra registrado en el sistema.',
            'celular.digits' => 'El celular debe tener exactamente 9 dígitos numéricos.',
            'clave.min' => 'La contraseña inicial debe tener al menos :min caracteres.',
            'max' => 'Este campo no puede superar los :max caracteres.',
            'rol.in' => 'Selecciona un rol válido (Secretaría o Estudiante).',
            'permisosInicialesCrear.*.in' => 'Uno de los permisos seleccionados no corresponde al rol.',
        ]);

        $permisosAsignar = array_values(array_intersect(
            UsuarioSistema::permisosPorDefecto($datos['rol']),
            $datos['permisosInicialesCrear']
        ));

        $usuario = DB::transaction(function () use ($datos, $permisosAsignar): UsuarioSistema {
            $persona = Persona::firstOrCreate(['dni' => $datos['dni']], [
                'nombres' => $datos['nombres'],
                'apellido_paterno' => $datos['apellido_paterno'],
                'apellido_materno' => $datos['apellido_materno'],
                'correo' => $datos['correo'],
                'celular' => $datos['celular'] !== '' ? $datos['celular'] : null,
            ]);

            if ($persona->usuarioSistema()->exists() || $persona->docente()->exists()) {
                throw ValidationException::withMessages(['dni' => 'El DNI ya está asociado a una cuenta o perfil docente.']);
            }

            $datosPersona = ['correo' => $datos['correo']];
            if ($datos['celular'] !== '') {
                $datosPersona['celular'] = $datos['celular'];
            }
            $persona->update($datosPersona);

            if ($datos['rol'] === 'secretaria') {
                Secretaria::firstOrCreate(['id_persona' => $persona->id_persona], [
                    'codigo_secretaria' => 'SEC-'.Str::upper(Str::random(10)),
                ]);
            } else {
                Alumno::firstOrCreate(['id_persona' => $persona->id_persona], [
                    'codigo_alumno' => 'ALU-'.Str::upper(Str::random(10)),
                ]);
            }

            $usuario = UsuarioSistema::create([
                'id_persona' => $persona->id_persona,
                'correo_usuario' => $datos['correo'],
                'contrasena' => $datos['clave'],
                'password_temporal' => Crypt::encryptString($datos['clave']),
                'rol' => $datos['rol'],
                'estado_cuenta' => 'Activo',
                'debe_cambiar_contrasena' => true,
            ]);
            $usuario->sincronizarAcceso($permisosAsignar);

            return $usuario;
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $rolCreado = $datos['rol'];
        $this->permisosSeleccionados[$usuario->id_usuario] = $usuario->permissions->pluck('name')->all();
        $this->reset('nombres', 'apellido_paterno', 'apellido_materno', 'dni', 'correo', 'celular', 'clave', 'rol', 'mostrarClave');
        $this->permisosInicialesCrear = UsuarioSistema::permisosPorDefecto($this->rol);
        $this->pestanaActiva = $rolCreado === 'alumno' ? 'alumnos' : 'secretaria';
        $this->mensaje = 'Usuario creado correctamente';
    }

    public function cambiarEstado(int $idUsuario): void
    {
        $this->mensaje = null;
        $usuario = $this->cuentaEditable($idUsuario);
        $usuario->update([
            'estado_cuenta' => $usuario->estado_cuenta === 'Activo' ? 'Inactivo' : 'Activo',
        ]);
        $this->mensaje = 'Estado de cuenta actualizado correctamente';
    }

    public function guardarPermisos(int $idUsuario): void
    {
        $this->mensaje = null;
        $usuario = $this->cuentaEditable($idUsuario);
        $campo = "permisosSeleccionados.{$idUsuario}";
        $this->validate([
            $campo => ['present', 'array'],
            "{$campo}.*" => ['string', Rule::in(UsuarioSistema::permisosPorDefecto($usuario->rol))],
        ], [
            "{$campo}.*.in" => 'Uno de los permisos seleccionados no corresponde al rol del usuario.',
        ]);

        DB::transaction(fn () => $usuario->syncPermissions($this->permisosSeleccionados[$idUsuario]));
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->mensaje = 'Permisos actualizados correctamente';
    }

    public function alternarPassword(int $idUsuario): void
    {
        $this->mensaje = null;

        if (isset($this->passwordsVisibles[$idUsuario])) {
            unset($this->passwordsVisibles[$idUsuario]);

            return;
        }

        $usuario = $this->cuentaEditable($idUsuario);
        if (! $usuario->password_temporal) {
            $this->mensaje = 'Este usuario no tiene una contraseña temporal visible.';

            return;
        }

        try {
            $plain = Crypt::decryptString($usuario->password_temporal);
        } catch (\Throwable) {
            $this->mensaje = 'No se pudo descifrar la contraseña del usuario.';

            return;
        }

        $this->passwordsVisibles[$idUsuario] = $plain;

        /** @var UsuarioSistema $usuarioActual */
        $usuarioActual = Auth::user();
        AuditoriaCredencial::query()->create([
            'id_usuario_secretaria' => $usuarioActual->id_usuario,
            'id_usuario_alumno' => $usuario->id_usuario,
            'accion' => 'ver',
            'fecha_accion' => now(),
        ]);
    }

    public function restablecerClave(int $idUsuario): void
    {
        $this->mensaje = null;
        $usuario = $this->cuentaEditable($idUsuario);
        $usuario->loadMissing('persona');
        $nuevaClave = Str::password(10);

        DB::transaction(function () use ($usuario, $nuevaClave): void {
            $usuario->update([
                'contrasena' => $nuevaClave,
                'password_temporal' => Crypt::encryptString($nuevaClave),
                'debe_cambiar_contrasena' => true,
            ]);

            /** @var UsuarioSistema $usuarioActual */
            $usuarioActual = Auth::user();
            AuditoriaCredencial::query()->create([
                'id_usuario_secretaria' => $usuarioActual->id_usuario,
                'id_usuario_alumno' => $usuario->id_usuario,
                'accion' => 'restablecer',
                'fecha_accion' => now(),
            ]);
        });

        $this->passwordsVisibles[$idUsuario] = $nuevaClave;
        $this->credencialUsuarioNombre = $usuario->persona?->nombre_completo ?? $usuario->correo_usuario;
        $this->credencialUsuarioCorreo = $usuario->correo_usuario;
        $this->credencialUsuarioRol = $usuario->rol === 'alumno' ? 'Estudiante' : 'Secretaría';
        $this->credencialClaveTemporal = $nuevaClave;
        $this->modalCredencialesAbierto = true;
        $this->mensaje = 'Contraseña restablecida correctamente';
    }

    public function cerrarModalCredenciales(): void
    {
        $this->modalCredencialesAbierto = false;
        $this->credencialUsuarioNombre = '';
        $this->credencialUsuarioCorreo = '';
        $this->credencialUsuarioRol = '';
        $this->credencialClaveTemporal = null;
    }

    private function cuentaEditable(int $idUsuario): UsuarioSistema
    {
        abort_if($idUsuario === Auth::id(), 403);

        return UsuarioSistema::whereIn('rol', ['secretaria', 'alumno'])->findOrFail($idUsuario);
    }

    /** @return Collection<int, UsuarioSistema> */
    private function cuentas(): Collection
    {
        return UsuarioSistema::whereIn('rol', ['secretaria', 'alumno'])
            ->with([
                'persona.alumno.matriculas.detalleMatriculas.curso.instrumento',
                'permissions',
            ])
            ->orderBy('correo_usuario')->get();
    }

    public function render(): View
    {
        $todasCuentas = $this->cuentas();

        $totalSecretaria = $todasCuentas->where('rol', 'secretaria')->count();
        $totalEstudiante = $todasCuentas->where('rol', 'alumno')->count();

        $instrumentos = Instrumento::query()
            ->whereHas('cursos')
            ->orderBy('id_instrumento')
            ->get();
        $cursosDisponibles = Curso::query()
            ->with('instrumento')
            ->when($this->filtroInstrumento !== 'all', function ($q): void {
                $q->whereHas('instrumento', fn ($qi) => $qi->where('nombre_instrumento', $this->filtroInstrumento));
            })
            ->orderBy('id_curso')
            ->get();

        $rolTab = $this->pestanaActiva === 'alumnos' ? 'alumno' : 'secretaria';

        $cuentasFiltradas = $todasCuentas->filter(function (UsuarioSistema $cuenta) use ($rolTab): bool {
            if ($cuenta->rol !== $rolTab) {
                return false;
            }

            if ($this->filtroEstado !== 'todos' && $cuenta->estado_cuenta !== $this->filtroEstado) {
                return false;
            }

            $matriculas = $cuenta->persona?->alumno?->matriculas ?? collect();
            $detalles = $matriculas->flatMap->detalleMatriculas;

            if ($rolTab === 'alumno' && $this->filtroInstrumento !== 'all') {
                $tieneInstrumento = $detalles->contains(
                    fn ($d) => $d->curso?->instrumento?->nombre_instrumento === $this->filtroInstrumento
                );
                if (! $tieneInstrumento) {
                    return false;
                }
            }

            if ($rolTab === 'alumno' && $this->filtroNivel !== 'all') {
                if (! $detalles->contains(fn ($d) => $d->curso?->nivel === $this->filtroNivel)) {
                    return false;
                }
            }

            if ($rolTab === 'alumno' && $this->filtroCurso !== 'all' && ctype_digit($this->filtroCurso)) {
                $idCurso = (int) $this->filtroCurso;
                if (! $detalles->contains(fn ($d) => (int) $d->id_curso === $idCurso)) {
                    return false;
                }
            }

            $q = mb_strtolower(trim($this->busqueda));
            if ($q !== '') {
                $cursosTxt = $detalles->map(fn ($d) => $d->curso?->nombre_curso)->implode(' ');
                $instTxt = $detalles->map(fn ($d) => $d->curso?->instrumento?->nombre_instrumento)->implode(' ');
                $codigosMatTxt = $matriculas->pluck('codigo_matricula')->implode(' ');

                $haystack = mb_strtolower(
                    ($cuenta->persona?->nombre_completo ?? '').' '.
                    ($cuenta->persona?->dni ?? '').' '.
                    $cuenta->correo_usuario.' '.
                    $cursosTxt.' '.
                    $instTxt.' '.
                    $codigosMatTxt
                );

                if (! str_contains($haystack, $q)) {
                    return false;
                }
            }

            return true;
        });

        return view('livewire.docente.usuarios-permisos', [
            'cuentas' => $cuentasFiltradas,
            'totalSecretaria' => $totalSecretaria,
            'totalEstudiante' => $totalEstudiante,
            'instrumentos' => $instrumentos,
            'cursosDisponibles' => $cursosDisponibles,
        ]);
    }
}
