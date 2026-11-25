<?php

use App\Livewire\Docente\UsuariosPermisos;
use App\Livewire\Publico\Reclamaciones as PublicoReclamaciones;
use App\Livewire\Reclamaciones\Gestion as ReclamacionesGestion;
use App\Mail\RespuestaReclamacionMail;
use App\Models\Persona;
use App\Models\Reclamacion;
use App\Models\Secretaria;
use App\Models\UsuarioSistema;
use Database\Seeders\RolesPermisosSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

function prepararActoresReclamaciones(): array
{
    $pDocente = Persona::query()->create([
        'nombres' => 'Carlos',
        'apellido_paterno' => 'Mamani',
        'apellido_materno' => 'Quispe',
        'dni' => '45001199',
        'correo' => 'docente.rec@dunamys.edu.pe',
        'celular' => '952233266',
    ]);
    $docente = UsuarioSistema::query()->create([
        'id_persona' => $pDocente->id_persona,
        'correo_usuario' => 'docente.rec@dunamys.edu.pe',
        'contrasena' => Hash::make('12345678'),
        'rol' => 'docente',
        'estado_cuenta' => 'Activo',
    ]);

    $pSec = Persona::query()->create([
        'nombres' => 'María',
        'apellido_paterno' => 'Condori',
        'apellido_materno' => 'Flores',
        'dni' => '70112299',
        'correo' => 'secretaria.rec@dunamys.edu.pe',
        'celular' => '999739090',
    ]);
    $secretaria = UsuarioSistema::query()->create([
        'id_persona' => $pSec->id_persona,
        'correo_usuario' => 'secretaria.rec@dunamys.edu.pe',
        'contrasena' => Hash::make('12345678'),
        'rol' => 'secretaria',
        'estado_cuenta' => 'Activo',
    ]);
    Secretaria::query()->create([
        'id_persona' => $pSec->id_persona,
        'codigo_secretaria' => 'SEC-REC-1',
        'turno_atencion' => 'Regular',
    ]);

    test()->seed(RolesPermisosSeeder::class);

    return [$docente, $secretaria];
}

test('guarda reclamacion publica con codigo correlativo y permite atenderla desde Docente y Secretaria', function () {
    Mail::fake();
    [$docente, $secretaria] = prepararActoresReclamaciones();

    Livewire::test(PublicoReclamaciones::class)
        ->set('tipo', 'Reclamo')
        ->set('nombres', 'Lucía')
        ->set('apellidos', 'Mendoza Rojas')
        ->set('dni', '74125896')
        ->set('telefono', '987654321')
        ->set('correo', 'lucia.reclamo@dunamys.edu.pe')
        ->set('domicilio', 'Jr. Lima 123, Juliaca')
        ->set('servicio', 'Curso de piano')
        ->set('monto_involucrado', '80.00')
        ->set('fecha_incidente', now()->toDateString())
        ->set('descripcion', 'El enlace del material PDF no abría correctamente.')
        ->set('pedido_consumidor', 'Habilitar el enlace del material PDF.')
        ->set('declaracion_veracidad', false)
        ->call('enviarSolicitud')
        ->assertHasErrors(['declaracion_veracidad'])
        ->set('declaracion_veracidad', true)
        ->call('enviarSolicitud')
        ->assertHasNoErrors()
        ->assertSet('solicitudRegistrada', true)
        ->assertSet('codigoGenerado', 'REC-0001');

    $reclamo = Reclamacion::query()->where('codigo', 'REC-0001')->firstOrFail();
    expect($reclamo->estado)->toBe('Pendiente');

    $this->actingAs($docente)
        ->get(route('docente.reclamaciones'))
        ->assertOk()
        ->assertSee('REC-0001')
        ->assertSee('Lucía Mendoza Rojas');

    Livewire::actingAs($secretaria)
        ->test(ReclamacionesGestion::class)
        ->assertSee('REC-0001')
        ->call('abrirDetalle', $reclamo->id_reclamacion)
        ->assertSet('modalDetalleAbierto', true)
        ->set('respuesta', 'Se actualizó el enlace del PDF y ya se encuentra disponible.')
        ->set('enviarCorreo', true)
        ->call('marcarComoAtendido')
        ->assertHasNoErrors();

    $reclamo->refresh();
    expect($reclamo->estado)->toBe('Atendido')
        ->and($reclamo->atendido_por)->toBe($secretaria->id_usuario)
        ->and($reclamo->fecha_atencion)->not->toBeNull();

    Mail::assertSent(RespuestaReclamacionMail::class, function (RespuestaReclamacionMail $mail): bool {
        return $mail->hasTo('lucia.reclamo@dunamys.edu.pe');
    });
});

test('al quitar el permiso atender_reclamaciones a secretaria desaparece del sidebar y bloquea la ruta con 403', function () {
    [$docente, $secretaria] = prepararActoresReclamaciones();

    $this->actingAs($secretaria)
        ->get(route('secretaria.panel'))
        ->assertOk()
        ->assertSee('Reclamaciones');

    $this->actingAs($secretaria)
        ->get(route('secretaria.reclamaciones'))
        ->assertOk();

    Livewire::actingAs($docente)
        ->test(UsuariosPermisos::class)
        ->set("permisosSeleccionados.{$secretaria->id_usuario}", ['resumen', 'pendientes'])
        ->call('guardarPermisos', $secretaria->id_usuario)
        ->assertHasNoErrors();

    $secretaria->refresh();
    expect($secretaria->can('atender_reclamaciones'))->toBeFalse();

    $this->actingAs($secretaria)
        ->get(route('secretaria.panel'))
        ->assertOk()
        ->assertDontSee(route('secretaria.reclamaciones'));

    $this->actingAs($secretaria)
        ->get(route('secretaria.reclamaciones'))
        ->assertForbidden();
});
