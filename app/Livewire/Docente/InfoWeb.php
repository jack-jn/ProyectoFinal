<?php

namespace App\Livewire\Docente;

use App\Models\AvisoWeb;
use App\Models\Docente;
use App\Models\GaleriaEvento;
use App\Models\InfoInstitucional;
use App\Models\UsuarioSistema;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class InfoWeb extends Component
{
    use WithFileUploads;

    public string $pestanaActiva = 'da';

    // Pestaña Datos Institucionales (RF029, RF032, RF033)
    public string $quienes_somos = '';

    public string $telefono_contacto = '';

    public string $correo_contacto = '';

    public string $direccion = '';

    public string $hora_apertura = '08:00';

    public string $hora_cierre = '18:00';

    // Pestaña Avisos
    public ?int $editandoAvisoId = null;

    public string $titulo_aviso = '';

    public string $texto_aviso = '';

    // Pestaña Galería
    public string $titulo_evento = '';

    public string $tipo_multimedia = 'Foto';

    public $archivo_galeria = null;

    // Mensajes Toast
    public ?string $toastMensaje = null;

    public string $toastTipo = 'success';

    public function mount(): void
    {
        $this->cargarDatosInstitucionales();
    }

    public function cargarDatosInstitucionales(): void
    {
        $info = InfoInstitucional::query()->first();

        if ($info) {
            $this->quienes_somos = $info->quienes_somos;
            $this->telefono_contacto = $info->telefono_contacto;
            $this->correo_contacto = $info->correo_contacto;
            $this->direccion = $info->direccion;
            $this->hora_apertura = substr((string) $info->hora_apertura, 0, 5);
            $this->hora_cierre = substr((string) $info->hora_cierre, 0, 5);
        } else {
            $this->quienes_somos = 'Dunamys es una academia de música en Juliaca dedicada a formar talentos con metodología práctica, recitales de gala y aula virtual interactiva.';
            $this->telefono_contacto = '952233266';
            $this->correo_contacto = 'contacto@dunamys.edu.pe';
            $this->direccion = 'Plaza de Armas - Juliaca, Jr. Ica 252, Galería San Román, 3er piso.';
            $this->hora_apertura = '08:00';
            $this->hora_cierre = '18:00';
        }
    }

    public function cambiarPestana(string $pestana): void
    {
        if (in_array($pestana, ['av', 'da', 'ga'], true)) {
            $this->pestanaActiva = $pestana;
            $this->resetErrorBag();
        }
    }

    public function updatedQuienesSomos(): void
    {
        $this->resetErrorBag('quienes_somos');
    }

    public function updatedTelefonoContacto(): void
    {
        $this->resetErrorBag('telefono_contacto');
    }

    public function updatedHoraApertura(): void
    {
        $this->resetErrorBag('horario');
    }

    public function updatedHoraCierre(): void
    {
        $this->resetErrorBag('horario');
    }

    public function updatedTituloAviso(): void
    {
        $this->resetErrorBag('aviso');
    }

    public function updatedTextoAviso(): void
    {
        $this->resetErrorBag('aviso');
    }

    public function guardarDatosInstitucionales(): void
    {
        $this->autorizarDocente();
        $this->resetErrorBag();
        $this->toastMensaje = null;

        $quienesLimpio = trim($this->quienes_somos);
        $telefonoLimpio = preg_replace('/[\s-]+/', '', trim($this->telefono_contacto)) ?? '';
        $correoLimpio = trim($this->correo_contacto);
        $direccionLimpia = trim($this->direccion);
        $aperturaLimpia = trim($this->hora_apertura);
        $cierreLimpio = trim($this->hora_cierre);

        // Validar TODOS los campos a la vez (RF029, RF033)
        if ($quienesLimpio === '') {
            $this->addError('quienes_somos', 'Este campo no puede quedar vacío');
        } elseif (mb_strlen($quienesLimpio) > 2000) {
            $this->addError('quienes_somos', 'El texto no puede superar 2000 caracteres');
        }

        if (! preg_match('/^\d{9}$/', $telefonoLimpio)) {
            $this->addError('telefono_contacto', 'Ingresa un número de celular válido');
        }

        if ($correoLimpio !== '' && ! filter_var($correoLimpio, FILTER_VALIDATE_EMAIL)) {
            $this->addError('correo_contacto', 'Ingresa un correo de contacto válido');
        }

        if (mb_strlen($direccionLimpia) > 255) {
            $this->addError('direccion', 'La dirección no puede superar 255 caracteres');
        }

        if ($aperturaLimpia === '' || $cierreLimpio === '' || $cierreLimpio <= $aperturaLimpia) {
            $this->addError('horario', 'La hora de cierre debe ser posterior a la de apertura');
        }

        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        try {
            $idDocente = $this->obtenerIdDocenteActual();

            DB::transaction(function () use ($idDocente, $quienesLimpio, $telefonoLimpio, $aperturaLimpia, $cierreLimpio): void {
                $info = InfoInstitucional::query()->first();

                $datos = [
                    'id_docente' => $idDocente,
                    'quienes_somos' => $quienesLimpio,
                    'telefono_contacto' => $telefonoLimpio,
                    'correo_contacto' => trim($this->correo_contacto) !== '' ? trim($this->correo_contacto) : 'contacto@dunamys.edu.pe',
                    'direccion' => trim($this->direccion) !== '' ? trim($this->direccion) : 'Plaza de Armas - Juliaca, Jr. Ica 252, Galería San Román, 3er piso.',
                    'hora_apertura' => strlen($aperturaLimpia) === 5 ? $aperturaLimpia.':00' : $aperturaLimpia,
                    'hora_cierre' => strlen($cierreLimpio) === 5 ? $cierreLimpio.':00' : $cierreLimpio,
                    'fecha_actualizado' => now(),
                ];

                if ($info) {
                    $info->update($datos);
                } else {
                    InfoInstitucional::query()->create($datos);
                }
            });

            $this->quienes_somos = $quienesLimpio;
            $this->telefono_contacto = $telefonoLimpio;
            $this->mostrarToast('Cambios guardados correctamente', 'success');
        } catch (\Throwable) {
            $this->mostrarToast('No se pudo guardar, intenta nuevamente', 'error');
        }
    }

    public function publicarAviso(): void
    {
        $this->autorizarDocente();
        $this->resetErrorBag('aviso');
        $this->toastMensaje = null;

        $titulo = trim($this->titulo_aviso);
        $texto = trim($this->texto_aviso);

        if ($titulo === '' || $texto === '') {
            $this->addError('aviso', 'Este campo no puede quedar vacío');

            return;
        }

        try {
            $idDocente = $this->obtenerIdDocenteActual();

            if ($this->editandoAvisoId) {
                $aviso = AvisoWeb::query()->findOrFail($this->editandoAvisoId);
                $aviso->update([
                    'titulo_aviso' => $titulo,
                    'texto_aviso' => $texto,
                ]);
            } else {
                AvisoWeb::query()->create([
                    'id_docente' => $idDocente,
                    'titulo_aviso' => $titulo,
                    'texto_aviso' => $texto,
                    'fecha_publicacion' => now(),
                ]);
            }

            $this->titulo_aviso = '';
            $this->texto_aviso = '';
            $this->editandoAvisoId = null;
            $this->mostrarToast('Cambios guardados correctamente', 'success');
        } catch (\Throwable) {
            $this->mostrarToast('No se pudo guardar, intenta nuevamente', 'error');
        }
    }

    public function editarAviso(int $idAviso): void
    {
        $this->autorizarDocente();
        $aviso = AvisoWeb::query()->find($idAviso);
        if (! $aviso) {
            return;
        }

        $this->editandoAvisoId = $aviso->id_aviso;
        $this->titulo_aviso = $aviso->titulo_aviso;
        $this->texto_aviso = $aviso->texto_aviso;
    }

    public function cancelarEdicionAviso(): void
    {
        $this->editandoAvisoId = null;
        $this->titulo_aviso = '';
        $this->texto_aviso = '';
        $this->resetErrorBag('aviso');
    }

    public function eliminarAviso(int $idAviso): void
    {
        $this->autorizarDocente();

        AvisoWeb::query()->whereKey($idAviso)->delete();
        if ($this->editandoAvisoId === $idAviso) {
            $this->cancelarEdicionAviso();
        }
        $this->mostrarToast('Cambios guardados correctamente', 'info');
    }

    public function agregarGaleria(): void
    {
        $this->autorizarDocente();
        $this->resetErrorBag('galeria');
        $this->toastMensaje = null;

        $titulo = trim($this->titulo_evento);
        if ($titulo === '') {
            $this->addError('galeria', 'Este campo no puede quedar vacío');

            return;
        }

        $archivoUrl = 'assets/img/galeria/galeria-01.webp';

        if ($this->archivo_galeria) {
            $extension = strtolower((string) $this->archivo_galeria->getClientOriginalExtension());
            $tamanoKb = $this->archivo_galeria->getSize() / 1024;

            if (! in_array($extension, ['webp', 'jpg', 'jpeg', 'png', 'mp4'], true) || $tamanoKb > 2048) {
                $this->addError('galeria', 'Formato o tamaño de archivo no permitido');

                return;
            }

            $rutaGuardada = $this->archivo_galeria->store('galeria', 'public');
            $archivoUrl = 'storage/'.$rutaGuardada;
        }

        try {
            $idDocente = $this->obtenerIdDocenteActual();

            GaleriaEvento::query()->create([
                'id_docente' => $idDocente,
                'titulo_evento' => $titulo,
                'tipo_multimedia' => in_array($this->tipo_multimedia, ['Foto', 'Video'], true) ? $this->tipo_multimedia : 'Foto',
                'archivo_url' => $archivoUrl,
            ]);

            $this->titulo_evento = '';
            $this->archivo_galeria = null;
            $this->mostrarToast('Cambios guardados correctamente', 'success');
        } catch (\Throwable) {
            $this->mostrarToast('No se pudo guardar, intenta nuevamente', 'error');
        }
    }

    public function eliminarGaleria(int $idGaleria): void
    {
        $this->autorizarDocente();

        GaleriaEvento::query()->whereKey($idGaleria)->delete();
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

    private function obtenerIdDocenteActual(): int
    {
        /** @var UsuarioSistema $usuario */
        $usuario = Auth::user();
        $docente = Docente::query()->where('id_persona', $usuario->id_persona)->first();

        if ($docente) {
            return $docente->id_docente;
        }

        $primerDocente = Docente::query()->first();
        if ($primerDocente) {
            return $primerDocente->id_docente;
        }

        return Docente::query()->create([
            'id_persona' => $usuario->id_persona,
            'especialidad' => 'Dirección Musical',
        ])->id_docente;
    }

    public function render(): View
    {
        $infoGuardada = InfoInstitucional::query()->first();
        $avisos = AvisoWeb::query()->orderByDesc('fecha_publicacion')->orderByDesc('id_aviso')->get();
        $galeria = GaleriaEvento::query()->orderByDesc('id_galeria')->get();

        return view('livewire.docente.info-web', [
            'infoGuardada' => $infoGuardada,
            'avisos' => $avisos,
            'galeria' => $galeria,
        ]);
    }
}
