<?php

namespace App\Livewire\Publico;

use App\Models\Reclamacion;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.publico')]
#[Title('Libro de Reclamaciones | DUNAMYS')]
class Reclamaciones extends Component
{
    public string $tipo = '';

    public string $nombres = '';

    public string $apellidos = '';

    public string $dni = '';

    public string $telefono = '';

    public string $correo = '';

    public string $domicilio = '';

    public string $servicio = '';

    public string $monto_involucrado = '';

    public string $fecha_incidente = '';

    public string $descripcion = '';

    public string $pedido_consumidor = '';

    public bool $declaracion_veracidad = false;

    public bool $consentimiento_datos = false;

    public bool $solicitudRegistrada = false;

    public string $codigoGenerado = '';

    public string $nombreResumen = '';

    public string $tipoResumen = '';

    public function updatedDeclaracionVeracidad(bool $valor): void
    {
        $this->consentimiento_datos = $valor;
        $this->resetErrorBag(['declaracion_veracidad', 'consentimiento_datos']);
    }

    public function updatedConsentimientoDatos(bool $valor): void
    {
        $this->declaracion_veracidad = $valor;
        $this->resetErrorBag(['declaracion_veracidad', 'consentimiento_datos']);
    }

    public function enviarSolicitud(): void
    {
        $this->nombres = trim($this->nombres);
        $this->apellidos = trim($this->apellidos);
        $this->dni = trim($this->dni);
        $this->telefono = trim($this->telefono);
        $this->correo = strtolower(trim($this->correo));
        $this->domicilio = trim($this->domicilio);
        $this->descripcion = trim($this->descripcion);
        $this->pedido_consumidor = trim($this->pedido_consumidor);

        if ($this->consentimiento_datos && ! $this->declaracion_veracidad) {
            $this->declaracion_veracidad = true;
        }

        $datos = $this->validate([
            'tipo' => ['required', Rule::in(['Reclamo', 'Queja'])],
            'nombres' => ['required', 'string', 'max:255'],
            'apellidos' => ['required', 'string', 'max:255'],
            'dni' => ['required', 'digits:8'],
            'telefono' => ['required', 'digits:9'],
            'correo' => ['required', 'email', 'max:255'],
            'domicilio' => ['required', 'string', 'max:255'],
            'servicio' => ['required', 'string', 'max:255'],
            'monto_involucrado' => ['nullable', 'numeric', 'min:0'],
            'fecha_incidente' => ['required', 'date', 'before_or_equal:today'],
            'descripcion' => ['required', 'string', 'max:2000'],
            'pedido_consumidor' => ['required', 'string', 'max:1000'],
            'declaracion_veracidad' => ['accepted'],
        ], [
            'required' => 'Este campo es obligatorio',
            'dni.digits' => 'El DNI debe tener 8 dígitos.',
            'telefono.digits' => 'El teléfono debe tener 9 dígitos.',
            'email' => 'Ingresa un correo electrónico válido.',
            'fecha_incidente.before_or_equal' => 'La fecha del incidente no puede ser futura.',
            'accepted' => 'Debes aceptar las declaraciones para continuar.',
        ]);

        $reclamacion = DB::transaction(fn (): Reclamacion => Reclamacion::query()->create([
            'codigo' => Reclamacion::generarSiguienteCodigo(),
            'tipo' => $datos['tipo'],
            'nombres' => $datos['nombres'],
            'apellidos' => $datos['apellidos'],
            'dni' => $datos['dni'],
            'telefono' => $datos['telefono'],
            'correo' => $datos['correo'],
            'domicilio' => $datos['domicilio'],
            'servicio' => $datos['servicio'],
            'monto_involucrado' => $datos['monto_involucrado'] !== '' && $datos['monto_involucrado'] !== null ? $datos['monto_involucrado'] : null,
            'fecha_incidente' => $datos['fecha_incidente'],
            'descripcion' => $datos['descripcion'],
            'pedido_consumidor' => $datos['pedido_consumidor'],
            'estado' => 'Pendiente',
        ]));

        $this->codigoGenerado = $reclamacion->codigo;
        $this->nombreResumen = $reclamacion->nombres.' '.$reclamacion->apellidos;
        $this->tipoResumen = $reclamacion->tipo;
        $this->solicitudRegistrada = true;

        $this->reset([
            'tipo',
            'nombres',
            'apellidos',
            'dni',
            'telefono',
            'correo',
            'domicilio',
            'servicio',
            'monto_involucrado',
            'fecha_incidente',
            'descripcion',
            'pedido_consumidor',
            'declaracion_veracidad',
            'consentimiento_datos',
        ]);
    }

    public function cerrarConfirmacion(): void
    {
        $this->solicitudRegistrada = false;
    }

    public function render(): View
    {
        return view('livewire.publico.reclamaciones');
    }
}
