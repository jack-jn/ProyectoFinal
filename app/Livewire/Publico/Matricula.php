<?php

namespace App\Livewire\Publico;

use App\Models\Alumno;
use App\Models\Apoderado;
use App\Models\Curso;
use App\Models\DetalleMatricula;
use App\Models\Matricula as MatriculaModel;
use App\Models\Pago;
use App\Models\Persona;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.publico')]
#[Title('Matrícula | DUNAMYS Academia Musical')]
class Matricula extends Component
{
    use WithFileUploads;

    public int $pasoActual = 1;

    /** @var array<int, int> */
    public array $cursosSeleccionados = [];

    // Datos personales del estudiante (Paso 3)
    public string $nombres = '';

    public string $apellido_paterno = '';

    public string $apellido_materno = '';

    public string $dni = '';

    public string $fecha_nacimiento = '';

    public string $correo = '';

    public string $celular = '';

    public bool $esMenorDeEdad = false;

    // Datos del apoderado (Paso 4 - obligatorio si < 18 años)
    public string $apoderado_nombres = '';

    public string $apoderado_apellido_paterno = '';

    public string $apoderado_apellido_materno = '';

    public string $apoderado_dni = '';

    public string $apoderado_celular = '';

    public string $apoderado_parentesco = 'Padre/Madre';

    public bool $apoderado_consentimiento = false;

    // Pago por Yape (Paso 6)
    public string $metodo_pago = 'Yape';

    public string $numero_operacion = '';

    public $comprobante = null;

    // Confirmación final y reenvío de comprobante observado
    public bool $matriculaRegistrada = false;

    public string $codigoMatriculaGenerado = '';

    public string $codigoBusquedaReenvio = '';

    public ?int $pagoObservadoId = null;

    public ?string $motivoObservacionActual = null;

    public string $nuevo_numero_operacion = '';

    public $nuevo_comprobante = null;

    public ?string $mensajeExitoReenvio = null;

    public function mount(): void
    {
        $idCursoPreseleccionado = (int) request()->query('curso', 0);
        if ($idCursoPreseleccionado > 0) {
            $this->alternarCurso($idCursoPreseleccionado);
        }
    }

    public function alternarCurso(int $idCurso): void
    {
        $this->resetErrorBag('cursosSeleccionados');

        $curso = Curso::query()
            ->withCount([
                'detalleMatriculas as matriculados_activos_count' => fn ($q) => $q->where('estado_acceso', 'Activo'),
            ])
            ->where('estado_habilitado', true)
            ->where('modalidad', 'Virtual')
            ->find($idCurso);

        if (! $curso || $curso->cupos_disponibles <= 0) {
            $this->addError('cursosSeleccionados', 'No se permite elegir un curso sin cupos disponibles.');

            return;
        }

        if (in_array($idCurso, $this->cursosSeleccionados, true)) {
            $this->cursosSeleccionados = array_values(array_diff($this->cursosSeleccionados, [$idCurso]));
        } else {
            $this->cursosSeleccionados[] = $idCurso;
        }
    }

    public function irAPaso(int $paso): void
    {
        $this->resetErrorBag();

        if ($paso === 2) {
            if (empty($this->cursosSeleccionados)) {
                $this->addError('cursosSeleccionados', 'Este campo es obligatorio');

                return;
            }

            // Verificar que todos tengan cupos disponibles
            $cursos = Curso::query()
                ->withCount([
                    'detalleMatriculas as matriculados_activos_count' => fn ($q) => $q->where('estado_acceso', 'Activo'),
                ])
                ->where('estado_habilitado', true)
                ->where('modalidad', 'Virtual')
                ->whereIn('id_curso', $this->cursosSeleccionados)
                ->get();

            if ($cursos->count() !== count($this->cursosSeleccionados) || $cursos->contains(fn (Curso $c) => $c->cupos_disponibles <= 0)) {
                $this->addError('cursosSeleccionados', 'Uno de los cursos seleccionados no tiene cupos disponibles.');

                return;
            }
        }

        $this->pasoActual = $paso;
    }

    public function continuarDesdeDatosPersonales(): void
    {
        $this->resetErrorBag();

        if (trim($this->nombres) === '') {
            $this->addError('nombres', 'Este campo es obligatorio');
        }
        if (trim($this->apellido_paterno) === '') {
            $this->addError('apellido_paterno', 'Este campo es obligatorio');
        }
        if (trim($this->apellido_materno) === '') {
            $this->addError('apellido_materno', 'Este campo es obligatorio');
        }
        if (! preg_match('/^\d{8}$/', trim($this->dni)) || trim($this->dni) === '00000000') {
            $this->addError('dni', 'Ingresa un DNI válido de 8 dígitos');
        }
        if (trim($this->fecha_nacimiento) === '') {
            $this->addError('fecha_nacimiento', 'Este campo es obligatorio');
        }
        if (trim($this->correo) === '' || ! filter_var(trim($this->correo), FILTER_VALIDATE_EMAIL)) {
            $this->addError('correo', 'Este campo es obligatorio');
        }

        $celularLimpio = preg_replace('/[\s-]+/', '', trim($this->celular)) ?? '';
        if (! preg_match('/^\d{9}$/', $celularLimpio)) {
            $this->addError('celular', 'Ingresa un número de celular válido');
        }

        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        $this->celular = $celularLimpio;
        $edad = Carbon::parse($this->fecha_nacimiento)->age;
        $this->esMenorDeEdad = $edad < 18;

        $this->pasoActual = $this->esMenorDeEdad ? 4 : 5;
    }

    public function continuarDesdeApoderado(): void
    {
        $this->resetErrorBag();

        if (trim($this->apoderado_nombres) === '') {
            $this->addError('apoderado_nombres', 'Este campo es obligatorio');
        }
        if (trim($this->apoderado_apellido_paterno) === '') {
            $this->addError('apoderado_apellido_paterno', 'Este campo es obligatorio');
        }
        if (trim($this->apoderado_apellido_materno) === '') {
            $this->addError('apoderado_apellido_materno', 'Este campo es obligatorio');
        }
        if (! preg_match('/^\d{8}$/', trim($this->apoderado_dni)) || trim($this->apoderado_dni) === '00000000') {
            $this->addError('apoderado_dni', 'Ingresa un DNI válido de 8 dígitos');
        }

        $celApodLimpio = preg_replace('/[\s-]+/', '', trim($this->apoderado_celular)) ?? '';
        if (! preg_match('/^\d{9}$/', $celApodLimpio)) {
            $this->addError('apoderado_celular', 'Ingresa un número de celular válido');
        }
        if (trim($this->apoderado_parentesco) === '') {
            $this->addError('apoderado_parentesco', 'Este campo es obligatorio');
        }

        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        $this->apoderado_celular = $celApodLimpio;
        $this->pasoActual = 5;
    }

    public function registrarMatricula(): void
    {
        $this->resetErrorBag();

        if (empty($this->cursosSeleccionados)) {
            $this->addError('cursosSeleccionados', 'Este campo es obligatorio');

            return;
        }

        // Recalcular minoría de edad por seguridad en servidor
        if (trim($this->fecha_nacimiento) === '') {
            $this->addError('fecha_nacimiento', 'Este campo es obligatorio');

            return;
        }

        $edad = Carbon::parse($this->fecha_nacimiento)->age;
        $this->esMenorDeEdad = $edad < 18;

        if ($this->esMenorDeEdad) {
            if (
                trim($this->apoderado_nombres) === '' ||
                trim($this->apoderado_apellido_paterno) === '' ||
                trim($this->apoderado_apellido_materno) === '' ||
                ! preg_match('/^\d{8}$/', trim($this->apoderado_dni)) ||
                ! preg_match('/^\d{9}$/', preg_replace('/[\s-]+/', '', trim($this->apoderado_celular)) ?? '') ||
                trim($this->apoderado_parentesco) === ''
            ) {
                $this->addError('apoderado_general', 'Los datos del apoderado son obligatorios para menores de 18 años.');

                return;
            }
        }

        if (! $this->comprobante) {
            $this->addError('comprobante', 'Este campo es obligatorio');

            return;
        }

        $ext = strtolower((string) $this->comprobante->getClientOriginalExtension());
        $tamanoKb = $this->comprobante->getSize() / 1024;
        if (! in_array($ext, ['webp', 'jpg', 'jpeg', 'png'], true) || $tamanoKb > 2048) {
            $this->addError('comprobante', 'Formato o tamaño de archivo no permitido');

            return;
        }

        $numOp = trim($this->numero_operacion);
        if ($numOp !== '' && Pago::query()->where('numero_operacion', $numOp)->exists()) {
            $this->addError('numero_operacion', 'Este número de operación ya fue registrado.');

            return;
        }

        $cursos = Curso::query()
            ->withCount([
                'detalleMatriculas as matriculados_activos_count' => fn ($q) => $q->where('estado_acceso', 'Activo'),
            ])
            ->where('estado_habilitado', true)
            ->where('modalidad', 'Virtual')
            ->whereIn('id_curso', $this->cursosSeleccionados)
            ->get();

        if ($cursos->isEmpty() || $cursos->contains(fn (Curso $c) => $c->cupos_disponibles <= 0)) {
            $this->addError('cursosSeleccionados', 'Uno de los cursos seleccionados ya no tiene cupos disponibles.');

            return;
        }

        // Guardar comprobante en disco privado (local)
        $rutaPrivada = $this->comprobante->store('comprobantes', 'local');

        DB::transaction(function () use ($cursos, $rutaPrivada, $numOp): void {
            $idApoderado = null;

            if ($this->esMenorDeEdad) {
                $personaApoderado = Persona::query()->updateOrCreate(
                    ['dni' => trim($this->apoderado_dni)],
                    [
                        'nombres' => trim($this->apoderado_nombres),
                        'apellido_paterno' => trim($this->apoderado_apellido_paterno),
                        'apellido_materno' => trim($this->apoderado_apellido_materno),
                        'celular' => preg_replace('/[\s-]+/', '', trim($this->apoderado_celular)),
                    ]
                );

                $apoderado = Apoderado::query()->updateOrCreate(
                    ['id_persona' => $personaApoderado->id_persona],
                    ['parentesco' => trim($this->apoderado_parentesco)]
                );

                $idApoderado = $apoderado->id_apoderado;
            }

            $personaAlumno = Persona::query()->updateOrCreate(
                ['dni' => trim($this->dni)],
                [
                    'nombres' => trim($this->nombres),
                    'apellido_paterno' => trim($this->apellido_paterno),
                    'apellido_materno' => trim($this->apellido_materno),
                    'fecha_nacimiento' => $this->fecha_nacimiento,
                    'correo' => strtolower(trim($this->correo)),
                    'celular' => preg_replace('/[\s-]+/', '', trim($this->celular)),
                ]
            );

            $alumnoExistente = Alumno::query()->where('id_persona', $personaAlumno->id_persona)->first();
            if ($alumnoExistente) {
                $alumnoExistente->update(['id_apoderado' => $idApoderado]);
                $alumno = $alumnoExistente;
            } else {
                $alumno = Alumno::query()->create([
                    'id_persona' => $personaAlumno->id_persona,
                    'id_apoderado' => $idApoderado,
                    'codigo_alumno' => $this->generarSiguienteCodigoAlumno(),
                ]);
            }

            $montoTotal = (float) $cursos->sum('costo');
            $codigoMatricula = $this->generarSiguienteCodigoMatricula();

            $matricula = MatriculaModel::query()->create([
                'id_alumno' => $alumno->id_alumno,
                'codigo_matricula' => $codigoMatricula,
                'fecha_matricula' => now(),
                'monto_total' => $montoTotal,
                'estado_matricula' => 'Pendiente de pago',
            ]);

            foreach ($cursos as $curso) {
                DetalleMatricula::query()->create([
                    'id_matricula' => $matricula->id_matricula,
                    'id_curso' => $curso->id_curso,
                    'fecha_inscripcion' => now(),
                    'precio_cobrado' => $curso->costo,
                    'estado_acceso' => 'Bloqueado',
                ]);
            }

            Pago::query()->create([
                'id_matricula' => $matricula->id_matricula,
                'id_secretaria' => null,
                'metodo_pago' => 'Yape',
                'numero_operacion' => $numOp !== '' ? $numOp : null,
                'comprobante_url' => $rutaPrivada,
                'monto_pagado' => $montoTotal,
                'fecha_pago' => now(),
                'estado_validacion' => 'Pendiente',
                'observacion' => null,
                'fecha_validacion' => null,
            ]);

            $this->codigoMatriculaGenerado = $codigoMatricula;
            $this->matriculaRegistrada = true;
        });
    }

    public function buscarMatriculaObservada(): void
    {
        $this->resetErrorBag('reenvio');
        $this->pagoObservadoId = null;
        $this->motivoObservacionActual = null;
        $this->mensajeExitoReenvio = null;

        $busqueda = trim($this->codigoBusquedaReenvio);
        if ($busqueda === '') {
            $this->addError('reenvio', 'Este campo es obligatorio');

            return;
        }

        $matricula = MatriculaModel::query()
            ->with('pagos')
            ->where('codigo_matricula', $busqueda)
            ->first();

        $pago = $matricula?->pagos->where('estado_validacion', 'Observado')->first();

        if (! $pago) {
            $this->addError('reenvio', 'No se encontró un comprobante observado para ese código de matrícula.');

            return;
        }

        $this->pagoObservadoId = $pago->id_pago;
        $this->motivoObservacionActual = $pago->observacion;
        $this->nuevo_numero_operacion = (string) ($pago->numero_operacion ?? '');
    }

    public function reenviarComprobanteObservado(): void
    {
        $this->resetErrorBag('nuevo_comprobante');
        if (! $this->pagoObservadoId) {
            return;
        }

        if (! $this->nuevo_comprobante) {
            $this->addError('nuevo_comprobante', 'Este campo es obligatorio');

            return;
        }

        $ext = strtolower((string) $this->nuevo_comprobante->getClientOriginalExtension());
        $tamanoKb = $this->nuevo_comprobante->getSize() / 1024;
        if (! in_array($ext, ['webp', 'jpg', 'jpeg', 'png'], true) || $tamanoKb > 2048) {
            $this->addError('nuevo_comprobante', 'Formato o tamaño de archivo no permitido');

            return;
        }

        $rutaPrivada = $this->nuevo_comprobante->store('comprobantes', 'local');

        DB::transaction(function () use ($rutaPrivada): void {
            $pago = Pago::query()->with('matricula')->findOrFail($this->pagoObservadoId);

            $pago->update([
                'comprobante_url' => $rutaPrivada,
                'numero_operacion' => trim($this->nuevo_numero_operacion) !== '' ? trim($this->nuevo_numero_operacion) : $pago->numero_operacion,
                'estado_validacion' => 'Pendiente',
                'observacion' => null,
                'fecha_pago' => now(),
                'fecha_validacion' => null,
            ]);

            $pago->matricula?->update([
                'estado_matricula' => 'Pendiente de pago',
            ]);
        });

        $this->pagoObservadoId = null;
        $this->nuevo_comprobante = null;
        $this->mensajeExitoReenvio = 'Cambios guardados correctamente';
    }

    private function generarSiguienteCodigoAlumno(): string
    {
        $max = Alumno::query()->count() + 1;

        return 'ALU-'.now()->year.'-'.str_pad((string) $max, 3, '0', STR_PAD_LEFT);
    }

    private function generarSiguienteCodigoMatricula(): string
    {
        $codigos = MatriculaModel::query()->pluck('codigo_matricula');
        $max = 0;
        foreach ($codigos as $cod) {
            if (preg_match('/MAT-\d{4}-(\d+)/', (string) $cod, $m)) {
                $max = max($max, (int) $m[1]);
            }
        }

        return 'MAT-'.now()->year.'-'.str_pad((string) ($max + 1), 3, '0', STR_PAD_LEFT);
    }

    public function render(): View
    {
        $cursosHabilitados = Curso::query()
            ->with('instrumento')
            ->withCount([
                'detalleMatriculas as matriculados_activos_count' => fn ($q) => $q->where('estado_acceso', 'Activo'),
            ])
            ->where('estado_habilitado', true)
            ->where('modalidad', 'Virtual')
            ->orderBy('id_curso')
            ->get();

        $cursosElegidos = $cursosHabilitados->whereIn('id_curso', $this->cursosSeleccionados);
        $totalPagar = (float) $cursosElegidos->sum('costo');

        return view('livewire.publico.matricula', [
            'cursosHabilitados' => $cursosHabilitados,
            'cursosElegidos' => $cursosElegidos,
            'totalPagar' => $totalPagar,
        ]);
    }
}
