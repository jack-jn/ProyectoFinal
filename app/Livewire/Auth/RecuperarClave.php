<?php

namespace App\Livewire\Auth;

use App\Models\UsuarioSistema;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.auth-dunamys')]
#[Title('Recuperar Contraseña | Academia de Música Dunamys')]
class RecuperarClave extends Component
{
    public const ALFABETO_CODIGO = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    public string $correo = '';

    public int $paso = 1;

    public string $codigoGenerado = '';

    public string $codigoIngresado = '';

    public int $intentosRestantes = 5;

    public int $expiraEnTimestamp = 0;

    public string $nueva_contrasena = '';

    public string $repetir_contrasena = '';

    public ?string $alertaMensaje = null;

    public string $alertaTipo = 'danger';

    public function enviarCodigo(): void
    {
        $this->alertaMensaje = null;
        $this->resetErrorBag();

        $correoLimpio = Str::lower(trim($this->correo));

        if ($correoLimpio === '') {
            $this->addError('correo', 'Este campo es obligatorio');

            return;
        }

        if (! filter_var($correoLimpio, FILTER_VALIDATE_EMAIL)) {
            $this->addError('correo', 'Ingresa un formato de correo válido (ejemplo: usuario@dunamys.pe)');

            return;
        }

        $usuario = UsuarioSistema::query()
            ->where('correo_usuario', $correoLimpio)
            ->first();

        if (! $usuario) {
            $this->addError('correo', 'Este correo no está registrado en el sistema.');

            return;
        }

        $this->correo = $usuario->correo_usuario;

        if (! $this->emitirNuevoCodigo($usuario->correo_usuario)) {
            return;
        }

        $this->paso = 2;
    }

    public function reenviarCodigo(): void
    {
        if ($this->correo === '') {
            return;
        }

        if (! $this->emitirNuevoCodigo($this->correo)) {
            return;
        }

        $this->alertaTipo = 'warning';
        $this->alertaMensaje = 'Se ha generado un nuevo código y el anterior ha sido invalidado.';
    }

    public function verificarCodigo(): void
    {
        $this->alertaMensaje = null;
        $this->resetErrorBag('codigoIngresado');

        if ($this->codigoGenerado === '' || now()->timestamp > $this->expiraEnTimestamp) {
            $this->codigoGenerado = '';
            $this->alertaTipo = 'warning';
            $this->alertaMensaje = 'El código de 6 caracteres ha vencido (plazo de 10 minutos superado). Solicita un nuevo código.';

            return;
        }

        $ingresado = Str::upper(trim($this->codigoIngresado));
        if (strlen($ingresado) < 6) {
            $this->addError('codigoIngresado', 'Por favor completa los 6 caracteres del código.');

            return;
        }

        if ($ingresado !== $this->codigoGenerado) {
            $this->intentosRestantes--;

            if ($this->intentosRestantes <= 0) {
                $this->codigoGenerado = '';
                $this->alertaTipo = 'danger';
                $this->alertaMensaje = 'Has superado el límite de 5 intentos fallidos. Por seguridad, este código fue invalidado. Solicita uno nuevo.';
            } else {
                $this->addError('codigoIngresado', "Código incorrecto. Te quedan {$this->intentosRestantes} intento(s).");
            }

            return;
        }

        $this->codigoGenerado = '';
        $this->paso = 3;
    }

    public function guardarNuevaContrasena(): void
    {
        $this->alertaMensaje = null;
        $this->resetErrorBag();

        if ($this->paso !== 3) {
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

        $usuario = UsuarioSistema::query()
            ->where('correo_usuario', $this->correo)
            ->first();

        if (! $usuario) {
            $this->alertaTipo = 'danger';
            $this->alertaMensaje = 'No se pudo guardar, intenta nuevamente';

            return;
        }

        $usuario->update([
            'contrasena' => Hash::make($this->nueva_contrasena),
            'password_temporal' => Crypt::encryptString($this->nueva_contrasena),
            'debe_cambiar_contrasena' => false,
        ]);

        DB::table('password_reset_tokens')->where('email', $usuario->correo_usuario)->delete();

        session()->flash('status', '¡Contraseña actualizada con éxito! Ya puedes iniciar sesión.');
        $this->redirectRoute('login');
    }

    private function emitirNuevoCodigo(string $correoDestino): bool
    {
        $alfabeto = self::ALFABETO_CODIGO;
        $longitudAlfabeto = strlen($alfabeto);
        $codigo = '';

        for ($i = 0; $i < 6; $i++) {
            $codigo .= $alfabeto[random_int(0, $longitudAlfabeto - 1)];
        }

        $this->codigoGenerado = $codigo;
        $this->codigoIngresado = '';
        $this->intentosRestantes = 5;
        $this->expiraEnTimestamp = now()->addMinutes(10)->timestamp;

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $correoDestino],
            [
                'token' => Hash::make($codigo),
                'created_at' => now(),
            ]
        );

        try {
            $enlaceRecuperacion = route('recuperar-clave', ['correo' => $correoDestino, 'codigo' => $codigo]);
            Mail::raw(
                "Academia de Música Dunamys - Recuperación de contraseña.\nCódigo de verificación: {$codigo}\nEnlace: {$enlaceRecuperacion}",
                function ($message) use ($correoDestino): void {
                    $message->to($correoDestino)
                        ->subject('Recuperación de Contraseña | Academia de Música Dunamys');
                }
            );

            return true;
        } catch (\Throwable $exception) {
            Log::error('Error al enviar código de recuperación por correo.', [
                'correo' => $correoDestino,
                'error' => $exception->getMessage(),
            ]);
            $this->codigoGenerado = '';
            $this->alertaTipo = 'danger';
            $this->alertaMensaje = 'No se pudo enviar el código a tu correo electrónico. Verifica la configuración SMTP o intenta nuevamente.';

            return false;
        }
    }

    public function render(): View
    {
        return view('livewire.auth.recuperar-clave');
    }
}
