<?php

namespace App\Livewire\Auth;

use App\Models\UsuarioSistema;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.auth-dunamys')]
#[Title('Cambiar Contraseña Inicial | Academia de Música Dunamys')]
class CambiarClave extends Component
{
    public string $nueva_contrasena = '';

    public string $repetir_contrasena = '';

    public function guardarContrasena(): void
    {
        $this->resetErrorBag();

        /** @var UsuarioSistema|null $usuario */
        $usuario = Auth::user();

        if (! $usuario) {
            $this->redirectRoute('login');

            return;
        }

        if ($this->nueva_contrasena === '') {
            $this->addError('nueva_contrasena', 'Este campo es obligatorio');

            return;
        }

        $cumpleLongitud = strlen($this->nueva_contrasena) >= 8;
        $cumpleLetra = (bool) preg_match('/[a-zA-ZáéíóúÁÉÍÓÚñÑ]/', $this->nueva_contrasena);
        $cumpleNumero = (bool) preg_match('/[0-9]/', $this->nueva_contrasena);

        if (! ($cumpleLongitud && $cumpleLetra && $cumpleNumero)) {
            $this->addError('nueva_contrasena', 'La contraseña debe tener mínimo 8 caracteres, al menos una letra y un número.');

            return;
        }

        if ($this->nueva_contrasena !== $this->repetir_contrasena) {
            $this->addError('repetir_contrasena', 'Las contraseñas no coinciden. Escríbelas idénticas.');

            return;
        }

        $usuario->update([
            'contrasena' => Hash::make($this->nueva_contrasena),
            'password_temporal' => Crypt::encryptString($this->nueva_contrasena),
            'debe_cambiar_contrasena' => false,
        ]);

        $rutaDestino = match ($usuario->rol) {
            'docente' => 'docente.panel',
            'secretaria' => 'secretaria.panel',
            default => 'estudiante.aula',
        };

        $this->redirectRoute($rutaDestino);
    }

    public function render(): View
    {
        return view('livewire.auth.cambiar-clave');
    }
}
