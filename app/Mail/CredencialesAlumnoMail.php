<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CredencialesAlumnoMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  list<string>  $cursosHabilitados
     */
    public function __construct(
        public string $nombreCompleto,
        public string $codigoMatricula,
        public array $cursosHabilitados,
        public string $correoAcceso,
        public ?string $passwordTemporal = null,
        public bool $esCuentaNueva = true,
    ) {}

    public function envelope(): Envelope
    {
        $asunto = $this->esCuentaNueva
            ? '¡Bienvenido a Academia de Música Dunamys! Tu pago fue validado'
            : '¡Pago validado! Nuevo curso habilitado en Academia de Música Dunamys';

        return new Envelope(
            subject: $asunto,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.credenciales-alumno',
        );
    }
}
