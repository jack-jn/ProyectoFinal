<?php

namespace App\Livewire\Docente;

use App\Models\Curso;
use App\Models\Instrumento;
use App\Models\Leccion;
use App\Models\Recurso;
use App\Models\Sesion;
use App\Models\TareaAsignada;
use App\Models\TipoRecurso;
use App\Models\UsuarioSistema;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class Sesiones extends Component
{
    use WithFileUploads;

    public bool $modoWorkspace = false;

    public ?int $cursoSeleccionadoId = null;

    public ?int $sesionActivaId = null;

    public string $subPestana = 'sesiones'; // 'sesiones' (Lecciones) | 'tareas' (Tareas)

    // Modal Crear / Editar Sesión
    public bool $modalSesionAbierto = false;

    public ?int $editandoSesionId = null;

    public string $titulo_sesion = '';

    // Modal Crear / Editar Lección
    public bool $modalLeccionAbierto = false;

    #[Locked]
    public ?int $editandoLeccionId = null;

    public string $titulo_leccion = '';

    public string $descripcion_leccion = '';

    public string $url_video_leccion = '';

    public string $tipo_recurso_extra = 'Ninguno'; // 'Ninguno' | 'Partitura' | 'Enlace de referencia' | 'Ambos'

    public ?TemporaryUploadedFile $archivo_pdf = null;

    public string $titulo_pdf = '';

    public string $url_apoyo = '';

    public string $titulo_apoyo = '';

    #[Locked]
    public bool $tienePdfGuardado = false;

    #[Locked]
    public ?string $nombrePdfGuardado = null;

    #[Locked]
    public bool $tieneMp4Leccion = false;

    // Modal Crear / Editar Tarea
    public bool $modalTareaAbierto = false;

    public string $titulo_tarea = '';

    public string $descripcion_tarea = '';

    public string $fecha_limite_tarea = '';

    public string $url_video_tarea = '';

    // Toast
    public ?string $toastMensaje = null;

    public string $toastTipo = 'success';

    public function mount(?int $idCursoInicial = null): void
    {
        if ($idCursoInicial) {
            $this->seleccionarCurso($idCursoInicial);
        }
    }

    public function seleccionarCurso(int $idCurso): void
    {
        $this->cursoSeleccionadoId = $idCurso;
        $this->modoWorkspace = true;
        $primeraSesion = Sesion::query()
            ->where('id_curso', $idCurso)
            ->orderBy('numero_orden')
            ->first();
        $this->sesionActivaId = $primeraSesion?->id_sesion;
    }

    public function volverAlCatalogo(): void
    {
        $this->modoWorkspace = false;
    }

    public function seleccionarSesion(int $idSesion): void
    {
        $this->sesionActivaId = $idSesion;
    }

    public function cambiarSubPestana(string $tab): void
    {
        if (in_array($tab, ['sesiones', 'tareas'], true)) {
            $this->subPestana = $tab;
        }
    }

    // =====================================================
    // GESTIÓN DE SESIONES Y REORDENAMIENTO (RF025 - Regla 4)
    // =====================================================
    public function abrirModalCrearSesion(): void
    {
        $this->autorizarDocente();
        $this->resetErrorBag();
        $this->editandoSesionId = null;
        $this->titulo_sesion = '';
        $this->modalSesionAbierto = true;
    }

    public function abrirModalEditarSesion(int $idSesion): void
    {
        $this->autorizarDocente();
        $this->resetErrorBag();
        $sesion = Sesion::query()->findOrFail($idSesion);
        $this->editandoSesionId = $sesion->id_sesion;
        $this->titulo_sesion = $sesion->titulo_sesion;
        $this->modalSesionAbierto = true;
    }

    public function guardarSesion(): void
    {
        $this->autorizarDocente();
        $this->resetErrorBag('titulo_sesion');

        $titulo = trim($this->titulo_sesion);
        if ($titulo === '') {
            $this->addError('titulo_sesion', 'Este campo es obligatorio');

            return;
        }
        if (mb_strlen($titulo) > 150) {
            $this->addError('titulo_sesion', 'El título de la sesión no puede superar 150 caracteres');

            return;
        }

        if (! $this->cursoSeleccionadoId) {
            return;
        }

        if ($this->editandoSesionId) {
            $sesion = Sesion::query()->findOrFail($this->editandoSesionId);
            $sesion->update(['titulo_sesion' => $titulo]);
        } else {
            $maxOrden = (int) Sesion::query()
                ->where('id_curso', $this->cursoSeleccionadoId)
                ->max('numero_orden');

            $sesion = Sesion::query()->create([
                'id_curso' => $this->cursoSeleccionadoId,
                'numero_orden' => $maxOrden + 1,
                'titulo_sesion' => $titulo,
            ]);
            $this->sesionActivaId = $sesion->id_sesion;
        }

        $this->modalSesionAbierto = false;
        $this->mostrarToast('Cambios guardados correctamente');
    }

    public function eliminarSesion(int $idSesion): void
    {
        $this->autorizarDocente();
        $sesion = Sesion::query()->findOrFail($idSesion);
        $idCurso = $sesion->id_curso;
        $sesion->delete();

        // Reordenar números de orden restantes en transacción sin cambiar id_sesion
        $restantes = Sesion::query()
            ->where('id_curso', $idCurso)
            ->orderBy('numero_orden')
            ->pluck('id_sesion')
            ->all();

        $this->reordenarSesiones($restantes);

        if ($this->sesionActivaId === $idSesion) {
            $this->sesionActivaId = $restantes[0] ?? null;
        }

        $this->mostrarToast('Cambios guardados correctamente', 'info');
    }

    public function moverSesion(int $idSesion, int $direccion): void
    {
        $this->autorizarDocente();
        if (! $this->cursoSeleccionadoId) {
            return;
        }

        $ids = Sesion::query()
            ->where('id_curso', $this->cursoSeleccionadoId)
            ->orderBy('numero_orden')
            ->pluck('id_sesion')
            ->values()
            ->all();

        $idxActual = array_search($idSesion, $ids, true);
        if ($idxActual === false) {
            return;
        }

        $idxNuevo = $idxActual + $direccion;
        if ($idxNuevo < 0 || $idxNuevo >= count($ids)) {
            return;
        }

        $temp = $ids[$idxActual];
        $ids[$idxActual] = $ids[$idxNuevo];
        $ids[$idxNuevo] = $temp;

        $this->reordenarSesiones($ids);
    }

    /**
     * Regla 4 de la sección 6 (RF025): actualiza numero_orden sin cambiar id_sesion
     * dentro de una transacción asignando primero órdenes temporales negativos.
     *
     * @param  array<int, int>  $ordenIds
     */
    public function reordenarSesiones(array $ordenIds): void
    {
        $this->autorizarDocente();
        if (! $this->cursoSeleccionadoId || empty($ordenIds)) {
            return;
        }

        DB::transaction(function () use ($ordenIds): void {
            foreach (array_values($ordenIds) as $index => $idSesion) {
                Sesion::query()
                    ->where('id_curso', $this->cursoSeleccionadoId)
                    ->where('id_sesion', (int) $idSesion)
                    ->update(['numero_orden' => -1000 - $index]);
            }

            foreach (array_values($ordenIds) as $index => $idSesion) {
                Sesion::query()
                    ->where('id_curso', $this->cursoSeleccionadoId)
                    ->where('id_sesion', (int) $idSesion)
                    ->update(['numero_orden' => $index + 1]);
            }
        });

        $this->mostrarToast('Cambios guardados correctamente');
    }

    // =====================================================
    // GESTIÓN DE LECCIONES Y RECURSOS (RF024 - Regla 7)
    // =====================================================
    public function abrirModalCrearLeccion(): void
    {
        $this->autorizarDocente();
        $this->resetErrorBag();
        $this->reset('editandoLeccionId', 'titulo_leccion', 'descripcion_leccion', 'url_video_leccion', 'tieneMp4Leccion');
        $this->cargarApoyo(null, null);
        $this->modalTareaAbierto = false;
        $this->modalLeccionAbierto = true;
    }

    public function abrirModalEditarLeccion(int $idLeccion): void
    {
        $this->autorizarDocente();
        $this->resetErrorBag();

        $leccion = Leccion::query()->where('id_sesion', $this->sesionActivaId)->findOrFail($idLeccion);
        $recursos = $this->recursosFormulario($leccion);

        $this->editandoLeccionId = $leccion->id_leccion;
        $this->titulo_leccion = $leccion->titulo_leccion;
        $this->descripcion_leccion = $leccion->descripcion ?? '';
        $this->url_video_leccion = $recursos['video']?->url_recurso ?? '';
        $this->tieneMp4Leccion = $recursos['mp4'] && ! $recursos['video'];
        $this->cargarApoyo($recursos['pdf'], $recursos['apoyo']);
        $this->modalTareaAbierto = false;
        $this->modalLeccionAbierto = true;
    }

    public function guardarLeccion(): void
    {
        $this->autorizarDocente();
        if (! $this->sesionActivaId) {
            return;
        }

        $this->titulo_leccion = trim($this->titulo_leccion);
        $this->descripcion_leccion = trim($this->descripcion_leccion);

        $this->guardarConRecursos(function (Sesion $sesion, ?string &$archivoNuevo): void {
            $leccion = $this->editandoLeccionId ? $sesion->lecciones()->findOrFail($this->editandoLeccionId) : null;
            $recursos = $this->recursosFormulario($leccion);
            $datos = $this->validarFormulario('url_video_leccion', ! $recursos['mp4'] || (bool) $recursos['video'], [
                'titulo_leccion' => ['required', 'string', 'max:255'],
                'descripcion_leccion' => ['required', 'string'],
            ], (bool) $recursos['pdf']);

            $atributos = [
                'titulo_leccion' => $datos['titulo_leccion'],
                'descripcion' => $datos['descripcion_leccion'],
            ];

            if ($leccion) {
                $leccion->update($atributos);
            } else {
                $orden = (int) $sesion->lecciones()->reorder()->max('numero_orden') + 1;
                $leccion = $sesion->lecciones()->create($atributos + [
                    'codigo_leccion' => sprintf('LEC-%02d', $orden),
                    'numero_orden' => $orden,
                ]);
            }

            $this->sincronizarRecursos($leccion, $recursos, $datos['url_video_leccion'] ?? '', $archivoNuevo);
        });

        $this->modalLeccionAbierto = false;
        $this->mostrarToast('Cambios guardados correctamente');
    }

    public function eliminarLeccion(int $idLeccion): void
    {
        $this->autorizarDocente();
        Leccion::query()->whereKey($idLeccion)->delete();
        $this->mostrarToast('Cambios guardados correctamente', 'info');
    }

    // =====================================================
    // GESTIÓN DE TAREA DE LA SESIÓN (1 TAREA POR SESIÓN)
    // =====================================================
    public function abrirModalTarea(): void
    {
        $this->autorizarDocente();
        $this->resetErrorBag();
        if (! $this->sesionActivaId) {
            return;
        }

        $tarea = TareaAsignada::query()->where('id_sesion', $this->sesionActivaId)->first();
        $recursos = $this->recursosFormulario($tarea);
        $this->titulo_tarea = $tarea?->titulo_tarea ?? '';
        $this->descripcion_tarea = $tarea?->descripcion ?? '';
        $this->fecha_limite_tarea = $tarea?->fecha_limite?->format('Y-m-d') ?? '';
        $this->url_video_tarea = $recursos['video']?->url_recurso ?? '';
        $this->cargarApoyo($recursos['pdf'], $recursos['apoyo']);
        $this->modalLeccionAbierto = false;
        $this->modalTareaAbierto = true;
    }

    public function guardarTarea(): void
    {
        $this->autorizarDocente();
        if (! $this->sesionActivaId) {
            return;
        }

        $this->titulo_tarea = trim($this->titulo_tarea);
        $this->descripcion_tarea = trim($this->descripcion_tarea);

        $this->guardarConRecursos(function (Sesion $sesion, ?string &$archivoNuevo): void {
            $recursos = $this->recursosFormulario($sesion->tareaAsignada()->first());
            $datos = $this->validarFormulario('url_video_tarea', false, [
                'titulo_tarea' => ['required', 'string', 'max:255'],
                'descripcion_tarea' => ['required', 'string'],
                'fecha_limite_tarea' => ['nullable', 'date_format:Y-m-d'],
            ], (bool) $recursos['pdf']);

            $tarea = $sesion->tareaAsignada()->updateOrCreate([], [
                'titulo_tarea' => $datos['titulo_tarea'],
                'descripcion' => $datos['descripcion_tarea'],
                'fecha_limite' => $datos['fecha_limite_tarea'] ?: null,
            ]);

            $this->sincronizarRecursos($tarea, $recursos, $datos['url_video_tarea'] ?? '', $archivoNuevo);
        });

        $this->modalTareaAbierto = false;
        $this->mostrarToast('Cambios guardados correctamente');
    }

    /** @return array{video: ?Recurso, mp4: ?Recurso, pdf: ?Recurso, apoyo: ?Recurso} */
    private function recursosFormulario(Leccion|TareaAsignada|null $contenido): array
    {
        $recursos = $contenido?->recursos()->with('tipoRecurso')->orderBy('id_recurso')->get() ?? collect();
        $enlaces = $recursos->filter(fn (Recurso $recurso) => $recurso->tipoRecurso?->nombre_tipo === 'URL');

        return [
            'video' => $enlaces->first(fn (Recurso $recurso) => $recurso->urlReproductor() !== null),
            'mp4' => $recursos->first(fn (Recurso $recurso) => $recurso->tipoRecurso?->nombre_tipo === 'MP4'),
            'pdf' => $recursos->first(fn (Recurso $recurso) => $recurso->tipoRecurso?->nombre_tipo === 'PDF'),
            'apoyo' => $enlaces->first(fn (Recurso $recurso) => $recurso->urlReproductor() === null),
        ];
    }

    public function quitarPdfTemporal(): void
    {
        $this->autorizarDocente();
        $this->reset('archivo_pdf');
        $this->resetValidation('archivo_pdf');
    }

    private function cargarApoyo(?Recurso $pdf, ?Recurso $apoyo): void
    {
        $this->archivo_pdf = null;
        $this->titulo_pdf = $pdf?->titulo_recurso ?? '';
        $this->url_apoyo = $apoyo?->url_recurso ?? '';
        $this->titulo_apoyo = $apoyo?->titulo_recurso ?? '';
        $this->tienePdfGuardado = (bool) $pdf;
        $this->nombrePdfGuardado = $pdf?->titulo_recurso;
        $this->tipo_recurso_extra = match (true) {
            $pdf && $apoyo => 'Ambos',
            (bool) $pdf => 'Partitura',
            (bool) $apoyo => 'Enlace de referencia',
            default => 'Ninguno',
        };
    }

    /** @param array<string, list<string>> $reglas */
    private function validarFormulario(string $campoVideo, bool $videoObligatorio, array $reglas, bool $tienePdf): array
    {
        $this->{$campoVideo} = trim($this->{$campoVideo});
        $this->url_apoyo = trim($this->url_apoyo);
        $this->titulo_pdf = trim($this->titulo_pdf);
        $this->titulo_apoyo = trim($this->titulo_apoyo);
        $requierePdf = in_array($this->tipo_recurso_extra, ['Partitura', 'Ambos'], true);
        $requiereApoyo = in_array($this->tipo_recurso_extra, ['Enlace de referencia', 'Ambos'], true);
        $datos = $this->validate($reglas + [
            $campoVideo => [$videoObligatorio ? 'required' : 'nullable', 'url:http,https', 'max:255'],
            'tipo_recurso_extra' => [Rule::in(['Ninguno', 'Partitura', 'Enlace de referencia', 'Ambos'])],
            'archivo_pdf' => array_merge($requierePdf ? [] : ['exclude'], [
                $tienePdf ? 'nullable' : 'required', 'file', 'mimes:pdf', 'extensions:pdf', 'max:10240',
            ]),
            'titulo_pdf' => [$requierePdf ? 'nullable' : 'exclude', 'string', 'max:255'],
            'url_apoyo' => [$requiereApoyo ? 'required' : 'exclude', 'url:http,https', 'max:255'],
            'titulo_apoyo' => [$requiereApoyo ? 'nullable' : 'exclude', 'string', 'max:255'],
        ], [
            'required' => 'Este campo es obligatorio',
            'url' => 'Ingresa un enlace válido que comience con http:// o https://.',
            'date' => 'Ingresa una fecha válida.',
            'max' => 'El texto no puede superar los :max caracteres.',
            'string' => 'Formato de texto no válido.',
            'in' => 'Selecciona una opción válida.',
            'archivo_pdf.file' => 'Formato o tamaño de archivo no permitido',
            'archivo_pdf.mimes' => 'Formato o tamaño de archivo no permitido',
            'archivo_pdf.extensions' => 'Formato o tamaño de archivo no permitido',
            'archivo_pdf.max' => 'Formato o tamaño de archivo no permitido',
        ]);

        $tipoUrl = new TipoRecurso(['nombre_tipo' => 'URL']);
        $obtenerEmbed = static fn (string $url): ?string => (new Recurso(['url_recurso' => $url]))
            ->setRelation('tipoRecurso', $tipoUrl)->urlReproductor();

        $embedVideo = $this->{$campoVideo} !== '' ? $obtenerEmbed($this->{$campoVideo}) : null;

        if ($this->{$campoVideo} !== '' && $embedVideo === null) {
            throw ValidationException::withMessages([$campoVideo => 'Ingresa un enlace válido de YouTube o Vimeo.']);
        }

        if ($requiereApoyo && $this->{$campoVideo} !== '') {
            $embedApoyo = $obtenerEmbed($this->url_apoyo);
            $mismoEnlace = $this->url_apoyo === $this->{$campoVideo}
                || ($embedApoyo !== null && $embedApoyo === $embedVideo);

            if ($mismoEnlace) {
                throw ValidationException::withMessages(['url_apoyo' => 'Ingresa un enlace web distinto del enlace de video.']);
            }
        }

        return $datos;
    }

    private function guardarConRecursos(\Closure $guardar): void
    {
        $archivoNuevo = null;

        try {
            DB::transaction(function () use ($guardar, &$archivoNuevo): void {
                $sesion = Sesion::query()->lockForUpdate()->findOrFail($this->sesionActivaId);
                $guardar($sesion, $archivoNuevo);
            });
        } catch (\Throwable $excepcion) {
            if ($archivoNuevo !== null) {
                Storage::disk('public')->delete($archivoNuevo);
            }

            throw $excepcion;
        }
    }

    /** @param array{video: ?Recurso, mp4: ?Recurso, pdf: ?Recurso, apoyo: ?Recurso} $recursos */
    private function sincronizarRecursos(Leccion|TareaAsignada $contenido, array $recursos, string $video, ?string &$archivoNuevo): void
    {
        $relacion = $contenido->recursos();
        if ($video !== '') {
            $this->guardarRecurso($relacion, $recursos['video'], 'URL', $recursos['video']?->titulo_recurso ?? 'Video principal', $video);
        } else {
            $recursos['video']?->delete();
        }

        if (in_array($this->tipo_recurso_extra, ['Partitura', 'Ambos'], true)) {
            $pdf = $recursos['pdf'];
            $ruta = $pdf?->url_recurso;
            $tamano = $pdf?->tamano_mb;
            if ($this->archivo_pdf) {
                $archivoNuevo = $this->archivo_pdf->store('recursos/pdfs', 'public') ?: throw ValidationException::withMessages([
                    'archivo_pdf' => 'No se pudo guardar, intenta nuevamente',
                ]);
                $ruta = 'storage/'.$archivoNuevo;
                $tamano = round($this->archivo_pdf->getSize() / 1048576, 2);
            }
            $this->guardarRecurso($relacion, $pdf, 'PDF', $this->titulo_pdf ?: 'Partitura / Guía PDF', $ruta, $tamano);
        } else {
            $recursos['pdf']?->delete();
        }

        if (in_array($this->tipo_recurso_extra, ['Enlace de referencia', 'Ambos'], true)) {
            $this->guardarRecurso($relacion, $recursos['apoyo'], 'URL', $this->titulo_apoyo ?: 'Enlace de apoyo', $this->url_apoyo);
        } else {
            $recursos['apoyo']?->delete();
        }
    }

    private function guardarRecurso(HasMany $relacion, ?Recurso $recurso, string $tipo, string $titulo, string $url, float|string|null $tamano = null): void
    {
        $datos = [
            'id_tipo_recurso' => TipoRecurso::query()->firstOrCreate(['nombre_tipo' => $tipo])->id_tipo_recurso,
            $relacion->getForeignKeyName() === 'id_leccion' ? 'id_tarea' : 'id_leccion' => null,
            'titulo_recurso' => $titulo,
            'url_recurso' => $url,
            'tamano_mb' => $tamano,
        ];

        if ($recurso) {
            $recurso->update($datos);
        } else {
            $relacion->create($datos);
        }
    }

    public function eliminarTarea(): void
    {
        $this->autorizarDocente();
        if (! $this->sesionActivaId) {
            return;
        }

        TareaAsignada::query()->where('id_sesion', $this->sesionActivaId)->delete();
        $this->mostrarToast('Cambios guardados correctamente', 'info');
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

    private function autorizarDocente(): void
    {
        /** @var UsuarioSistema|null $usuario */
        $usuario = Auth::user();
        abort_unless($usuario && $usuario->rol === 'docente' && $usuario->estado_cuenta === 'Activo', 403);
    }

    public function render(): View
    {
        $cursos = Curso::query()
            ->with(['instrumento', 'sesiones.lecciones', 'sesiones.tareaAsignada.entregas'])
            ->withCount([
                'detalleMatriculas as matriculados_activos_count' => fn ($q) => $q->where('estado_acceso', 'Activo'),
            ])
            ->orderBy('id_curso')
            ->get();

        $instrumentos = Instrumento::query()->orderBy('id_instrumento')->get();

        $cursoActivo = $this->cursoSeleccionadoId
            ? $cursos->firstWhere('id_curso', $this->cursoSeleccionadoId)
            : $cursos->first();

        if ($cursoActivo && ! $this->cursoSeleccionadoId) {
            $this->cursoSeleccionadoId = $cursoActivo->id_curso;
        }

        $sesionesCurso = $cursoActivo
            ? Sesion::query()
                ->with(['lecciones.recursos.tipoRecurso', 'tareaAsignada.recursos.tipoRecurso', 'tareaAsignada.entregas'])
                ->where('id_curso', $cursoActivo->id_curso)
                ->orderBy('numero_orden')
                ->get()
            : collect();

        $sesionActiva = $this->sesionActivaId
            ? $sesionesCurso->firstWhere('id_sesion', $this->sesionActivaId)
            : $sesionesCurso->first();

        if ($sesionActiva && $this->sesionActivaId !== $sesionActiva->id_sesion) {
            $this->sesionActivaId = $sesionActiva->id_sesion;
        }

        return view('livewire.docente.sesiones', [
            'cursos' => $cursos,
            'instrumentos' => $instrumentos,
            'cursoActivo' => $cursoActivo,
            'sesionesCurso' => $sesionesCurso,
            'sesionActiva' => $sesionActiva,
        ]);
    }
}
