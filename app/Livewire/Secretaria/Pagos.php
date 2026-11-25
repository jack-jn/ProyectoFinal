<?php

namespace App\Livewire\Secretaria;

use App\Mail\CredencialesAlumnoMail;
use App\Models\Curso;
use App\Models\DetalleMatricula;
use App\Models\Instrumento;
use App\Models\Pago;
use App\Models\Secretaria;
use App\Models\UsuarioSistema;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.secretaria')]
#[Title('DUNAMYS | Gestión de Pagos')]
class Pagos extends Component
{
    use WithPagination;

    #[Locked]
    public string $pantallaActiva = 'resumen';

    // Filtros: Pagos pendientes
    public string $busquedaPendientes = '';

    public string $filtroEstadoPendientes = 'Pendiente';

    public string $filtroInstrumentoPendientes = 'todos';

    public string $filtroCursoPendientes = 'todos';

    public string $filtroEstadoCuentaPendientes = 'todos';

    public string $fechaDesdePendientes = '';

    public string $fechaHastaPendientes = '';

    // Filtros: Historial de pagos
    public string $busquedaHistorial = '';

    public string $filtroEstadoHistorial = 'todos';

    public string $filtroInstrumentoHistorial = 'todos';

    public string $filtroCursoHistorial = 'todos';

    public string $filtroEstadoCuentaHistorial = 'todos';

    public string $fechaDesdeHistorial = '';

    public string $fechaHastaHistorial = '';

    // Filtros: Pago aprobado y acceso
    public string $busquedaAccesos = '';

    public string $filtroEstadoPagoAccesos = 'Validado';

    public string $filtroInstrumentoAccesos = 'todos';

    public string $filtroCursoAccesos = 'todos';

    public string $filtroEstadoCuentaAccesos = 'todos';

    public string $fechaDesdeAccesos = '';

    public string $fechaHastaAccesos = '';

    // Modal Detalle de Comprobante
    public bool $modalDetalleAbierto = false;

    #[Locked]
    public ?int $pagoDetalleId = null;

    // Modal Observar / Rechazar Comprobante
    public bool $modalObservarAbierto = false;

    #[Locked]
    public ?int $pagoObservarId = null;

    public string $motivo_observacion = '';

    // Modal Confirmación de Correo
    public bool $modalConfirmacionCorreoAbierto = false;

    public bool $correoConfirmacionEnviado = true;

    public string $correoConfirmacionNombre = '';

    public string $correoConfirmacionDestino = '';

    // Pantalla de Acceso Aprobado
    public ?int $ultimoPagoValidadoId = null;

    // Toast
    public ?string $toastMensaje = null;

    public string $toastTipo = 'success';

    public function updatedBusquedaAccesos(): void
    {
        $this->resetPage('accesosPage');
    }

    public function updatedFiltroEstadoPagoAccesos(): void
    {
        $this->resetPage('accesosPage');
    }

    public function updatedFiltroInstrumentoAccesos(): void
    {
        $this->filtroCursoAccesos = 'todos';
        $this->resetPage('accesosPage');
    }

    public function updatedFiltroCursoAccesos(): void
    {
        $this->resetPage('accesosPage');
    }

    public function updatedFiltroEstadoCuentaAccesos(): void
    {
        $this->resetPage('accesosPage');
    }

    public function updatedFechaDesdeAccesos(): void
    {
        $this->resetPage('accesosPage');
    }

    public function updatedFechaHastaAccesos(): void
    {
        $this->resetPage('accesosPage');
    }

    public function updatedFiltroInstrumentoPendientes(): void
    {
        $this->filtroCursoPendientes = 'todos';
    }

    public function updatedFiltroInstrumentoHistorial(): void
    {
        $this->filtroCursoHistorial = 'todos';
    }

    public function limpiarFiltrosPendientes(): void
    {
        $this->busquedaPendientes = '';
        $this->filtroEstadoPendientes = 'Pendiente';
        $this->filtroInstrumentoPendientes = 'todos';
        $this->filtroCursoPendientes = 'todos';
        $this->filtroEstadoCuentaPendientes = 'todos';
        $this->fechaDesdePendientes = '';
        $this->fechaHastaPendientes = '';
    }

    public function limpiarFiltrosHistorial(): void
    {
        $this->busquedaHistorial = '';
        $this->filtroEstadoHistorial = 'todos';
        $this->filtroInstrumentoHistorial = 'todos';
        $this->filtroCursoHistorial = 'todos';
        $this->filtroEstadoCuentaHistorial = 'todos';
        $this->fechaDesdeHistorial = '';
        $this->fechaHastaHistorial = '';
    }

    public function limpiarFiltrosAccesos(): void
    {
        $this->busquedaAccesos = '';
        $this->filtroEstadoPagoAccesos = 'Validado';
        $this->filtroInstrumentoAccesos = 'todos';
        $this->filtroCursoAccesos = 'todos';
        $this->filtroEstadoCuentaAccesos = 'todos';
        $this->fechaDesdeAccesos = '';
        $this->fechaHastaAccesos = '';
        $this->resetPage('accesosPage');
    }

    public function cambiarPantalla(string $pantalla): void
    {
        if (in_array($pantalla, ['resumen', 'pendientes', 'historial', 'acceso'], true)) {
            abort_unless(Auth::user()?->can($pantalla), 403);
            $this->cerrarModalDetalle();
            $this->pantallaActiva = $pantalla;
        }
    }

    public function verDetallePago(int $idPago): void
    {
        $this->autorizarSecretaria();
        abort_unless(Auth::user()?->can($this->pantallaActiva), 403);
        $this->cerrarModalObservar();
        $this->pagoDetalleId = Pago::query()->findOrFail($idPago)->id_pago;
        $this->modalDetalleAbierto = true;
    }

    public function cerrarModalDetalle(): void
    {
        $this->modalDetalleAbierto = false;
        $this->pagoDetalleId = null;
    }

    /**
     * Validar pago en DB::transaction:
     * - Verifica que el DNI tenga 8 dígitos y no sea "00000000".
     * - pagos.estado_validacion = 'Validado', guarda id_secretaria y fecha_validacion
     * - matriculas.estado_matricula = 'Confirmada'
     * - detalle_matriculas.estado_acceso = 'Activo'
     * - Crea o actualiza usuarios_sistema con rol = 'alumno', estado_cuenta = 'Activo',
     *   contraseña aleatoria Str::password(10), contrasena = Hash::make(),
     *   password_temporal = Crypt::encryptString(), debe_cambiar_contrasena = true.
     * - NO modifica cursos.cupos.
     */
    public function validarPago(int $idPago): void
    {
        $this->autorizarSecretaria();
        abort_unless(Auth::user()->canAny(['secretaria.validar_pagos', 'validar_observar']), 403);

        $pagoPreliminar = Pago::query()
            ->with(['matricula.alumno.persona'])
            ->findOrFail($idPago);

        if ($pagoPreliminar->estado_validacion !== 'Pendiente') {
            $this->mostrarToast('Solo se pueden validar pagos en estado Pendiente.', 'error');

            return;
        }

        $personaPreliminar = $pagoPreliminar->matricula?->alumno?->persona;
        $dniAlumno = trim((string) ($personaPreliminar?->dni ?? ''));

        if (! preg_match('/^\d{8}$/', $dniAlumno) || $dniAlumno === '00000000') {
            $this->mostrarToast('El DNI del alumno debe tener 8 dígitos válidos y no puede ser 00000000.', 'error');

            return;
        }

        $idSecretaria = $this->obtenerIdSecretariaActual();

        /** @var array{aviso: ?string, correo: ?array{nombre: string, codigo: string, cursos: list<string>, correo: string, password: ?string, es_nueva: bool}} $resultado */
        $resultado = DB::transaction(function () use ($idPago, $idSecretaria): array {
            $pago = Pago::query()
                ->with(['matricula.alumno.persona', 'matricula.detalleMatriculas.curso'])
                ->findOrFail($idPago);

            $pago->update([
                'estado_validacion' => 'Validado',
                'id_secretaria' => $idSecretaria,
                'fecha_validacion' => now(),
                'observacion' => null,
            ]);

            $matricula = $pago->matricula;
            $matricula->update([
                'estado_matricula' => 'Confirmada',
            ]);

            DetalleMatricula::query()
                ->where('id_matricula', $matricula->id_matricula)
                ->update(['estado_acceso' => 'Activo']);

            $personaAlumno = $matricula->alumno?->persona;
            if ($personaAlumno && $personaAlumno->correo) {
                $correoLimpio = strtolower(trim($personaAlumno->correo));
                $usuarioPorPersona = UsuarioSistema::query()
                    ->where('id_persona', $personaAlumno->id_persona)
                    ->first();

                $usuarioPorCorreo = UsuarioSistema::query()
                    ->where('correo_usuario', $correoLimpio)
                    ->first();

                if ($usuarioPorPersona && $usuarioPorPersona->rol !== 'alumno') {
                    return [
                        'aviso' => 'Pago validado. El alumno ya tiene una cuenta con otro rol y no se generó un acceso de alumno.',
                        'correo' => null,
                    ];
                }

                if ($usuarioPorCorreo && $usuarioPorCorreo->id_persona !== $personaAlumno->id_persona) {
                    return [
                        'aviso' => 'Pago validado. El correo del alumno ya pertenece a otra cuenta. Se necesita un correo propio para generar su acceso.',
                        'correo' => null,
                    ];
                }

                $usuarioExistente = $usuarioPorPersona;
                $esCuentaNueva = $usuarioExistente === null;
                $claveAleatoria = null;

                if ($usuarioExistente) {
                    $datosActualizacion = [
                        'rol' => 'alumno',
                        'estado_cuenta' => 'Activo',
                    ];

                    if (! $usuarioPorCorreo || $usuarioPorCorreo->id_usuario === $usuarioExistente->id_usuario) {
                        $datosActualizacion['correo_usuario'] = $correoLimpio;
                    }

                    $usuarioExistente->update($datosActualizacion);
                    $usuarioAlumno = $usuarioExistente;
                } else {
                    $claveAleatoria = Str::password(10, symbols: false);
                    $usuarioAlumno = UsuarioSistema::query()->create([
                        'id_persona' => $personaAlumno->id_persona,
                        'correo_usuario' => $correoLimpio,
                        'contrasena' => Hash::make($claveAleatoria),
                        'password_temporal' => Crypt::encryptString($claveAleatoria),
                        'rol' => 'alumno',
                        'estado_cuenta' => 'Activo',
                        'debe_cambiar_contrasena' => true,
                    ]);
                }

                if (! $usuarioAlumno->hasRole('alumno')) {
                    $usuarioAlumno->sincronizarAcceso($usuarioAlumno->permisosIniciales());
                }

                $nombreCompleto = trim($personaAlumno->nombres.' '.$personaAlumno->apellido_paterno.' '.$personaAlumno->apellido_materno);
                $cursosHabilitados = $matricula->detalleMatriculas
                    ->map(fn (DetalleMatricula $det): ?string => $det->curso?->nombre_curso)
                    ->filter()
                    ->values()
                    ->all();

                return [
                    'aviso' => null,
                    'correo' => [
                        'nombre' => $nombreCompleto !== '' ? $nombreCompleto : 'Estudiante',
                        'codigo' => (string) $matricula->codigo_matricula,
                        'cursos' => $cursosHabilitados,
                        'correo' => $correoLimpio,
                        'password' => $claveAleatoria,
                        'es_nueva' => $esCuentaNueva,
                    ],
                ];
            }

            return ['aviso' => null, 'correo' => null];
        });

        $this->cerrarModalDetalle();
        $this->ultimoPagoValidadoId = $idPago;
        if (Auth::user()->can('acceso')) {
            $this->pantallaActiva = 'acceso';
        }

        $avisoAcceso = $resultado['aviso'];
        $datosCorreo = $resultado['correo'];

        if ($avisoAcceso === null && $datosCorreo !== null) {
            $correoEnviado = true;
            try {
                Mail::to($datosCorreo['correo'])->send(new CredencialesAlumnoMail(
                    nombreCompleto: $datosCorreo['nombre'],
                    codigoMatricula: $datosCorreo['codigo'],
                    cursosHabilitados: $datosCorreo['cursos'],
                    correoAcceso: $datosCorreo['correo'],
                    passwordTemporal: $datosCorreo['password'],
                    esCuentaNueva: $datosCorreo['es_nueva'],
                ));
            } catch (\Throwable $exception) {
                $correoEnviado = false;
                Log::error('Error al enviar credenciales al alumno tras validar pago.', [
                    'id_pago' => $idPago,
                    'correo' => $datosCorreo['correo'],
                    'error' => $exception->getMessage(),
                ]);
            }

            $this->correoConfirmacionEnviado = $correoEnviado;
            $this->correoConfirmacionNombre = $datosCorreo['nombre'];
            $this->correoConfirmacionDestino = $datosCorreo['correo'];
            $this->modalConfirmacionCorreoAbierto = true;
        }

        $this->mostrarToast($avisoAcceso ?? 'Cambios guardados correctamente', $avisoAcceso ? 'info' : 'success');
    }

    public function cerrarModalConfirmacionCorreo(): void
    {
        $this->modalConfirmacionCorreoAbierto = false;
    }

    public function abrirModalObservar(int $idPago): void
    {
        $this->autorizarSecretaria();
        abort_unless(Auth::user()->canAny(['secretaria.validar_pagos', 'validar_observar']), 403);
        $this->resetErrorBag('motivo_observacion');
        $this->pagoObservarId = $idPago;
        $this->motivo_observacion = '';
        $this->modalDetalleAbierto = false;
        $this->modalObservarAbierto = true;
    }

    public function cerrarModalObservar(): void
    {
        $this->modalObservarAbierto = false;
        $this->pagoObservarId = null;
        $this->resetErrorBag('motivo_observacion');
    }

    public function observarPago(): void
    {
        $this->autorizarSecretaria();
        abort_unless(Auth::user()->canAny(['secretaria.validar_pagos', 'validar_observar']), 403);
        $this->resetErrorBag('motivo_observacion');

        $motivo = trim($this->motivo_observacion);
        if ($motivo === '') {
            $this->addError('motivo_observacion', 'Este campo es obligatorio');

            return;
        }
        if (mb_strlen($motivo) < 5) {
            $this->addError('motivo_observacion', 'La observación debe tener al menos 5 caracteres');

            return;
        }

        if (! $this->pagoObservarId) {
            return;
        }

        $idSecretaria = $this->obtenerIdSecretariaActual();

        DB::transaction(function () use ($motivo, $idSecretaria): void {
            $pago = Pago::query()->with('matricula')->findOrFail($this->pagoObservarId);

            abort_unless($pago->estado_validacion === 'Pendiente', 422, 'Solo se pueden observar pagos pendientes.');

            $pago->update([
                'estado_validacion' => 'Observado',
                'observacion' => $motivo,
                'id_secretaria' => $idSecretaria,
                'fecha_validacion' => now(),
            ]);

            $pago->matricula?->update([
                'estado_matricula' => 'Rechazada',
            ]);

            if ($pago->matricula) {
                DetalleMatricula::query()
                    ->where('id_matricula', $pago->matricula->id_matricula)
                    ->update(['estado_acceso' => 'Bloqueado']);
            }
        });

        $this->modalObservarAbierto = false;
        $this->pagoObservarId = null;
        $this->motivo_observacion = '';
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

    private function autorizarSecretaria(): void
    {
        /** @var UsuarioSistema|null $usuario */
        $usuario = Auth::user();
        abort_unless(
            $usuario && in_array($usuario->rol, ['secretaria', 'docente'], true) && $usuario->estado_cuenta === 'Activo',
            403
        );
    }

    private function obtenerIdSecretariaActual(): int
    {
        /** @var UsuarioSistema $usuario */
        $usuario = Auth::user();
        $sec = Secretaria::query()->where('id_persona', $usuario->id_persona)->first();
        if ($sec) {
            return $sec->id_secretaria;
        }

        return Secretaria::query()->first()?->id_secretaria
            ?? Secretaria::query()->create([
                'id_persona' => $usuario->id_persona,
                'codigo_secretaria' => 'SEC-'.random_int(100, 999),
                'turno_atencion' => 'Regular',
            ])->id_secretaria;
    }

    private function coincideFiltrosColeccion(
        Pago $p,
        string $busqueda,
        string $estado,
        string $instrumento,
        string $cursoId,
        string $fechaDesde,
        string $fechaHasta,
        string $estadoCuenta
    ): bool {
        if ($estado !== 'todos' && $p->estado_validacion !== $estado) {
            return false;
        }

        $cuenta = $p->matricula?->alumno?->persona?->usuarioSistema?->estado_cuenta ?? 'sin_cuenta';
        if ($estadoCuenta !== 'todos' && $cuenta !== $estadoCuenta) {
            return false;
        }

        $detalles = $p->matricula?->detalleMatriculas ?? collect();

        if (($instrumento !== 'todos' || $cursoId !== 'todos') && ! $detalles->contains(
            fn ($detalle) => ($instrumento === 'todos' || $detalle->curso?->instrumento?->nombre_instrumento === $instrumento)
                && ($cursoId === 'todos' || (string) $detalle->id_curso === $cursoId)
        )) {
            return false;
        }

        if ($fechaDesde !== '' && $p->fecha_pago) {
            if ($p->fecha_pago->toDateString() < $fechaDesde) {
                return false;
            }
        }

        if ($fechaHasta !== '' && $p->fecha_pago) {
            if ($p->fecha_pago->toDateString() > $fechaHasta) {
                return false;
            }
        }

        if (trim($busqueda) !== '') {
            $q = mb_strtolower(trim($busqueda));
            $persona = $p->matricula?->alumno?->persona;
            $cursosTxt = $detalles->map(fn ($d) => $d->curso?->nombre_curso)->implode(' ');
            $instTxt = $detalles->map(fn ($d) => $d->curso?->instrumento?->nombre_instrumento)->implode(' ');
            $haystack = mb_strtolower(
                ($persona?->nombre_completo ?? '').' '.
                ($persona?->dni ?? '').' '.
                ($p->matricula?->codigo_matricula ?? '').' '.
                ($p->numero_operacion ?? '').' '.
                $cursosTxt.' '.
                $instTxt
            );

            if (! str_contains($haystack, $q)) {
                return false;
            }
        }

        return true;
    }

    public function render(): View
    {
        $this->autorizarSecretaria();
        $usuario = Auth::user();
        if (! $usuario->can($this->pantallaActiva)) {
            $this->pantallaActiva = collect(['resumen', 'pendientes', 'historial', 'acceso'])
                ->first(fn (string $pantalla) => $usuario->can($pantalla)) ?? 'sin_acceso';
            $this->cerrarModalDetalle();
        }

        $instrumentos = Instrumento::query()->orderBy('id_instrumento')->get();
        $cursos = Curso::query()->with('instrumento')->orderBy('id_curso')->get();

        $pagos = Pago::query()
            ->with([
                'matricula.alumno.persona.usuarioSistema',
                'matricula.detalleMatriculas.curso.instrumento',
                'secretaria.persona',
            ])
            ->orderByDesc('fecha_pago')
            ->orderByDesc('id_pago')
            ->get();

        $pendientes = $pagos->filter(fn (Pago $p): bool => $this->coincideFiltrosColeccion(
            $p,
            $this->busquedaPendientes,
            $this->filtroEstadoPendientes,
            $this->filtroInstrumentoPendientes,
            $this->filtroCursoPendientes,
            $this->fechaDesdePendientes,
            $this->fechaHastaPendientes,
            $this->filtroEstadoCuentaPendientes
        ));

        $historial = $pagos->filter(fn (Pago $p): bool => $this->coincideFiltrosColeccion(
            $p,
            $this->busquedaHistorial,
            $this->filtroEstadoHistorial,
            $this->filtroInstrumentoHistorial,
            $this->filtroCursoHistorial,
            $this->fechaDesdeHistorial,
            $this->fechaHastaHistorial,
            $this->filtroEstadoCuentaHistorial
        ));

        $queryAccesos = Pago::query()
            ->with([
                'matricula.alumno.persona.usuarioSistema',
                'matricula.detalleMatriculas.curso.instrumento',
            ]);

        if ($this->filtroEstadoPagoAccesos !== 'todos') {
            $queryAccesos->where('estado_validacion', $this->filtroEstadoPagoAccesos);
        }

        if ($this->filtroInstrumentoAccesos !== 'todos' || $this->filtroCursoAccesos !== 'todos') {
            $queryAccesos->whereHas('matricula.detalleMatriculas', function ($detalles): void {
                $detalles->when($this->filtroCursoAccesos !== 'todos',
                    fn ($query) => $query->where('id_curso', $this->filtroCursoAccesos))
                    ->when($this->filtroInstrumentoAccesos !== 'todos',
                        fn ($query) => $query->whereHas('curso.instrumento',
                            fn ($instrumento) => $instrumento->where('nombre_instrumento', $this->filtroInstrumentoAccesos)));
            });
        }

        if ($this->filtroEstadoCuentaAccesos !== 'todos') {
            if ($this->filtroEstadoCuentaAccesos === 'Sin cuenta') {
                $queryAccesos->whereDoesntHave('matricula.alumno.persona.usuarioSistema');
            } else {
                $queryAccesos->whereHas('matricula.alumno.persona.usuarioSistema', function ($q): void {
                    $q->where('estado_cuenta', $this->filtroEstadoCuentaAccesos);
                });
            }
        }

        if ($this->fechaDesdeAccesos !== '') {
            $queryAccesos->whereDate(DB::raw('COALESCE(fecha_validacion, fecha_pago)'), '>=', $this->fechaDesdeAccesos);
        }

        if ($this->fechaHastaAccesos !== '') {
            $queryAccesos->whereDate(DB::raw('COALESCE(fecha_validacion, fecha_pago)'), '<=', $this->fechaHastaAccesos);
        }

        $terminoAcceso = trim($this->busquedaAccesos);
        if ($terminoAcceso !== '') {
            $like = '%'.$terminoAcceso.'%';
            $queryAccesos->where(function ($q) use ($like): void {
                $q->where('numero_operacion', 'like', $like)
                    ->orWhereHas('matricula', fn ($qm) => $qm->where('codigo_matricula', 'like', $like))
                    ->orWhereHas('matricula.alumno.persona', function ($qp) use ($like): void {
                        $qp->where('dni', 'like', $like)
                            ->orWhere('nombres', 'like', $like)
                            ->orWhere('apellido_paterno', 'like', $like)
                            ->orWhere('apellido_materno', 'like', $like)
                            ->orWhere('correo', 'like', $like)
                            ->orWhereRaw("CONCAT_WS(' ', nombres, apellido_paterno, apellido_materno) LIKE ?", [$like]);
                    });
            });
        }

        $accesosPaginados = $queryAccesos
            ->orderByDesc('fecha_validacion')
            ->orderByDesc('id_pago')
            ->paginate(10, ['*'], 'accesosPage');

        $pagoDetalle = $this->modalDetalleAbierto
            ? $pagos->firstWhere('id_pago', $this->pagoDetalleId)
            : null;
        $edadAlumno = $pagoDetalle?->matricula?->alumno?->persona?->fecha_nacimiento?->age;
        $apoderadoDetalle = $edadAlumno !== null && $edadAlumno < 18
            ? $pagoDetalle->matricula->alumno->apoderado?->loadMissing('persona')
            : null;
        $pagoValidado = $this->ultimoPagoValidadoId
            ? $pagos->firstWhere('id_pago', $this->ultimoPagoValidadoId)
            : $pagos->where('estado_validacion', 'Validado')->first();

        $totalRecaudado = (float) $pagos->where('estado_validacion', 'Validado')->sum('monto_pagado');

        return view('livewire.secretaria.pagos', [
            'instrumentos' => $instrumentos,
            'cursos' => $cursos,
            'pagos' => $pagos,
            'pendientes' => $pendientes,
            'historial' => $historial,
            'accesosPaginados' => $accesosPaginados,
            'pagoDetalle' => $pagoDetalle,
            'apoderadoDetalle' => $apoderadoDetalle,
            'pagoValidado' => $pagoValidado,
            'totalRecaudado' => $totalRecaudado,
        ]);
    }
}
