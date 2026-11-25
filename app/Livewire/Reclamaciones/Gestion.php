<?php

namespace App\Livewire\Reclamaciones;

use App\Mail\RespuestaReclamacionMail;
use App\Models\Reclamacion;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class Gestion extends Component
{
    use WithPagination;

    public string $busqueda = '';

    public string $filtroEstado = 'todos';

    public string $filtroTipo = 'todos';

    public bool $modalDetalleAbierto = false;

    #[Locked]
    public ?int $reclamacionSeleccionadaId = null;

    public string $respuesta = '';

    public bool $enviarCorreo = true;

    public ?string $toastMensaje = null;

    public string $toastTipo = 'success';

    public function boot(): void
    {
        $usuario = Auth::user();
        abort_unless($usuario
            && in_array($usuario->rol, ['docente', 'secretaria'], true)
            && $usuario->estado_cuenta === 'Activo'
            && $usuario->can('atender_reclamaciones'), 403);
    }

    public function updatedBusqueda(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroEstado(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroTipo(): void
    {
        $this->resetPage();
    }

    public function limpiarFiltros(): void
    {
        $this->busqueda = '';
        $this->filtroEstado = 'todos';
        $this->filtroTipo = 'todos';
        $this->resetPage();
    }

    public function abrirDetalle(int $idReclamacion): void
    {
        $reclamacion = Reclamacion::query()->findOrFail($idReclamacion);
        $this->reclamacionSeleccionadaId = $reclamacion->id_reclamacion;
        $this->respuesta = (string) ($reclamacion->respuesta ?? '');
        $this->enviarCorreo = true;
        $this->resetErrorBag('respuesta');
        $this->modalDetalleAbierto = true;
    }

    public function cerrarDetalle(): void
    {
        $this->modalDetalleAbierto = false;
        $this->reclamacionSeleccionadaId = null;
        $this->respuesta = '';
        $this->resetErrorBag('respuesta');
    }

    public function marcarComoAtendido(): void
    {
        $this->toastMensaje = null;
        $this->respuesta = trim($this->respuesta);

        $this->validate([
            'respuesta' => ['required', 'string', 'min:5', 'max:3000'],
        ], [
            'respuesta.required' => 'Ingresa la respuesta o detalle de atención.',
            'respuesta.min' => 'La respuesta debe tener al menos :min caracteres.',
        ]);

        $reclamacion = Reclamacion::query()->findOrFail($this->reclamacionSeleccionadaId);

        $reclamacion->update([
            'respuesta' => $this->respuesta,
            'estado' => 'Atendido',
            'atendido_por' => Auth::id(),
            'fecha_atencion' => now(),
        ]);

        $correoEnviado = true;
        if ($this->enviarCorreo && $reclamacion->correo) {
            try {
                Mail::to($reclamacion->correo)->send(new RespuestaReclamacionMail($reclamacion->fresh()));
            } catch (\Throwable $exception) {
                $correoEnviado = false;
                Log::error('Error al enviar respuesta de reclamación por correo.', [
                    'codigo' => $reclamacion->codigo,
                    'correo' => $reclamacion->correo,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        $this->cerrarDetalle();
        $this->toastTipo = $correoEnviado ? 'success' : 'info';
        $this->toastMensaje = $correoEnviado
            ? 'Reclamación marcada como atendida correctamente.'
            : 'Reclamación marcada como atendida, pero no se pudo enviar el correo de notificación.';
    }

    public function render(): View
    {
        $usuario = Auth::user();
        $esDocente = $usuario->rol === 'docente';

        $consulta = Reclamacion::query()->with('atendidoPor.persona');

        if ($this->filtroEstado !== 'todos') {
            $consulta->where('estado', $this->filtroEstado);
        }

        if ($this->filtroTipo !== 'todos') {
            $consulta->where('tipo', $this->filtroTipo);
        }

        if (trim($this->busqueda) !== '') {
            $termino = '%'.trim($this->busqueda).'%';
            $consulta->where(function ($q) use ($termino): void {
                $q->where('codigo', 'like', $termino)
                    ->orWhere('dni', 'like', $termino)
                    ->orWhere('nombres', 'like', $termino)
                    ->orWhere('apellidos', 'like', $termino);
            });
        }

        $reclamaciones = $consulta->orderByDesc('created_at')->orderByDesc('id_reclamacion')->paginate(12);

        $totalReclamaciones = Reclamacion::query()->count();
        $pendientesCount = Reclamacion::query()->where('estado', 'Pendiente')->count();
        $atendidosCount = Reclamacion::query()->where('estado', 'Atendido')->count();

        $reclamacionDetalle = $this->reclamacionSeleccionadaId
            ? Reclamacion::query()->with('atendidoPor.persona')->find($this->reclamacionSeleccionadaId)
            : null;

        return view('livewire.reclamaciones.gestion', [
            'esDocente' => $esDocente,
            'reclamaciones' => $reclamaciones,
            'totalReclamaciones' => $totalReclamaciones,
            'pendientesCount' => $pendientesCount,
            'atendidosCount' => $atendidosCount,
            'reclamacionDetalle' => $reclamacionDetalle,
        ])->layout($esDocente ? 'layouts.docente' : 'layouts.secretaria')
            ->title('Libro de Reclamaciones | Academia de Música Dunamys');
    }
}
