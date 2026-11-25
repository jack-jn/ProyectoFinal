<?php

namespace App\Livewire\Auth;

use App\Models\UsuarioSistema;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.auth-dunamys')]
#[Title('Iniciar Sesión | Academia de Música Dunamys')]
class Login extends Component
{
    public string $correo_usuario = '';

    public string $contrasena = '';

    public bool $mostrarContrasena = false;

    public ?string $errorGeneral = null;

    public bool $esAdvertencia = false;

    public function mount(): void
    {
        if (session()->has('auth_error')) {
            $this->errorGeneral = (string) session('auth_error');
            $this->esAdvertencia = true;
        }
    }

    public function alternarVisibilidadClave(): void
    {
        $this->mostrarContrasena = ! $this->mostrarContrasena;
    }

    public function updatedCorreoUsuario(): void
    {
        $this->resetErrorBag('correo_usuario');
        $this->errorGeneral = null;
    }

    public function updatedContrasena(): void
    {
        $this->resetErrorBag('contrasena');
        $this->errorGeneral = null;
    }

    public function ingresar(): void
    {
        $this->errorGeneral = null;
        $this->esAdvertencia = false;

        $this->validate([
            'correo_usuario' => ['required', 'email'],
            'contrasena' => ['required', 'string'],
        ], [
            'correo_usuario.required' => 'Este campo es obligatorio',
            'correo_usuario.email' => 'Ingresa un formato de correo válido (ejemplo: usuario@dunamys.pe)',
            'contrasena.required' => 'Este campo es obligatorio',
        ]);

        $correoLimpio = Str::lower(trim($this->correo_usuario));
        $throttleKey = 'dunamys_login:'.Str::transliterate($correoLimpio.'|'.request()->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $segundos = RateLimiter::availableIn($throttleKey);
            $this->esAdvertencia = true;
            $this->errorGeneral = "Demasiados intentos fallidos. Por seguridad, el acceso está bloqueado durante {$segundos} segundos.";

            return;
        }

        $usuario = UsuarioSistema::query()
            ->where('correo_usuario', $correoLimpio)
            ->first();

        if (! $usuario || ! Hash::check($this->contrasena, $usuario->contrasena)) {
            RateLimiter::hit($throttleKey, 30);
            $intentos = RateLimiter::attempts($throttleKey);

            if ($intentos >= 5) {
                $segundos = RateLimiter::availableIn($throttleKey);
                $this->esAdvertencia = true;
                $this->errorGeneral = "Demasiados intentos fallidos. Por seguridad, el acceso está bloqueado durante {$segundos} segundos.";
            } else {
                $this->errorGeneral = "Correo o contraseña incorrectos. (Intento {$intentos} de 5)";
            }

            return;
        }

        if ($usuario->estado_cuenta !== 'Activo') {
            $this->esAdvertencia = true;
            $this->errorGeneral = 'Tu cuenta se encuentra inactiva. Comunícate con administración.';

            return;
        }

        RateLimiter::clear($throttleKey);

        Auth::login($usuario);
        session()->regenerate();

        if ($usuario->debe_cambiar_contrasena) {
            $this->redirectRoute('cambiar-clave');

            return;
        }

        $rutaDestino = match ($usuario->rol) {
            'docente' => 'docente.panel',
            'secretaria' => 'secretaria.panel',
            default => 'estudiante.aula',
        };

        $this->redirectRoute($rutaDestino);
    }

    public function render(): View
    {
        return view('livewire.auth.login');
    }
}
