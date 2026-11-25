<?php

use App\Livewire\Docente\Sesiones as DocenteSesiones;
use App\Livewire\Estudiante\Aula as EstudianteAula;
use App\Models\Alumno;
use App\Models\Curso;
use App\Models\DetalleEntregaTarea;
use App\Models\DetalleMatricula;
use App\Models\DetalleSesionCompletada;
use App\Models\Docente;
use App\Models\Instrumento;
use App\Models\Leccion;
use App\Models\Matricula;
use App\Models\Persona;
use App\Models\Recurso;
use App\Models\Sesion;
use App\Models\TareaAsignada;
use App\Models\TipoRecurso;
use App\Models\UsuarioSistema;
use Database\Seeders\RolesPermisosSeeder;
use Database\Seeders\VideosCursosSeeder;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

function crearEscenarioAulaAlumno(): array
{
    $instTeclado = Instrumento::query()->firstOrCreate(
        ['nombre_instrumento' => 'Teclado'],
        ['icono' => 'i-inst-piano']
    );
    $instGuitarra = Instrumento::query()->firstOrCreate(
        ['nombre_instrumento' => 'Guitarra'],
        ['icono' => 'i-inst-guitar']
    );

    $personaDoc = Persona::query()->create([
        'nombres' => 'Carlos',
        'apellido_paterno' => 'Mamani',
        'apellido_materno' => 'Quispe',
        'dni' => '75000001',
        'correo' => 'docente.aula.test@dunamys.edu.pe',
        'celular' => '951000001',
    ]);
    $docente = Docente::query()->create([
        'id_persona' => $personaDoc->id_persona,
        'especialidad' => 'Teclado y Piano',
    ]);

    $cursoActivo = Curso::query()->create([
        'id_instrumento' => $instTeclado->id_instrumento,
        'id_docente' => $docente->id_docente,
        'codigo_curso' => 'C-701',
        'nombre_curso' => 'Teclado Básico Aula Test',
        'nivel' => 'Básico',
        'modalidad' => 'Presencial',
        'costo' => 150.00,
        'cupos' => 15,
        'imagen_url' => 'assets/img/cursos/teclado.webp',
        'estado_habilitado' => true,
    ]);

    $cursoNoMatriculado = Curso::query()->create([
        'id_instrumento' => $instGuitarra->id_instrumento,
        'id_docente' => $docente->id_docente,
        'codigo_curso' => 'C-702',
        'nombre_curso' => 'Guitarra No Matriculado Test',
        'nivel' => 'Básico',
        'modalidad' => 'Presencial',
        'costo' => 150.00,
        'cupos' => 10,
        'imagen_url' => 'assets/img/cursos/guitarra.webp',
        'estado_habilitado' => true,
    ]);

    $sesion1 = Sesion::query()->create([
        'id_curso' => $cursoActivo->id_curso,
        'numero_orden' => 1,
        'titulo_sesion' => 'Bienvenida y Postura Inicial',
    ]);

    Leccion::query()->create([
        'id_sesion' => $sesion1->id_sesion,
        'codigo_leccion' => 'LEC-701-1',
        'numero_orden' => 1,
        'titulo_leccion' => 'Posición de manos',
        'descripcion' => 'Relajación de muñecas y hombros.',
    ]);

    $tarea1 = TareaAsignada::query()->create([
        'id_sesion' => $sesion1->id_sesion,
        'titulo_tarea' => 'Evidencia de postura inicial',
        'descripcion' => 'Graba 1 minuto mostrando tu postura frente al teclado.',
        'fecha_limite' => now()->addDays(7)->toDateString(),
    ]);

    $sesion2 = Sesion::query()->create([
        'id_curso' => $cursoActivo->id_curso,
        'numero_orden' => 2,
        'titulo_sesion' => 'Conociendo el Teclado y Escalas',
    ]);

    $tarea2 = TareaAsignada::query()->create([
        'id_sesion' => $sesion2->id_sesion,
        'titulo_tarea' => 'Escala de Do Mayor',
        'descripcion' => 'Ejecuta la escala de Do Mayor a 80 BPM.',
        'fecha_limite' => now()->addDays(14)->toDateString(),
    ]);

    $sesionAjena = Sesion::query()->create([
        'id_curso' => $cursoNoMatriculado->id_curso,
        'numero_orden' => 1,
        'titulo_sesion' => 'Afinación de Guitarra',
    ]);

    $personaAlumno = Persona::query()->create([
        'nombres' => 'Lucía',
        'apellido_paterno' => 'Mendoza',
        'apellido_materno' => 'Rojas',
        'dni' => '75000002',
        'fecha_nacimiento' => '2005-06-15',
        'correo' => 'lucia.aula.test@dunamys.edu.pe',
        'celular' => '951000002',
    ]);

    $usuarioAlumno = UsuarioSistema::query()->create([
        'id_persona' => $personaAlumno->id_persona,
        'correo_usuario' => 'lucia.aula.test@dunamys.edu.pe',
        'contrasena' => Hash::make('ClaveSegura123'),
        'rol' => 'alumno',
        'debe_cambiar_contrasena' => false,
        'estado_cuenta' => 'Activo',
    ]);

    test()->seed(RolesPermisosSeeder::class);

    $alumno = Alumno::query()->create([
        'id_persona' => $personaAlumno->id_persona,
        'id_apoderado' => null,
        'codigo_alumno' => 'ALU-2026-701',
    ]);

    $matricula = Matricula::query()->create([
        'id_alumno' => $alumno->id_alumno,
        'codigo_matricula' => 'MAT-2026-701',
        'fecha_matricula' => now(),
        'monto_total' => 150.00,
        'estado_matricula' => 'Confirmada',
    ]);

    $detalleActivo = DetalleMatricula::query()->create([
        'id_matricula' => $matricula->id_matricula,
        'id_curso' => $cursoActivo->id_curso,
        'fecha_inscripcion' => now(),
        'precio_cobrado' => 150.00,
        'estado_acceso' => 'Activo',
    ]);

    return compact(
        'usuarioAlumno',
        'alumno',
        'cursoActivo',
        'cursoNoMatriculado',
        'detalleActivo',
        'sesion1',
        'sesion2',
        'sesionAjena',
        'tarea1',
        'tarea2'
    );
}

test('RF035 y RF038: muestra solo cursos con estado_acceso Activo, bloquea sesiones posteriores y restringe sesiones ajenas', function () {
    $datos = crearEscenarioAulaAlumno();

    $this->actingAs($datos['usuarioAlumno'])
        ->get(route('estudiante.aula'))
        ->assertOk()
        ->assertSee('Teclado Básico Aula Test')
        ->assertDontSee('Guitarra No Matriculado Test');

    // La sesión 2 inicia bloqueada porque la sesión 1 aún no está completada
    Livewire::actingAs($datos['usuarioAlumno'])
        ->test(EstudianteAula::class)
        ->call('entregarTarea', $datos['sesion2']->id_sesion)
        ->assertHasErrors(['video_evidencia']);

    // Intentar acceder a una sesión de un curso no matriculado muestra Acceso Restringido
    Livewire::actingAs($datos['usuarioAlumno'])
        ->test(EstudianteAula::class)
        ->call('seleccionarSesion', $datos['sesionAjena']->id_sesion)
        ->assertSet('accesoNoAutorizado', true)
        ->assertSee('No tienes autorización para acceder a esta sesión');
});

test('RF039 y Regla 8: entrega de tarea por enlace de video marca sesión completada y desbloquea la siguiente sesión', function () {
    $datos = crearEscenarioAulaAlumno();

    // Validación de enlace vacío
    Livewire::actingAs($datos['usuarioAlumno'])
        ->test(EstudianteAula::class)
        ->call('cambiarVista', 'tareas', $datos['sesion1']->id_sesion)
        ->set('video_evidencia', '')
        ->call('entregarTarea', $datos['sesion1']->id_sesion)
        ->assertHasErrors(['video_evidencia']);

    // Entrega con enlace válido de YouTube
    Livewire::actingAs($datos['usuarioAlumno'])
        ->test(EstudianteAula::class)
        ->call('cambiarVista', 'tareas', $datos['sesion1']->id_sesion)
        ->set('video_evidencia', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ')
        ->call('entregarTarea', $datos['sesion1']->id_sesion)
        ->assertHasNoErrors()
        ->assertSet('mensajeExito', 'Cambios guardados correctamente')
        ->assertSet('vistaActiva', 'detalle_entrega');

    $entrega = DetalleEntregaTarea::query()
        ->where('id_tarea', $datos['tarea1']->id_tarea)
        ->where('id_detalle_matricula', $datos['detalleActivo']->id_detalle_matricula)
        ->first();

    expect($entrega)->not->toBeNull()
        ->and($entrega->video_evidencia)->toBe('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
        ->and($entrega->estado_revision)->toBe('Pendiente');

    $completada = DetalleSesionCompletada::query()
        ->where('id_detalle_matricula', $datos['detalleActivo']->id_detalle_matricula)
        ->where('id_sesion', $datos['sesion1']->id_sesion)
        ->exists();

    expect($completada)->toBeTrue();

    // Ahora la sesión 2 ya está desbloqueada ("En proceso") y permite entregar su tarea
    Livewire::actingAs($datos['usuarioAlumno'])
        ->test(EstudianteAula::class)
        ->call('cambiarVista', 'tareas', $datos['sesion2']->id_sesion)
        ->set('video_evidencia', 'https://drive.google.com/file/d/123456789/view')
        ->call('entregarTarea', $datos['sesion2']->id_sesion)
        ->assertHasNoErrors()
        ->assertSet('mensajeExito', 'Cambios guardados correctamente');
});

test('Flujo 5 y Punto 1 Sección 9: permite reenviar tarea Observada actualizando la misma fila y bloquea cambios si está Aprobada', function () {
    $datos = crearEscenarioAulaAlumno();

    $entrega = DetalleEntregaTarea::query()->create([
        'id_tarea' => $datos['tarea1']->id_tarea,
        'id_detalle_matricula' => $datos['detalleActivo']->id_detalle_matricula,
        'video_evidencia' => 'https://youtu.be/intento-inicial',
        'estado_revision' => 'Observada',
        'comentario_docente' => 'Mantén las muñecas relajadas y repite el ejercicio.',
        'fecha_entrega' => now()->subDay(),
        'fecha_revision' => now()->subHours(2),
    ]);

    // El alumno ve el comentario del docente y reenvía un nuevo enlace
    Livewire::actingAs($datos['usuarioAlumno'])
        ->test(EstudianteAula::class)
        ->call('cambiarVista', 'tareas', $datos['sesion1']->id_sesion)
        ->assertSee('Mantén las muñecas relajadas y repite el ejercicio.')
        ->set('video_evidencia', 'https://youtu.be/intento-corregido')
        ->call('entregarTarea', $datos['sesion1']->id_sesion)
        ->assertHasNoErrors()
        ->assertSet('mensajeExito', 'Cambios guardados correctamente');

    // Se actualiza la misma fila en detalle_entrega_tareas y se limpian comentario_docente y fecha_revision
    expect(DetalleEntregaTarea::query()
        ->where('id_tarea', $datos['tarea1']->id_tarea)
        ->where('id_detalle_matricula', $datos['detalleActivo']->id_detalle_matricula)
        ->count())->toBe(1);

    $entrega->refresh();
    expect($entrega->video_evidencia)->toBe('https://youtu.be/intento-corregido')
        ->and($entrega->estado_revision)->toBe('Pendiente')
        ->and($entrega->comentario_docente)->toBeNull()
        ->and($entrega->fecha_revision)->toBeNull();

    // Si el docente la aprueba, queda cerrada y no admite cambios
    $entrega->update([
        'estado_revision' => 'Aprobada',
        'comentario_docente' => 'Excelente postura.',
        'fecha_revision' => now(),
    ]);

    Livewire::actingAs($datos['usuarioAlumno'])
        ->test(EstudianteAula::class)
        ->call('cambiarVista', 'tareas', $datos['sesion1']->id_sesion)
        ->set('video_evidencia', 'https://youtu.be/intento-prohibido')
        ->call('entregarTarea', $datos['sesion1']->id_sesion)
        ->assertHasErrors(['video_evidencia']);

    $entrega->refresh();
    expect($entrega->video_evidencia)->toBe('https://youtu.be/intento-corregido')
        ->and($entrega->estado_revision)->toBe('Aprobada');
});

test('incrusta videos de YouTube y Vimeo solo con permiso para ver recursos', function () {
    $datos = crearEscenarioAulaAlumno();
    $tipo = TipoRecurso::firstOrCreate(['nombre_tipo' => 'URL']);
    $leccion = $datos['sesion1']->lecciones()->firstOrFail();

    foreach ([
        'YouTube corto' => 'https://youtu.be/dQw4w9WgXcQ',
        'YouTube watch' => 'https://www.youtube.com/watch?v=cbBM9pzVf0M',
        'YouTube Shorts' => 'https://www.youtube.com/shorts/QsSEevleUGc?feature=share',
        'Vimeo' => 'https://vimeo.com/12345678',
        'Vimeo player' => 'https://player.vimeo.com/video/87654321?autoplay=1',
        'Enlace externo' => 'https://youtube.com.example.com/watch?v=dQw4w9WgXcQ',
    ] as $titulo => $url) {
        Recurso::create([
            'id_tipo_recurso' => $tipo->id_tipo_recurso,
            'id_leccion' => $leccion->id_leccion,
            'titulo_recurso' => $titulo,
            'url_recurso' => $url,
        ]);
    }

    $usuario = $datos['usuarioAlumno'];
    $this->actingAs($usuario)->get(route('estudiante.aula'))
        ->assertOk()
        ->assertSee('src="https://www.youtube.com/embed/dQw4w9WgXcQ"', false)
        ->assertSee('src="https://www.youtube.com/embed/cbBM9pzVf0M"', false)
        ->assertSee('src="https://www.youtube.com/embed/QsSEevleUGc"', false)
        ->assertSee('src="https://player.vimeo.com/video/12345678"', false)
        ->assertSee('src="https://player.vimeo.com/video/87654321"', false)
        ->assertDontSee('src="https://youtube.com.example.com', false);

    $usuario->syncPermissions(['aula_virtual']);
    $this->actingAs($usuario)->get(route('estudiante.aula'))
        ->assertOk()
        ->assertDontSee('https://www.youtube.com/embed')
        ->assertDontSee('https://player.vimeo.com/video');
});

test('carga los cuatro videos de Teclado sin duplicarlos y los muestra en sus tarjetas del aula', function () {
    $datos = crearEscenarioAulaAlumno();
    $curso = $datos['cursoActivo'];
    $curso->update(['codigo_curso' => 'C-001']);

    $this->seed(VideosCursosSeeder::class);
    $sesion = $datos['sesion1'];
    $guitarra = $datos['cursoNoMatriculado']->sesiones()->where('numero_orden', 1)->firstOrFail();
    $videoGuitarra = $guitarra->lecciones()->firstOrFail()->recursos()->firstOrFail();
    expect($videoGuitarra->url_recurso)->toBe('https://www.youtube.com/embed/eJGs9_kgRyg');

    $videoGuitarra->update(['url_recurso' => 'https://vimeo.com/12345678']);
    $this->seed(VideosCursosSeeder::class);

    expect($videoGuitarra->fresh()->url_recurso)->toBe('https://vimeo.com/12345678')
        ->and($guitarra->lecciones()->count())->toBe(1)
        ->and($sesion->lecciones()->pluck('numero_orden')->all())->toBe([1, 2, 3])
        ->and($sesion->tareaAsignada()->firstOrFail()->id_tarea)->toBe($datos['tarea1']->id_tarea);

    foreach ($sesion->lecciones()->get() as $leccion) {
        $recurso = $leccion->recursos()->sole();
        expect($recurso->id_tarea)->toBeNull()
            ->and($recurso->tipoRecurso->nombre_tipo)->toBe('URL');
    }

    $videoTarea = $datos['tarea1']->recursos()->sole();
    expect($videoTarea->id_leccion)->toBeNull()
        ->and($videoTarea->tipoRecurso->nombre_tipo)->toBe('URL');

    $leccion = $sesion->lecciones()->firstOrFail();
    foreach ([
        ['PDF', 'Guía de digitación', 'recursos/guia-digitacion-teclado.pdf'],
        ['MP4', 'Demostración', 'storage/recursos/videos/digitacion.mp4'],
        ['URL', 'Lectura complementaria', 'https://example.org/teclado'],
    ] as [$tipo, $titulo, $ruta]) {
        $leccion->recursos()->create([
            'id_tipo_recurso' => TipoRecurso::firstOrCreate(['nombre_tipo' => $tipo])->id_tipo_recurso,
            'titulo_recurso' => $titulo,
            'url_recurso' => $ruta,
        ]);
    }

    $docente = $curso->docente->persona->usuarioSistema()->create([
        'correo_usuario' => 'docente.aula.test@dunamys.edu.pe',
        'contrasena' => Hash::make('Clave2026'),
        'rol' => 'docente',
        'estado_cuenta' => 'Activo',
    ]);

    $verificarLecciones = function ($panel): void {
        foreach (['cbBM9pzVf0M', '9TAtZpoUUzo', 'QsSEevleUGc'] as $video) {
            $url = 'https://www.youtube.com/embed/'.$video;
            $panel->assertSeeHtml('src="'.$url.'"')->assertDontSeeHtml('href="'.$url.'"');
        }

        $pdf = asset('storage/recursos/guia-digitacion-teclado.pdf');
        $panel->assertDontSee('DUNAMYS HD')
            ->assertSeeHtml('href="'.$pdf.'"')
            ->assertDontSeeHtml('src="'.$pdf.'"')
            ->assertSeeHtml('href="https://example.org/teclado"')
            ->assertSeeHtml('<video controls')
            ->assertSeeHtml('src="'.asset('storage/recursos/videos/digitacion.mp4').'"');
    };

    $verificarTarea = function ($panel): void {
        $panel->assertSee('Práctica de la Sesión 1: Demostración en teclado')
            ->assertSeeHtml('src="https://www.youtube.com/embed/wkVnb41ckIU"')
            ->assertDontSeeHtml('href="https://www.youtube.com/embed/wkVnb41ckIU"');
    };

    $aula = Livewire::actingAs($datos['usuarioAlumno'])->test(EstudianteAula::class);
    $verificarLecciones($aula);
    $verificarTarea($aula->call('cambiarVista', 'tareas', $sesion->id_sesion));

    $panelDocente = Livewire::actingAs($docente)->test(DocenteSesiones::class, ['idCursoInicial' => $curso->id_curso]);
    $verificarLecciones($panelDocente);
    $verificarTarea($panelDocente->call('cambiarSubPestana', 'tareas'));
});
