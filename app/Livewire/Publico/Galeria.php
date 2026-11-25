<?php

namespace App\Livewire\Publico;

use App\Models\GaleriaEvento;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.publico')]
#[Title('Galería | DUNAMYS Academia Musical')]
class Galeria extends Component
{
    public string $filtro = 'all';

    public ?string $imagenModalUrl = null;

    public ?string $imagenModalTitulo = null;

    public function filtrar(string $tipo): void
    {
        if (in_array($tipo, ['all', 'Foto', 'Video'], true)) {
            $this->filtro = $tipo;
        }
    }

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

    public function render(): View
    {
        $query = GaleriaEvento::query()->orderByDesc('id_galeria');
        if ($this->filtro !== 'all') {
            $query->where('tipo_multimedia', $this->filtro);
        }

        return view('livewire.publico.galeria', [
            'eventos' => $query->get(),
        ]);
    }
}
