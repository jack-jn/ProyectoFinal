<?php

namespace App\Livewire\Docente;

use App\Models\DetalleEntregaTarea;
use App\Models\Sesion;
use App\Models\UsuarioSistema;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class Entregas extends Component
{
    #[Reactive]
    public ?int $idSesion = null;

    /** @var array<int, string> */
    public array $comentarios = [];

    public ?string $toastMensaje = null;

    public string $toastTipo = 'success';

    public function mount(?int $idSesion = null): void
    {
        $this->idSesion = $idSesion;
        $this->sincronizarComentarios();
    }

    public function updatedIdSesion(): void
    {
        $this->sincronizarComentarios();
    }

    public function sincronizarComentarios(): void
    {
        if (! $this->idSesion) {
            $this->comentarios = [];

            return;
        }

        $sesion = Sesion::query()->with('tareaAsignada.entregas')->find($this->idSesion);
        if (! $sesion || ! $sesion->tareaAsignada) {
            $this->comentarios = [];

            return;
        }

        foreach ($sesion->tareaAsignada->entregas as $entrega) {
            if (! array_key_exists($entrega->id_detalle_entrega, $this->comentarios)) {
                $this->comentarios[$entrega->id_detalle_entrega] = (string) ($entrega->comentario_docente ?? '');
            }
        }
    }

    public function calificarEntrega(int $idDetalleEntrega, string $decision): void
    {
        $this->autorizarDocente();
        $this->resetErrorBag("comentario_{$idDetalleEntrega}");
        $this->toastMensaje = null;

        if (! in_array($decision, ['Aprobada', 'Observada'], true)) {
            return;
        }

        $entrega = DetalleEntregaTarea::query()->findOrFail($idDetalleEntrega);
        $comentario = trim((string) ($this->comentarios[$idDetalleEntrega] ?? ''));

        // Regla 5 de la sección 6 (RF027): Aprobada comentario opcional; Observada required|min:5
        if ($decision === 'Observada') {
            if ($comentario === '') {
                $this->addError("comentario_{$idDetalleEntrega}", 'Este campo es obligatorio');

                return;
            }
            if (mb_strlen($comentario) < 5) {
                $this->addError("comentario_{$idDetalleEntrega}", 'El comentario debe tener al menos 5 caracteres');

                return;
            }
        }

        $entrega->update([
            'estado_revision' => $decision,
            'comentario_docente' => $comentario !== '' ? $comentario : null,
            'fecha_revision' => now(),
        ]);

        $this->comentarios[$idDetalleEntrega] = $comentario;
        $this->toastMensaje = 'Cambios guardados correctamente';
        $this->toastTipo = 'success';
    }

    public function cerrarToast(): void
    {
        $this->toastMensaje = null;
    }

    private function autorizarDocente(): void
    {
        /** @var UsuarioSistema|null $usuario */
        $usuario = Auth::user();
        abort_unless($usuario && $usuario->rol === 'docente' && $usuario->estado_cuenta === 'Activo', 403);
    }

    public function render(): View
    {
        $sesion = $this->idSesion
            ? Sesion::query()->with(['tareaAsignada.entregas.detalleMatricula.matricula.alumno.persona'])->find($this->idSesion)
            : null;

        $entregas = $sesion?->tareaAsignada
            ? $sesion->tareaAsignada->entregas
            : collect();

        foreach ($entregas as $entrega) {
            if (! array_key_exists($entrega->id_detalle_entrega, $this->comentarios)) {
                $this->comentarios[$entrega->id_detalle_entrega] = (string) ($entrega->comentario_docente ?? '');
            }
        }

        return view('livewire.docente.entregas', [
            'entregas' => $entregas,
        ]);
    }
}
