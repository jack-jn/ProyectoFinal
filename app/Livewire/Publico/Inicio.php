<?php

namespace App\Livewire\Publico;

use App\Models\AvisoWeb;
use App\Models\Curso;
use App\Models\GaleriaEvento;
use App\Models\InfoInstitucional;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.publico')]
#[Title('DUNAMYS | Academia Musical')]
class Inicio extends Component
{
    public ?int $idCursoDetalle = null;

    public ?string $imagenModalUrl = null;

    public ?string $imagenModalTitulo = null;

    public function abrirModal(string $url, string $titulo): void
    {
        $this->imagenModalUrl = $url;
        $this->imagenModalTitulo = $titulo;
    }

    public function cerrarModal(): void
    {
        $this->imagenModalUrl = null;
        $this->imagenModalTitulo = null;
    }

    public function formatearHora12(?string $hora): string
    {
        $valor = trim((string) $hora);
        if (! preg_match('/^(\d{1,2}):(\d{2})/', $valor, $m)) {
            return $valor ?: '8:00 a. m.';
        }

        $h = (int) $m[1];
        $min = $m[2];
        $periodo = $h >= 12 ? 'p. m.' : 'a. m.';
        $h12 = $h % 12;
        if ($h12 === 0) {
            $h12 = 12;
        }

        return "{$h12}:{$min} {$periodo}";
    }

    public function formatearTelefonoVisual(?string $telefono): string
    {
        $limpio = preg_replace('/[\s-]+/', '', (string) $telefono) ?? '';
        if (preg_match('/^\d{9}$/', $limpio)) {
            return substr($limpio, 0, 3).' '.substr($limpio, 3, 3).' '.substr($limpio, 6, 3);
        }

        return $limpio ?: '952 233 266';
    }

    public function render(): View
    {
        $info = InfoInstitucional::query()->first();
        $cursos = Curso::query()
            ->with('instrumento')
            ->withCount([
                'detalleMatriculas as matriculados_activos_count' => fn ($q) => $q->where('estado_acceso', 'Activo'),
            ])
            ->where('estado_habilitado', true)
            ->where('modalidad', 'Virtual')
            ->orderBy('id_curso')
            ->get();

        $cursoDetalle = $cursos->firstWhere('id_curso', $this->idCursoDetalle);
        $cursoDetalle?->load(['docente.persona', 'sesiones']);

        $avisos = AvisoWeb::query()
            ->orderByDesc('fecha_publicacion')
            ->orderByDesc('id_aviso')
            ->get();

        $galeria = GaleriaEvento::query()
            ->where('tipo_multimedia', 'Foto')
            ->orderByDesc('id_galeria')
            ->take(8)
            ->get();

        $quienesSomos = $info?->quienes_somos
            ?: 'En Dunamys enseñamos a tocar instrumentos, teclado, guitarra, violín, canto y más, con una metodología práctica, moderna y personalizada.';
        $telefonoRaw = preg_replace('/[\s-]+/', '', (string) ($info?->telefono_contacto ?: '952233266')) ?: '952233266';
        $telefonoVisual = $this->formatearTelefonoVisual($telefonoRaw);
        $correoContacto = $info?->correo_contacto ?: 'contacto@dunamys.edu.pe';
        $direccion = $info?->direccion ?: 'Plaza de Armas - Juliaca, Jr. Ica 252, Galería San Román, 3er piso.';
        $horarioApertura12 = $this->formatearHora12($info?->hora_apertura ?: '08:00');
        $horarioCierre12 = $this->formatearHora12($info?->hora_cierre ?: '18:00');

        return view('livewire.publico.inicio', [
            'cursos' => $cursos,
            'cursoDetalle' => $cursoDetalle,
            'avisos' => $avisos,
            'galeria' => $galeria,
            'quienesSomos' => $quienesSomos,
            'telefonoRaw' => $telefonoRaw,
            'telefonoVisual' => $telefonoVisual,
            'correoContacto' => $correoContacto,
            'direccion' => $direccion,
            'horarioApertura12' => $horarioApertura12,
            'horarioCierre12' => $horarioCierre12,
        ]);
    }
}
