<?php

namespace App\Livewire\Docente;

use App\Models\AuditoriaCredencial;
use App\Models\Curso;
use App\Models\DetalleMatricula;
use App\Models\Instrumento;
use App\Models\Matricula;
use App\Models\UsuarioSistema;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class AlumnosMatriculados extends Component
{
    use WithPagination;

    public string $busqueda = '';

    public string $filtroInstrumento = 'all';

    public string $filtroNivel = 'all';

    public string $filtroCurso = 'all';

    public string $filtroEstadoCuenta = 'all';

    /**
     * Contraseñas descifradas bajo demanda por id_usuario.
     * Solo se puebla cuando el docente presiona "Mostrar" o "Restablecer".
     *
     * @var array<int, string>
     */
    #[Locked]
    public array $passwordsVisibles = [];

    public ?string $toastMensaje = null;

    public string $toastTipo = 'success';

    public function updatedBusqueda(): void
    {
        $this->resetPage('alumnosPage');
    }

    public function updatedFiltroInstrumento(): void
    {
        $this->filtroCurso = 'all';
        $this->resetPage('alumnosPage');
    }

    public function updatedFiltroNivel(): void
    {
        $this->resetPage('alumnosPage');
    }

    public function updatedFiltroCurso(): void
    {
        $this->resetPage('alumnosPage');
    }

    public function updatedFiltroEstadoCuenta(): void
    {
        $this->resetPage('alumnosPage');
    }

    public function seleccionarInstrumento(string $instrumento): void
    {
        $this->filtroInstrumento = $instrumento;
        $this->filtroCurso = 'all';
        $this->resetPage('alumnosPage');
    }

    public function seleccionarCurso(string $idCurso): void
    {
        $this->filtroCurso = $this->filtroCurso === $idCurso ? 'all' : $idCurso;
        $this->resetPage('alumnosPage');
    }

    public function limpiarFiltros(): void
    {
        $this->busqueda = '';
        $this->filtroInstrumento = 'all';
        $this->filtroNivel = 'all';
        $this->filtroCurso = 'all';
        $this->filtroEstadoCuenta = 'all';
        $this->resetPage('alumnosPage');
    }

    public function alternarPasswordAlumno(int $idUsuarioAlumno): void
    {
        $this->autorizarSoloDocente();

        if (isset($this->passwordsVisibles[$idUsuarioAlumno])) {
            unset($this->passwordsVisibles[$idUsuarioAlumno]);

            return;
        }

        $usuarioAlumno = UsuarioSistema::query()
            ->where('id_usuario', $idUsuarioAlumno)
            ->where('rol', 'alumno')
            ->firstOrFail();

        if (! $usuarioAlumno->password_temporal) {
            $this->mostrarToast('Este alumno no tiene una contraseña visible registrada.', 'error');

            return;
        }

        try {
            $plain = Crypt::decryptString($usuarioAlumno->password_temporal);
        } catch (\Throwable) {
            $this->mostrarToast('No se pudo descifrar la contraseña del alumno.', 'error');

            return;
        }

        $this->passwordsVisibles[$idUsuarioAlumno] = $plain;
        $this->registrarAuditoria($usuarioAlumno->id_usuario, 'ver');
    }

    public function restablecerPasswordAlumno(int $idUsuarioAlumno): void
    {
        $this->autorizarSoloDocente();

        $usuarioAlumno = UsuarioSistema::query()
            ->where('id_usuario', $idUsuarioAlumno)
            ->where('rol', 'alumno')
            ->firstOrFail();

        $nuevaClave = Str::password(10);

        DB::transaction(function () use ($usuarioAlumno, $nuevaClave): void {
            $usuarioAlumno->update([
                'contrasena' => Hash::make($nuevaClave),
                'password_temporal' => Crypt::encryptString($nuevaClave),
                'debe_cambiar_contrasena' => true,
            ]);

            $this->registrarAuditoria($usuarioAlumno->id_usuario, 'restablecer');
        });

        $this->passwordsVisibles[$idUsuarioAlumno] = $nuevaClave;
        $this->mostrarToast('Contraseña restablecida correctamente', 'success');
    }

    public function cerrarToast(): void
    {
        $this->toastMensaje = null;
    }

    private function mostrarToast(string $mensaje, string $tipo = 'success'): void
    {
        $this->toastMensaje = $mensaje;
        $this->toastTipo = $tipo;
    }

    private function autorizarSoloDocente(): void
    {
        /** @var UsuarioSistema|null $usuario */
        $usuario = Auth::user();
        abort_unless(
            $usuario && $usuario->rol === 'docente' && $usuario->estado_cuenta === 'Activo',
            403
        );
    }

    private function registrarAuditoria(int $idUsuarioAlumno, string $accion): void
    {
        /** @var UsuarioSistema $usuarioActual */
        $usuarioActual = Auth::user();

        AuditoriaCredencial::query()->create([
            'id_usuario_secretaria' => $usuarioActual->id_usuario,
            'id_usuario_alumno' => $idUsuarioAlumno,
            'accion' => $accion,
            'fecha_accion' => now(),
        ]);
    }

    public function render(): View
    {
        $this->autorizarSoloDocente();

        $instrumentos = Instrumento::query()
            ->whereHas('cursos')
            ->orderBy('id_instrumento')
            ->get();

        $cursosDisponibles = Curso::query()
            ->with('instrumento')
            ->withCount([
                'detalleMatriculas as alumnos_activos_count' => fn ($q) => $q->where('estado_acceso', 'Activo'),
            ])
            ->when($this->filtroInstrumento !== 'all', function ($q): void {
                $q->whereHas('instrumento', fn ($qi) => $qi->where('nombre_instrumento', $this->filtroInstrumento));
            })
            ->orderBy('id_curso')
            ->get();

        $query = Matricula::query()
            ->with([
                'alumno.persona.usuarioSistema',
                'detalleMatriculas.curso.instrumento',
                'pagos',
            ])
            ->where(function ($q): void {
                $q->where('estado_matricula', 'Confirmada')
                    ->orWhereHas('pagos', fn ($qp) => $qp->where('estado_validacion', 'Validado'))
                    ->orWhereHas('detalleMatriculas', fn ($qd) => $qd->where('estado_acceso', 'Activo'));
            });

        if ($this->filtroInstrumento !== 'all') {
            $query->whereHas('detalleMatriculas.curso.instrumento', function ($q): void {
                $q->where('nombre_instrumento', $this->filtroInstrumento);
            });
        }

        if ($this->filtroNivel !== 'all') {
            $nivel = $this->filtroNivel;
            $query->whereHas('detalleMatriculas.curso', function ($q) use ($nivel): void {
                $q->where('nivel', $nivel);
            });
        }

        if ($this->filtroCurso !== 'all' && ctype_digit($this->filtroCurso)) {
            $idCurso = (int) $this->filtroCurso;
            $query->whereHas('detalleMatriculas', function ($q) use ($idCurso): void {
                $q->where('id_curso', $idCurso);
            });
        }

        if ($this->filtroEstadoCuenta !== 'all') {
            if ($this->filtroEstadoCuenta === 'Sin cuenta') {
                $query->whereDoesntHave('alumno.persona.usuarioSistema');
            } else {
                $query->whereHas('alumno.persona.usuarioSistema', function ($q): void {
                    $q->where('estado_cuenta', $this->filtroEstadoCuenta);
                });
            }
        }

        $termino = trim($this->busqueda);
        if ($termino !== '') {
            $like = '%'.$termino.'%';
            $query->where(function ($q) use ($like): void {
                $q->where('codigo_matricula', 'like', $like)
                    ->orWhereHas('alumno.persona', function ($qp) use ($like): void {
                        $qp->where('dni', 'like', $like)
                            ->orWhere('nombres', 'like', $like)
                            ->orWhere('apellido_paterno', 'like', $like)
                            ->orWhere('apellido_materno', 'like', $like)
                            ->orWhere('correo', 'like', $like)
                            ->orWhereRaw("CONCAT_WS(' ', nombres, apellido_paterno, apellido_materno) LIKE ?", [$like]);
                    });
            });
        }

        $matriculasPaginadas = $query
            ->orderByDesc('fecha_matricula')
            ->orderByDesc('id_matricula')
            ->paginate(10, ['*'], 'alumnosPage');

        $totalInscripcionesActivas = DetalleMatricula::query()
            ->where('estado_acceso', 'Activo')
            ->count();

        $totalCuentasActivas = UsuarioSistema::query()
            ->where('rol', 'alumno')
            ->where('estado_cuenta', 'Activo')
            ->count();

        $totalCambioPendiente = UsuarioSistema::query()
            ->where('rol', 'alumno')
            ->where('debe_cambiar_contrasena', true)
            ->count();

        return view('livewire.docente.alumnos-matriculados', [
            'instrumentos' => $instrumentos,
            'cursosDisponibles' => $cursosDisponibles,
            'matriculasPaginadas' => $matriculasPaginadas,
            'totalInscripcionesActivas' => $totalInscripcionesActivas,
            'totalCuentasActivas' => $totalCuentasActivas,
            'totalCambioPendiente' => $totalCambioPendiente,
        ]);
    }
}
