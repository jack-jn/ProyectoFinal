<?php

namespace App\Livewire\Estudiante;

use App\Models\DetalleEntregaTarea;
use App\Models\DetalleMatricula;
use App\Models\DetalleSesionCompletada;
use App\Models\Sesion;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.estudiante')]
#[Title('Portal del Estudiante | Academia de Música Dunamys')]
class Aula extends Component
{
    public ?int $idDetalleMatriculaActiva = null;

    public string $vistaActiva = 'sesiones';

    public ?int $idSesionSeleccionada = null;

    public bool $accesoNoAutorizado = false;

    public string $video_evidencia = '';

    #[Locked]
    public ?int $idRecursoPreview = null;

    public ?string $mensajeExito = null;

    public function boot(): void
    {
        $usuario = Auth::user();
        abort_unless($usuario?->rol === 'alumno' && $usuario->estado_cuenta === 'Activo'
            && $usuario->can('aula_virtual'), 403);
    }

    public function mount(): void
    {
        $detallesActivos = $this->obtenerDetallesMatriculasActivas();
        $primero = $detallesActivos->first();

        if ($primero) {
            $this->idDetalleMatriculaActiva = $primero->id_detalle_matricula;
            $this->inicializarSesionPorDefecto($primero);
        }
    }

    public function cambiarCurso(int $idDetalleMatricula): void
    {
        $this->resetErrorBag();
        $this->mensajeExito = null;
        $this->accesoNoAutorizado = false;

        $detallesActivos = $this->obtenerDetallesMatriculasActivas();
        $detalle = $detallesActivos->firstWhere('id_detalle_matricula', $idDetalleMatricula);

        if (! $detalle) {
            $this->accesoNoAutorizado = true;

            return;
        }

        $this->idDetalleMatriculaActiva = $detalle->id_detalle_matricula;
        $this->vistaActiva = 'sesiones';
        $this->inicializarSesionPorDefecto($detalle);
    }

    public function cambiarVista(string $vista, ?int $idSesion = null): void
    {
        $this->resetErrorBag();
        $this->mensajeExito = null;
        $this->accesoNoAutorizado = false;

        if (in_array($vista, ['cursos', 'sesiones', 'tareas', 'detalle_entrega'], true)) {
            $this->vistaActiva = $vista;
        }

        if ($idSesion !== null) {
            $this->seleccionarSesion($idSesion);
        }
    }

    public function seleccionarSesion(int $idSesion): void
    {
        $this->resetErrorBag();
        $this->mensajeExito = null;

        $detalle = $this->obtenerDetalleActivoActual();
        if (! $detalle || ! $detalle->curso) {
            $this->accesoNoAutorizado = true;

            return;
        }

        $sesionPertenece = $detalle->curso->sesiones->contains('id_sesion', $idSesion);
        if (! $sesionPertenece) {
            $this->accesoNoAutorizado = true;

            return;
        }

        $this->accesoNoAutorizado = false;
        $this->idSesionSeleccionada = $idSesion;
        $this->video_evidencia = '';
    }

    public function marcarSesionCompletada(int $idSesion): void
    {
        $this->resetErrorBag();
        $this->mensajeExito = null;

        $detalle = $this->obtenerDetalleActivoActual();
        if (! $detalle || ! $detalle->curso) {
            $this->accesoNoAutorizado = true;

            return;
        }

        $sesion = $detalle->curso->sesiones->firstWhere('id_sesion', $idSesion);
        if (! $sesion) {
            $this->accesoNoAutorizado = true;

            return;
        }

        $estados = $this->calcularEstadosSesiones($detalle);
        if (($estados[$idSesion] ?? 'Bloqueada') === 'Bloqueada') {
            $this->addError('sesion', 'Debes completar la sesión anterior para acceder a esta.');

            return;
        }

        DetalleSesionCompletada::query()->updateOrCreate(
            [
                'id_detalle_matricula' => $detalle->id_detalle_matricula,
                'id_sesion' => $sesion->id_sesion,
            ],
            [
                'fecha_completada' => now(),
            ]
        );

        $this->mensajeExito = 'Cambios guardados correctamente';
    }

    public function entregarTarea(?int $idSesion = null): void
    {
        abort_unless(Auth::user()->can('entregar_tareas'), 403);
        $this->resetErrorBag();
        $this->mensajeExito = null;

        $detalle = $this->obtenerDetalleActivoActual();
        if (! $detalle || ! $detalle->curso) {
            $this->accesoNoAutorizado = true;

            return;
        }

        $targetSesionId = $idSesion ?? $this->idSesionSeleccionada;
        /** @var Sesion|null $sesion */
        $sesion = $detalle->curso->sesiones->firstWhere('id_sesion', $targetSesionId);

        if (! $sesion || ! $sesion->tareaAsignada) {
            $this->accesoNoAutorizado = true;

            return;
        }

        $estados = $this->calcularEstadosSesiones($detalle);
        if (($estados[$sesion->id_sesion] ?? 'Bloqueada') === 'Bloqueada') {
            $this->addError('video_evidencia', 'Debes completar la sesión anterior para entregar esta tarea.');

            return;
        }

        $url = trim($this->video_evidencia);
        if ($url === '') {
            $this->addError('video_evidencia', 'Este campo no puede quedar vacío');

            return;
        }

        if (! filter_var($url, FILTER_VALIDATE_URL) || ! preg_match('/^https?:\/\//i', $url)) {
            $this->addError('video_evidencia', 'Ingresa un enlace de video válido (YouTube o Google Drive).');

            return;
        }

        $tarea = $sesion->tareaAsignada;

        $entregaExistente = DetalleEntregaTarea::query()
            ->where('id_tarea', $tarea->id_tarea)
            ->where('id_detalle_matricula', $detalle->id_detalle_matricula)
            ->first();

        if ($entregaExistente && $entregaExistente->estado_revision === 'Aprobada') {
            $this->addError('video_evidencia', 'Una entrega aprobada queda cerrada y no admite cambios.');

            return;
        }

        DB::transaction(function () use ($entregaExistente, $tarea, $detalle, $sesion, $url): void {
            if ($entregaExistente) {
                $entregaExistente->update([
                    'video_evidencia' => $url,
                    'estado_revision' => 'Pendiente',
                    'comentario_docente' => null,
                    'fecha_entrega' => now(),
                    'fecha_revision' => null,
                ]);
            } else {
                DetalleEntregaTarea::query()->create([
                    'id_tarea' => $tarea->id_tarea,
                    'id_detalle_matricula' => $detalle->id_detalle_matricula,
                    'video_evidencia' => $url,
                    'estado_revision' => 'Pendiente',
                    'comentario_docente' => null,
                    'fecha_entrega' => now(),
                    'fecha_revision' => null,
                ]);
            }

            DetalleSesionCompletada::query()->updateOrCreate(
                [
                    'id_detalle_matricula' => $detalle->id_detalle_matricula,
                    'id_sesion' => $sesion->id_sesion,
                ],
                [
                    'fecha_completada' => now(),
                ]
            );
        });

        $this->video_evidencia = '';
        $this->idSesionSeleccionada = $sesion->id_sesion;
        $this->vistaActiva = 'detalle_entrega';
        $this->mensajeExito = 'Cambios guardados correctamente';
    }

    public function limpiarEnlaceTarea(): void
    {
        $this->resetErrorBag('video_evidencia');
        $this->video_evidencia = '';
    }

    public function abrirPreviewRecurso(int $idRecurso): void
    {
        abort_unless(Auth::user()->can('ver_recursos'), 403);
        $this->idRecursoPreview = $idRecurso;
    }

    public function cerrarPreviewRecurso(): void
    {
        $this->idRecursoPreview = null;
    }

    /**
     * @return Collection<int, DetalleMatricula>
     */
    private function obtenerDetallesMatriculasActivas(): Collection
    {
        $usuario = Auth::user();
        $idAlumno = $usuario?->persona?->alumno?->id_alumno;

        if (! $idAlumno) {
            return collect();
        }

        return DetalleMatricula::query()
            ->with([
                'curso.instrumento',
                'curso.docente.persona',
                'curso.sesiones' => fn ($q) => $q->orderBy('numero_orden'),
                'curso.sesiones.lecciones' => fn ($q) => $q->orderBy('numero_orden'),
                'curso.sesiones.lecciones.recursos.tipoRecurso',
                'curso.sesiones.tareaAsignada.recursos.tipoRecurso',
                'sesionesCompletadas',
                'entregasTareas',
            ])
            ->where('estado_acceso', 'Activo')
            ->whereHas('matricula', fn ($q) => $q->where('id_alumno', $idAlumno))
            ->orderBy('id_detalle_matricula')
            ->get();
    }

    private function obtenerDetalleActivoActual(): ?DetalleMatricula
    {
        $detalles = $this->obtenerDetallesMatriculasActivas();
        if ($detalles->isEmpty()) {
            return null;
        }

        return $detalles->firstWhere('id_detalle_matricula', $this->idDetalleMatriculaActiva) ?? $detalles->first();
    }

    private function inicializarSesionPorDefecto(DetalleMatricula $detalle): void
    {
        $estados = $this->calcularEstadosSesiones($detalle);
        $sesiones = $detalle->curso?->sesiones ?? collect();

        $enProceso = $sesiones->first(fn (Sesion $s) => ($estados[$s->id_sesion] ?? '') === 'En proceso');
        $primera = $enProceso ?? $sesiones->first();

        $this->idSesionSeleccionada = $primera?->id_sesion;
    }

    /**
     * @return array<int, string>
     */
    private function calcularEstadosSesiones(DetalleMatricula $detalle): array
    {
        $estados = [];
        $sesiones = $detalle->curso?->sesiones?->values() ?? collect();

        $sesionesCompletadasIds = $detalle->sesionesCompletadas
            ->pluck('id_sesion')
            ->all();

        $tareasEntregadasIds = $detalle->entregasTareas
            ->pluck('id_tarea')
            ->all();

        foreach ($sesiones as $index => $sesion) {
            $idTarea = $sesion->tareaAsignada?->id_tarea;
            $tieneEntrega = $idTarea !== null && in_array($idTarea, $tareasEntregadasIds, true);
            $marcadaCompletada = in_array($sesion->id_sesion, $sesionesCompletadasIds, true);

            if ($tieneEntrega || $marcadaCompletada) {
                $estados[$sesion->id_sesion] = 'Completada';
            } elseif ($index === 0) {
                $estados[$sesion->id_sesion] = 'En proceso';
            } else {
                $sesionAnterior = $sesiones[$index - 1];
                $anteriorCompletada = ($estados[$sesionAnterior->id_sesion] ?? '') === 'Completada';
                $estados[$sesion->id_sesion] = $anteriorCompletada ? 'En proceso' : 'Bloqueada';
            }
        }

        return $estados;
    }

    public function render(): View
    {
        $usuario = Auth::user();
        $persona = $usuario?->persona;
        $nombreAlumno = $persona
            ? trim($persona->nombres.' '.$persona->apellido_paterno)
            : 'Estudiante Dunamys';
        $inicialAlumno = mb_strtoupper(mb_substr($nombreAlumno, 0, 1));

        $detallesActivos = $this->obtenerDetallesMatriculasActivas();
        $detalleActivo = $detallesActivos->firstWhere('id_detalle_matricula', $this->idDetalleMatriculaActiva)
            ?? $detallesActivos->first();

        $cursoActivo = $detalleActivo?->curso;
        $sesiones = $cursoActivo?->sesiones ?? collect();
        $estadosSesiones = $detalleActivo ? $this->calcularEstadosSesiones($detalleActivo) : [];

        $sesionActiva = $sesiones->firstWhere('id_sesion', $this->idSesionSeleccionada)
            ?? $sesiones->first();

        $entregasPorTarea = $detalleActivo
            ? $detalleActivo->entregasTareas->keyBy('id_tarea')
            : collect();

        $totalSesiones = $sesiones->count();
        $sesionesCompletadasCount = collect($estadosSesiones)->filter(fn (string $st) => $st === 'Completada')->count();
        $porcentajeProgreso = $totalSesiones > 0
            ? (int) round(($sesionesCompletadasCount / $totalSesiones) * 100)
            : 0;

        $recursoPreview = $this->idRecursoPreview && Auth::user()->can('ver_recursos')
            && $sesionActiva && ($estadosSesiones[$sesionActiva->id_sesion] ?? null) !== 'Bloqueada'
            ? $sesionActiva->lecciones->flatMap->recursos->firstWhere('id_recurso', $this->idRecursoPreview)
            : null;

        $cursosEnProgreso = collect();
        $cursosConcluidos = collect();

        foreach ($detallesActivos as $det) {
            $sesionesDet = $det->curso?->sesiones ?? collect();
            $estadosDet = $this->calcularEstadosSesiones($det);
            $totalDet = $sesionesDet->count();
            $completadasDet = collect($estadosDet)->filter(fn (string $st) => $st === 'Completada')->count();
            $porcentajeDet = $totalDet > 0 ? (int) round(($completadasDet / $totalDet) * 100) : 0;
            $concluidoDet = $totalDet > 0 && $completadasDet === $totalDet;

            $item = [
                'detalle' => $det,
                'curso' => $det->curso,
                'instrumento' => $det->curso?->instrumento?->nombre_instrumento ?? 'General',
                'total' => $totalDet,
                'completadas' => $completadasDet,
                'porcentaje' => $porcentajeDet,
                'concluido' => $concluidoDet,
            ];

            if ($concluidoDet) {
                $cursosConcluidos->push($item);
            } else {
                $cursosEnProgreso->push($item);
            }
        }

        return view('livewire.estudiante.aula', [
            'nombreAlumno' => $nombreAlumno,
            'inicialAlumno' => $inicialAlumno,
            'detallesActivos' => $detallesActivos,
            'detalleActivo' => $detalleActivo,
            'cursoActivo' => $cursoActivo,
            'sesiones' => $sesiones,
            'estadosSesiones' => $estadosSesiones,
            'sesionActiva' => $sesionActiva,
            'entregasPorTarea' => $entregasPorTarea,
            'totalSesiones' => $totalSesiones,
            'sesionesCompletadasCount' => $sesionesCompletadasCount,
            'porcentajeProgreso' => $porcentajeProgreso,
            'recursoPreview' => $recursoPreview,
            'cursosEnProgresoPorInstrumento' => $cursosEnProgreso->groupBy('instrumento'),
            'cursosConcluidos' => $cursosConcluidos,
        ]);
    }
}
