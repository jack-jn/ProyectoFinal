<?php

namespace App\Livewire\Docente;

use App\Models\Curso;
use App\Models\Docente;
use App\Models\Instrumento;
use App\Models\UsuarioSistema;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class Cursos extends Component
{
    use WithFileUploads;

    public string $busqueda = '';

    public string $filtroInstrumento = 'all';

    public string $filtroEstado = 'all';

    // Modal Crear / Editar Curso
    public bool $modalAbierto = false;

    public ?int $editandoCursoId = null;

    public string $codigo_curso = '';

    public string $nombre_curso = '';

    public string $instrumento_nombre = 'Teclado';

    public string $nivel = 'Básico';

    public string $costo = '';

    public string $cupos = '';

    public bool $estado_habilitado = true;

    public $imagen_curso = null;

    public int $matriculadosActuales = 0;

    // Nuevo instrumento rápido
    public bool $mostrarNuevoInstrumento = false;

    public string $nuevo_instrumento_nombre = '';

    public string $nuevo_instrumento_icono = 'i-inst-drum';

    // Toast
    public ?string $toastMensaje = null;

    public string $toastTipo = 'success';

    public function limpiarFiltros(): void
    {
        $this->busqueda = '';
        $this->filtroInstrumento = 'all';
        $this->filtroEstado = 'all';
    }

    public function abrirModalCrear(): void
    {
        $this->autorizarDocente();
        $this->resetErrorBag();
        $this->editandoCursoId = null;
        $this->codigo_curso = $this->generarSiguienteCodigoCurso();
        $this->nombre_curso = '';
        $primerInstrumento = Instrumento::query()->orderBy('id_instrumento')->first();
        $this->instrumento_nombre = $primerInstrumento?->nombre_instrumento ?? 'Teclado';
        $this->nivel = 'Básico';
        $this->costo = '';
        $this->cupos = '';
        $this->estado_habilitado = true;
        $this->imagen_curso = null;
        $this->matriculadosActuales = 0;
        $this->mostrarNuevoInstrumento = false;
        $this->modalAbierto = true;
    }

    public function abrirModalEditar(int $idCurso): void
    {
        $this->autorizarDocente();
        $this->resetErrorBag();

        $curso = Curso::query()
            ->with('instrumento')
            ->withCount([
                'detalleMatriculas as matriculados_activos_count' => fn ($q) => $q->where('estado_acceso', 'Activo'),
            ])
            ->findOrFail($idCurso);

        $this->editandoCursoId = $curso->id_curso;
        $this->codigo_curso = $curso->codigo_curso;
        $this->nombre_curso = $curso->nombre_curso;
        $this->instrumento_nombre = $curso->instrumento?->nombre_instrumento ?? 'Teclado';
        $this->nivel = $curso->nivel;
        $this->costo = (string) $curso->costo;
        $this->cupos = (string) $curso->cupos;
        $this->estado_habilitado = (bool) $curso->estado_habilitado;
        $this->imagen_curso = null;
        $this->matriculadosActuales = (int) $curso->matriculados_activos_count;

        $this->modalAbierto = true;
    }

    public function cerrarModal(): void
    {
        $this->modalAbierto = false;
        $this->resetErrorBag();
    }

    public function agregarInstrumento(): void
    {
        $this->autorizarDocente();
        $nombre = trim($this->nuevo_instrumento_nombre);
        if ($nombre === '') {
            $this->addError('nuevo_instrumento', 'Ingresa el nombre del instrumento');

            return;
        }
        if (mb_strlen($nombre) > 60) {
            $this->addError('nuevo_instrumento', 'El nombre del instrumento no puede superar 60 caracteres');

            return;
        }

        $nombreFormateado = mb_strtoupper(mb_substr($nombre, 0, 1)).mb_substr($nombre, 1);
        $instrumento = Instrumento::query()->firstOrCreate(
            ['nombre_instrumento' => $nombreFormateado],
            ['icono' => $this->nuevo_instrumento_icono ?: 'i-inst-note']
        );

        $this->instrumento_nombre = $instrumento->nombre_instrumento;
        $this->nuevo_instrumento_nombre = '';
        $this->mostrarNuevoInstrumento = false;
        $this->resetErrorBag('nuevo_instrumento');
        $this->mostrarToast('Cambios guardados correctamente');
    }

    public function guardarCurso(): void
    {
        $this->autorizarDocente();
        $this->resetErrorBag();

        $nombreLimpio = trim($this->nombre_curso);
        if ($nombreLimpio === '') {
            $this->addError('nombre_curso', 'Este campo es obligatorio');
        } elseif (mb_strlen($nombreLimpio) > 150) {
            $this->addError('nombre_curso', 'El nombre del curso no puede superar 150 caracteres');
        }

        $instrumento = Instrumento::query()->where('nombre_instrumento', $this->instrumento_nombre)->first();
        if (! $instrumento) {
            $this->addError('instrumento_nombre', 'Este campo es obligatorio');
        }

        if (! in_array($this->nivel, ['Básico', 'Intermedio', 'Avanzado'], true)) {
            $this->addError('nivel', 'Este campo es obligatorio');
        }

        if (trim($this->costo) === '') {
            $this->addError('costo', 'Este campo es obligatorio');
        } elseif (! is_numeric($this->costo) || (float) $this->costo < 0) {
            $this->addError('costo', 'Ingresa un costo válido mayor o igual a 0');
        }

        $cursoActual = $this->editandoCursoId
            ? Curso::query()->withCount([
                'detalleMatriculas as matriculados_activos_count' => fn ($q) => $q->where('estado_acceso', 'Activo'),
            ])->find($this->editandoCursoId)
            : null;

        if (trim($this->cupos) === '') {
            $this->addError('cupos', 'Este campo es obligatorio');
        } elseif (! ctype_digit(trim($this->cupos)) || (int) $this->cupos < 1) {
            $this->addError('cupos', 'Ingresa una cantidad entera mayor o igual a 1');
        } elseif ($cursoActual && (int) $this->cupos < (int) $cursoActual->matriculados_activos_count) {
            // Regla 2 de la sección 6 (RF022)
            $this->addError('cupos', 'Los cupos no pueden ser menores al número de alumnos matriculados');
        }

        $imagenUrl = $cursoActual?->imagen_url ?? $this->imagenPorDefectoInstrumento($this->instrumento_nombre);

        if ($this->imagen_curso) {
            $ext = strtolower((string) $this->imagen_curso->getClientOriginalExtension());
            $tamanoKb = $this->imagen_curso->getSize() / 1024;
            if (! in_array($ext, ['webp', 'jpg', 'jpeg', 'png'], true) || $tamanoKb > 2048) {
                $this->addError('imagen_curso', 'Formato o tamaño de archivo no permitido');
            } else {
                $ruta = $this->imagen_curso->store('cursos', 'public');
                $imagenUrl = 'storage/'.$ruta;
            }
        }

        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        $datosCurso = [
            'id_instrumento' => $instrumento->id_instrumento,
            'nombre_curso' => $nombreLimpio,
            'nivel' => $this->nivel,
            'modalidad' => 'Virtual',
            'costo' => (float) $this->costo,
            'cupos' => (int) $this->cupos,
            'imagen_url' => $imagenUrl,
            'estado_habilitado' => $this->estado_habilitado,
        ];

        if ($cursoActual) {
            $cursoActual->update($datosCurso);
        } else {
            Curso::query()->create($datosCurso + [
                'id_docente' => $this->obtenerIdDocenteActual(),
                'codigo_curso' => $this->generarSiguienteCodigoCurso(),
            ]);
        }

        $this->modalAbierto = false;
        $this->mostrarToast('Cambios guardados correctamente');
    }

    public function alternarEstadoCurso(int $idCurso): void
    {
        $this->autorizarDocente();
        $curso = Curso::query()->findOrFail($idCurso);
        $curso->update([
            'estado_habilitado' => ! $curso->estado_habilitado,
        ]);

        $this->mostrarToast('Cambios guardados correctamente', 'info');
    }

    public function eliminarCurso(int $idCurso): void
    {
        $this->autorizarDocente();
        $curso = Curso::query()->withCount('detalleMatriculas')->findOrFail($idCurso);

        // Regla 3 de la sección 6 (RF023): un curso con matriculados no se elimina; solo cambia estado_habilitado = false
        if ($curso->detalle_matriculas_count > 0) {
            $curso->update(['estado_habilitado' => false]);
            $this->mostrarToast(
                'El curso tiene alumnos matriculados; fue deshabilitado en lugar de eliminarse para conservar su acceso al aula.',
                'info'
            );

            return;
        }

        $curso->delete();
        $this->mostrarToast('Cambios guardados correctamente', 'info');
    }

    public function irAGestionAula(int $idCurso): void
    {
        $this->dispatch('abrir-aula-curso', idCurso: $idCurso);
    }

    public function cerrarToast(): void
    {
        $this->toastMensaje = null;
    }

    private function generarSiguienteCodigoCurso(): string
    {
        $codigos = Curso::query()->pluck('codigo_curso');
        $max = 0;
        foreach ($codigos as $cod) {
            if (preg_match('/C-(\d+)/', (string) $cod, $m)) {
                $max = max($max, (int) $m[1]);
            }
        }

        return 'C-'.str_pad((string) ($max + 1), 3, '0', STR_PAD_LEFT);
    }

    private function imagenPorDefectoInstrumento(string $instrumento): string
    {
        $mapa = [
            'Teclado' => 'assets/img/cursos/teclado.webp',
            'Piano' => 'assets/img/cursos/piano.webp',
            'Guitarra' => 'assets/img/cursos/guitarra.webp',
            'Violín' => 'assets/img/cursos/violin.webp',
            'Bajo Eléctrico' => 'assets/img/cursos/bajo-electrico.webp',
            'Batería' => 'assets/img/cursos/bateria.webp',
            'Canto' => 'assets/img/cursos/canto.webp',
        ];

        return $mapa[$instrumento] ?? 'assets/img/cursos/teclado.webp';
    }

    private function mostrarToast(string $mensaje, string $tipo = 'success'): void
    {
        $this->toastMensaje = $mensaje;
        $this->toastTipo = $tipo;
    }

    private function autorizarDocente(): void
    {
        /** @var UsuarioSistema|null $usuario */
        $usuario = Auth::user();
        abort_unless($usuario && $usuario->rol === 'docente' && $usuario->estado_cuenta === 'Activo', 403);
    }

    private function obtenerIdDocenteActual(): int
    {
        /** @var UsuarioSistema $usuario */
        $usuario = Auth::user();
        $docente = Docente::query()->where('id_persona', $usuario->id_persona)->first();
        if ($docente) {
            return $docente->id_docente;
        }

        return Docente::query()->first()?->id_docente
            ?? Docente::query()->create(['id_persona' => $usuario->id_persona, 'especialidad' => 'Música'])->id_docente;
    }

    public function render(): View
    {
        $todosLosCursos = Curso::query()
            ->with(['instrumento', 'docente.persona'])
            ->withCount([
                'detalleMatriculas as matriculados_activos_count' => fn ($q) => $q->where('estado_acceso', 'Activo'),
            ])
            ->orderBy('id_curso')
            ->get();

        $cursosFiltrados = $todosLosCursos->filter(function (Curso $curso): bool {
            if ($this->filtroInstrumento !== 'all' && $curso->instrumento?->nombre_instrumento !== $this->filtroInstrumento) {
                return false;
            }
            if ($this->filtroEstado === 'on' && ! $curso->estado_habilitado) {
                return false;
            }
            if ($this->filtroEstado === 'off' && $curso->estado_habilitado) {
                return false;
            }
            if (trim($this->busqueda) !== '') {
                $q = mb_strtolower(trim($this->busqueda));
                $haystack = mb_strtolower($curso->nombre_curso.' '.$curso->codigo_curso.' '.($curso->instrumento?->nombre_instrumento ?? ''));
                if (! str_contains($haystack, $q)) {
                    return false;
                }
            }

            return true;
        });

        $cursosHabilitados = $todosLosCursos->where('estado_habilitado', true);
        $totalMatriculados = $todosLosCursos->sum('matriculados_activos_count');
        $instrumentos = Instrumento::query()->orderBy('id_instrumento')->get();

        return view('livewire.docente.cursos', [
            'todosLosCursos' => $todosLosCursos,
            'cursosFiltrados' => $cursosFiltrados,
            'cursosHabilitados' => $cursosHabilitados,
            'totalMatriculados' => $totalMatriculados,
            'instrumentos' => $instrumentos,
        ]);
    }
}
