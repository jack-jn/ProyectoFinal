<?php

use App\Livewire\Docente\InfoWeb;
use App\Models\Docente;
use App\Models\InfoInstitucional;
use App\Models\Persona;
use App\Models\UsuarioSistema;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

function crearDocenteConInfoInicial(): array
{
    $persona = Persona::query()->create([
        'nombres' => 'Carlos',
        'apellido_paterno' => 'Mamani',
        'apellido_materno' => 'Quispe',
        'dni' => '45001122',
        'fecha_nacimiento' => '1985-04-15',
        'correo' => 'docente@dunamys.edu.pe',
        'celular' => '952233266',
    ]);

    $usuario = UsuarioSistema::query()->create([
        'id_persona' => $persona->id_persona,
        'correo_usuario' => 'docente@dunamys.edu.pe',
        'contrasena' => Hash::make('12345678'),
        'rol' => 'docente',
        'estado_cuenta' => 'Activo',
        'debe_cambiar_contrasena' => false,
    ]);

    $docente = Docente::query()->create([
        'id_persona' => $persona->id_persona,
        'especialidad' => 'Piano y Teclado',
    ]);

    $info = InfoInstitucional::query()->create([
        'id_docente' => $docente->id_docente,
        'quienes_somos' => 'Texto institucional inicial de Dunamys.',
        'telefono_contacto' => '952233266',
        'correo_contacto' => 'contacto@dunamys.edu.pe',
        'direccion' => 'Jr. Ica 252, Juliaca',
        'hora_apertura' => '08:00:00',
        'hora_cierre' => '18:00:00',
        'fecha_actualizado' => now(),
    ]);

    return [$usuario, $docente, $info];
}

test('RF029, RF032 y RF033: el docente edita textos, celular y horarios y se reflejan de inmediato en la web publica', function () {
    [$usuario] = crearDocenteConInfoInicial();

    Livewire::actingAs($usuario)
        ->test(InfoWeb::class)
        ->set('quienes_somos', 'Nueva reseña oficial de la Academia Dunamys en Juliaca.')
        ->set('telefono_contacto', '987654321')
        ->set('hora_apertura', '09:00')
        ->set('hora_cierre', '19:30')
        ->call('guardarDatosInstitucionales')
        ->assertHasNoErrors()
        ->assertSet('toastMensaje', 'Cambios guardados correctamente');

    $this->get('/')
        ->assertOk()
        ->assertSee('Nueva reseña oficial de la Academia Dunamys en Juliaca.')
        ->assertSee('987 654 321')
        ->assertSee('9:00 a. m.')
        ->assertSee('7:30 p. m.');
});

test('RF029-02 y RF033-02/03: valida todos los campos a la vez y conserva el texto anterior si hay errores', function () {
    [$usuario, , $info] = crearDocenteConInfoInicial();

    Livewire::actingAs($usuario)
        ->test(InfoWeb::class)
        ->set('quienes_somos', '   ')
        ->set('telefono_contacto', '95223A266')
        ->set('hora_apertura', '18:00')
        ->set('hora_cierre', '08:00')
        ->call('guardarDatosInstitucionales')
        ->assertHasErrors([
            'quienes_somos',
            'telefono_contacto',
            'horario',
        ])
        ->assertSee('Este campo no puede quedar vacío')
        ->assertSee('Ingresa un número de celular válido')
        ->assertSee('La hora de cierre debe ser posterior a la de apertura');

    expect($info->fresh()->quienes_somos)->toBe('Texto institucional inicial de Dunamys.')
        ->and($info->fresh()->telefono_contacto)->toBe('952233266');
});

test('RF032-02: ante fallo de conexion muestra el mensaje exacto y conserva la version anterior', function () {
    [$usuario, , $info] = crearDocenteConInfoInicial();

    InfoInstitucional::updating(function (): never {
        throw new RuntimeException('Base de datos no disponible');
    });

    Livewire::actingAs($usuario)
        ->test(InfoWeb::class)
        ->set('quienes_somos', 'Intento que no debe guardarse')
        ->call('guardarDatosInstitucionales')
        ->assertSet('toastMensaje', 'No se pudo guardar, intenta nuevamente');

    expect($info->fresh()->quienes_somos)->toBe('Texto institucional inicial de Dunamys.');
});

test('RF032: el docente publica un aviso web y se refleja en la pagina publica', function () {
    [$usuario] = crearDocenteConInfoInicial();

    Livewire::actingAs($usuario)
        ->test(InfoWeb::class)
        ->call('cambiarPestana', 'av')
        ->set('titulo_aviso', 'Concierto de Gala 2026')
        ->set('texto_aviso', 'Inscripciones abiertas para el recital de clausura.')
        ->call('publicarAviso')
        ->assertSet('toastMensaje', 'Cambios guardados correctamente');

    $this->get('/')
        ->assertOk()
        ->assertSee('Concierto de Gala 2026')
        ->assertSee('Inscripciones abiertas para el recital de clausura.');
});
