<?php

namespace App\Livewire\Docente;

use App\Models\Persona;
use App\Models\UsuarioSistema;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.docente')]
#[Title('Panel del Docente | Academia de Música Dunamys')]
class Panel extends Component
{
    #[Url(as: 'tab')]
    public string $vistaActiva = 'cursos';

    public ?int $idCursoAula = null;

    public bool $editandoAdministrador = false;

    public string $nombres = '';

    public string $apellido_paterno = '';

    public string $apellido_materno = '';

    public ?string $mensajeAdministrador = null;

    public function editarAdministrador(): void
    {
        $persona = $this->personaAdministrador();
        $this->nombres = $persona->nombres;
        $this->apellido_paterno = $persona->apellido_paterno;
        $this->apellido_materno = $persona->apellido_materno;
        $this->mensajeAdministrador = null;
        $this->resetValidation();
        $this->editandoAdministrador = true;
    }

    public function cerrarDatosAdministrador(): void
    {
        $this->editandoAdministrador = false;
        $this->resetValidation();
    }

    public function guardarAdministrador(): void
    {
        $persona = $this->personaAdministrador();
        $this->nombres = trim($this->nombres);
        $this->apellido_paterno = trim($this->apellido_paterno);
        $this->apellido_materno = trim($this->apellido_materno);

        $persona->update($this->validate([
            'nombres' => ['required', 'string', 'min:2', 'max:255', 'regex:/^[\p{L}\s.\'-]+$/u'],
            'apellido_paterno' => ['required', 'string', 'min:2', 'max:255', 'regex:/^[\p{L}\s.\'-]+$/u'],
            'apellido_materno' => ['required', 'string', 'min:2', 'max:255', 'regex:/^[\p{L}\s.\'-]+$/u'],
        ], [
            'required' => 'Este campo es obligatorio',
            'min' => 'Debe tener al menos :min caracteres',
            'max' => 'No puede superar los :max caracteres',
            'regex' => 'Ingresa solo letras y caracteres válidos',
        ]));

        $this->cerrarDatosAdministrador();
        $this->mensajeAdministrador = 'Cambios guardados correctamente';
    }

    private function personaAdministrador(): Persona
    {
        $usuario = Auth::user();
        abort_unless($usuario instanceof UsuarioSistema
            && $usuario->rol === 'docente'
            && $usuario->estado_cuenta === 'Activo'
            && ! $usuario->debe_cambiar_contrasena, 403);

        return $usuario->persona;
    }

    public function cambiarVista(string $vista): void
    {
        $this->personaAdministrador();
        if (in_array($vista, ['cursos', 'sesiones', 'alumnos', 'info', 'usuarios'], true)) {
            $this->vistaActiva = $vista;
        }
    }

    #[On('abrir-aula-curso')]
    public function abrirAulaCurso(int $idCurso): void
    {
        $this->idCursoAula = $idCurso;
        $this->vistaActiva = 'sesiones';
    }

    public function render(): View
    {
        return view('livewire.docente.panel');
    }
}
