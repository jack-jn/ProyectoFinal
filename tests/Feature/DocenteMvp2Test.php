<?php

use App\Livewire\Docente\Cursos;
use App\Livewire\Docente\Entregas;
use App\Livewire\Docente\Panel;
use App\Livewire\Docente\Sesiones;
use App\Models\Alumno;
use App\Models\Curso;
use App\Models\DetalleEntregaTarea;
use App\Models\DetalleMatricula;
use App\Models\DetalleSesionCompletada;
use App\Models\DiaSemana;
use App\Models\Docente;
use App\Models\Instrumento;
use App\Models\Matricula;
use App\Models\Persona;
use App\Models\Sesion;
use App\Models\TareaAsignada;
use App\Models\TipoRecurso;
use App\Models\UsuarioSistema;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

function prepararEscenarioDocenteMvp2(): array
{
    $pDocente = Persona::query()->create([
        'nombres' => 'Carlos',
        'apellido_paterno' => 'Mamani',
        'apellido_materno' => 'Quispe',
        'dni' => '45001122',
        'correo' => 'docente@dunamys.edu.pe',
        'celular' => '952233266',
    ]);

    $usuarioDocente = UsuarioSistema::query()->create([
        'id_persona' => $pDocente->id_persona,
        'correo_usuario' => 'docente@dunamys.edu.pe',
        'contrasena' => Hash::make('12345678'),
        'rol' => 'docente',
        'estado_cuenta' => 'Activo',
        'debe_cambiar_contrasena' => false,
    ]);

    $docente = Docente::query()->create([
        'id_persona' => $pDocente->id_persona,
        'especialidad' => 'Teclado y Piano',
    ]);

    $instrumento = Instrumento::query()->create([
        'nombre_instrumento' => 'Teclado',
        'icono' => 'i-inst-piano',
    ]);

    DiaSemana::query()->create(['nombre_dia' => 'Lunes']);
    DiaSemana::query()->create(['nombre_dia' => 'Miércoles']);

    $curso = Curso::query()->create([
        'id_docente' => $docente->id_docente,
        'id_instrumento' => $instrumento->id_instrumento,
        'codigo_curso' => 'C-001',
        'nombre_curso' => 'Teclado Básico',
        'nivel' => 'Básico',
        'modalidad' => 'Presencial y virtual',
        'costo' => 80.00,
        'cupos' => 10,
        'imagen_url' => 'assets/img/cursos/teclado.webp',
        'estado_habilitado' => true,
    ]);

    $pAlumno = Persona::query()->create([
        'nombres' => 'Lucía',
        'apellido_paterno' => 'Mendoza',
        'apellido_materno' => 'Cahuana',
        'dni' => '75889900',
        'correo' => 'alumno@dunamys.edu.pe',
        'celular' => '951445566',
    ]);

    $alumno = Alumno::query()->create([
        'id_persona' => $pAlumno->id_persona,
        'codigo_alumno' => 'ALU-2026-001',
    ]);

    $matricula = Matricula::query()->create([
        'id_alumno' => $alumno->id_alumno,
        'codigo_matricula' => 'MAT-2026-001',
        'fecha_matricula' => now(),
        'monto_total' => 80.00,
        'estado_matricula' => 'Confirmada',
    ]);

    $detalleMatricula = DetalleMatricula::query()->create([
        'id_matricula' => $matricula->id_matricula,
        'id_curso' => $curso->id_curso,
        'fecha_inscripcion' => now(),
        'precio_cobrado' => 80.00,
        'estado_acceso' => 'Activo',
    ]);

    return [$usuarioDocente, $docente, $instrumento, $curso, $detalleMatricula];
}

test('RF021, RF022 y RF023: valida cupos minimos segun matriculados activos y deshabilita en vez de eliminar curso con alumnos', function () {
    [$usuarioDocente, , , $curso] = prepararEscenarioDocenteMvp2();

    // Hay 1 alumno activo en $curso: intentar bajar cupos a 0 o menos de matriculados_activos
    // Agreguemos un segundo matriculado activo para probar cupos = 1 < 2
    $p2 = Persona::query()->create([
        'nombres' => 'Mateo',
        'apellido_paterno' => 'Rojas',
        'apellido_materno' => 'Silva',
        'dni' => '71223344',
        'correo' => 'mateo@dunamys.edu.pe',
        'celular' => '987654321',
    ]);
    $al2 = Alumno::query()->create(['id_persona' => $p2->id_persona, 'codigo_alumno' => 'ALU-2026-002']);
    $mat2 = Matricula::query()->create([
        'id_alumno' => $al2->id_alumno,
        'codigo_matricula' => 'MAT-2026-002',
        'fecha_matricula' => now(),
        'monto_total' => 80.00,
        'estado_matricula' => 'Confirmada',
    ]);
    DetalleMatricula::query()->create([
        'id_matricula' => $mat2->id_matricula,
        'id_curso' => $curso->id_curso,
        'fecha_inscripcion' => now(),
        'precio_cobrado' => 80.00,
        'estado_acceso' => 'Activo',
    ]);

    Livewire::actingAs($usuarioDocente)
        ->test(Cursos::class)
        ->call('abrirModalEditar', $curso->id_curso)
        ->set('cupos', '1')
        ->call('guardarCurso')
        ->assertHasErrors(['cupos'])
        ->assertSee('Los cupos no pueden ser menores al número de alumnos matriculados');

    // RF023: intentar eliminar un curso con matriculados solo lo deshabilita
    Livewire::actingAs($usuarioDocente)
        ->test(Cursos::class)
        ->call('eliminarCurso', $curso->id_curso);

    expect($curso->fresh())->not->toBeNull()
        ->and($curso->fresh()->estado_habilitado)->toBeFalse();
});

test('RF024 y RF025: reordena sesiones en transaccion sin cambiar id_sesion y valida formatos de recursos', function () {
    Storage::fake('public');
    [$usuarioDocente, , , $curso, $detalleMatricula] = prepararEscenarioDocenteMvp2();

    $s1 = Sesion::query()->create([
        'id_curso' => $curso->id_curso,
        'numero_orden' => 1,
        'titulo_sesion' => 'Sesión Inicial 1',
    ]);
    $s2 = Sesion::query()->create([
        'id_curso' => $curso->id_curso,
        'numero_orden' => 2,
        'titulo_sesion' => 'Sesión Inicial 2',
    ]);

    DetalleSesionCompletada::query()->create([
        'id_detalle_matricula' => $detalleMatricula->id_detalle_matricula,
        'id_sesion' => $s1->id_sesion,
        'fecha_completada' => now(),
    ]);

    // Mover s2 hacia arriba (-1): s2 pasa a orden 1 y s1 a orden 2 conservando su id_sesion
    Livewire::actingAs($usuarioDocente)
        ->test(Sesiones::class, ['idCursoInicial' => $curso->id_curso])
        ->call('moverSesion', $s2->id_sesion, -1);

    expect($s2->fresh()->numero_orden)->toBe(1)
        ->and($s1->fresh()->numero_orden)->toBe(2)
        ->and(DetalleSesionCompletada::query()->where('id_sesion', $s1->id_sesion)->exists())->toBeTrue();

    // RF024: archivo no permitido en lección (ej. ejecutable o PDF > 10 MB)
    $pdfPesado = UploadedFile::fake()->create('partitura_pesada.pdf', 11000, 'application/pdf'); // ~11 MB > 10 MB

    Livewire::actingAs($usuarioDocente)
        ->test(Sesiones::class, ['idCursoInicial' => $curso->id_curso])
        ->call('abrirModalCrearLeccion')
        ->set('titulo_leccion', 'Lección de prueba')
        ->set('descripcion_leccion', 'Descripción de prueba')
        ->set('url_video_leccion', 'https://youtu.be/cbBM9pzVf0M')
        ->set('tipo_recurso_extra', 'Partitura')
        ->set('archivo_pdf', $pdfPesado)
        ->call('guardarLeccion')
        ->assertHasErrors(['archivo_pdf'])
        ->assertSee('Formato o tamaño de archivo no permitido');
});

test('RF026 y RF027: muestra mensaje exacto sin entregas y exige comentario minimo de 5 caracteres al observar', function () {
    [$usuarioDocente, , , $curso, $detalleMatricula] = prepararEscenarioDocenteMvp2();

    $sesion = Sesion::query()->create([
        'id_curso' => $curso->id_curso,
        'numero_orden' => 1,
        'titulo_sesion' => 'Sesión de Práctica',
    ]);

    $tarea = TareaAsignada::query()->create([
        'id_sesion' => $sesion->id_sesion,
        'titulo_tarea' => 'Escala de Do Mayor',
        'descripcion' => 'Subir enlace de video practicando la escala.',
        'fecha_limite' => now()->addDays(5)->toDateString(),
    ]);

    // Sin entregas: mensaje exacto
    Livewire::actingAs($usuarioDocente)
        ->test(Entregas::class, ['idSesion' => $sesion->id_sesion])
        ->assertSee('Aún no hay tareas entregadas para esta sesión');

    // Con entrega: observar sin comentario o con menos de 5 caracteres falla; con >= 5 guarda Observada y fecha_revision
    $entrega = DetalleEntregaTarea::query()->create([
        'id_tarea' => $tarea->id_tarea,
        'id_detalle_matricula' => $detalleMatricula->id_detalle_matricula,
        'video_evidencia' => 'https://youtu.be/demo12345',
        'estado_revision' => 'Pendiente',
        'fecha_entrega' => now(),
    ]);

    $comp = Livewire::actingAs($usuarioDocente)
        ->test(Entregas::class, ['idSesion' => $sesion->id_sesion]);

    $comp->set("comentarios.{$entrega->id_detalle_entrega}", '')
        ->call('calificarEntrega', $entrega->id_detalle_entrega, 'Observada')
        ->assertHasErrors(["comentario_{$entrega->id_detalle_entrega}"]);

    expect($entrega->fresh()->estado_revision)->toBe('Pendiente');

    $comp->set("comentarios.{$entrega->id_detalle_entrega}", 'Corrige la postura de la mano derecha.')
        ->call('calificarEntrega', $entrega->id_detalle_entrega, 'Observada')
        ->assertHasNoErrors()
        ->assertSet('toastMensaje', 'Cambios guardados correctamente');

    expect($entrega->fresh()->estado_revision)->toBe('Observada')
        ->and($entrega->fresh()->comentario_docente)->toBe('Corrige la postura de la mano derecha.')
        ->and($entrega->fresh()->fecha_revision)->not->toBeNull();

    // Aprobar permite comentario opcional
    $comp->set("comentarios.{$entrega->id_detalle_entrega}", '')
        ->call('calificarEntrega', $entrega->id_detalle_entrega, 'Aprobada')
        ->assertHasNoErrors();

    expect($entrega->fresh()->estado_revision)->toBe('Aprobada');
});

test('los cursos se crean sin horario y la edición conserva horarios históricos', function () {
    [$usuario, , , $curso] = prepararEscenarioDocenteMvp2();
    $horario = $curso->detalleHorarios()->create([
        'id_dia' => DiaSemana::query()->firstOrFail()->id_dia,
        'hora_inicio' => '16:00:00',
        'hora_fin' => '17:00:00',
    ]);

    $catalogo = Livewire::actingAs($usuario)->test(Cursos::class)
        ->assertDontSee('Ver cursos en la página pública')
        ->call('abrirModalCrear')
        ->assertDontSee('Hora de inicio')
        ->set('nombre_curso', 'Teclado virtual')
        ->set('costo', '80')
        ->set('cupos', '10')
        ->call('guardarCurso')
        ->assertHasNoErrors()
        ->assertSee('Acceso 24/7 (Virtual)');

    $nuevo = Curso::query()->where('nombre_curso', 'Teclado virtual')->firstOrFail();
    expect($nuevo->modalidad)->toBe('Virtual')
        ->and($nuevo->detalleHorarios()->exists())->toBeFalse();

    $catalogo->call('abrirModalEditar', $curso->id_curso)
        ->assertDontSee('Hora de inicio')
        ->set('nombre_curso', 'Teclado actualizado')
        ->call('guardarCurso')
        ->assertHasNoErrors();

    expect($curso->fresh()->modalidad)->toBe('Virtual')
        ->and($horario->fresh()->hora_inicio)->toBe('16:00:00');
});

test('el docente actualiza su nombre y lo ve en el sidebar al guardar', function () {
    [$usuario] = prepararEscenarioDocenteMvp2();

    Livewire::actingAs($usuario)->test(Panel::class)
        ->call('editarAdministrador')
        ->assertSet('nombres', 'Carlos')
        ->set('nombres', '   ')
        ->call('guardarAdministrador')
        ->assertHasErrors(['nombres'])
        ->set('nombres', ' José Luis ')
        ->set('apellido_paterno', 'Flores')
        ->set('apellido_materno', 'Vargas')
        ->call('guardarAdministrador')
        ->assertHasNoErrors()
        ->assertSet('editandoAdministrador', false)
        ->assertSee('Cambios guardados correctamente')
        ->assertSee('José Luis Flores Vargas');

    expect($usuario->persona()->firstOrFail()->nombre_completo)->toBe('José Luis Flores Vargas');
});

test('el guardado del perfil de administrador exige una cuenta docente', function () {
    [$usuario] = prepararEscenarioDocenteMvp2();
    $panel = Livewire::actingAs($usuario)->test(Panel::class)->call('editarAdministrador');
    $usuario->update(['rol' => 'alumno']);

    $panel->set('nombres', 'Nombre alterado')->call('guardarAdministrador')->assertForbidden();
    expect($usuario->persona()->firstOrFail()->nombres)->toBe('Carlos');
});

test('crea y edita una lección con video y apoyo separados sin duplicar recursos', function () {
    Storage::fake('public');
    [$usuario, , , $curso] = prepararEscenarioDocenteMvp2();
    $sesion = $curso->sesiones()->create(['numero_orden' => 1, 'titulo_sesion' => 'Introducción']);

    $panel = Livewire::actingAs($usuario)->test(Sesiones::class, ['idCursoInicial' => $curso->id_curso])
        ->call('abrirModalCrearLeccion')
        ->set('titulo_leccion', 'Digitación')
        ->set('descripcion_leccion', 'Ejercicio de digitación.')
        ->set('url_video_leccion', 'https://example.org/video')
        ->call('guardarLeccion')
        ->assertHasErrors(['url_video_leccion'])
        ->set('url_video_leccion', 'https://www.youtube.com/shorts/cbBM9pzVf0M')
        ->set('tipo_recurso_extra', 'Ambos')
        ->set('url_apoyo', 'https://youtu.be/cbBM9pzVf0M')
        ->set('archivo_pdf', UploadedFile::fake()->create('digitacion.pdf', 100, 'application/pdf'))
        ->call('guardarLeccion')
        ->assertHasErrors(['url_apoyo'])
        ->set('titulo_pdf', 'PDF de digitación')
        ->set('url_apoyo', 'https://example.org/material')
        ->set('titulo_apoyo', 'Lectura')
        ->call('guardarLeccion')
        ->assertHasNoErrors()
        ->assertSet('toastMensaje', 'Cambios guardados correctamente')
        ->assertSeeHtml('src="https://www.youtube.com/embed/cbBM9pzVf0M"');

    $leccion = $sesion->lecciones()->sole();
    $recursos = $leccion->recursos()->with('tipoRecurso')->get();
    expect($recursos)->toHaveCount(3)
        ->and($recursos->whereNotNull('id_tarea'))->toBeEmpty()
        ->and($recursos->where('tipoRecurso.nombre_tipo', 'URL'))->toHaveCount(2);
    $pdf = $recursos->firstWhere('titulo_recurso', 'PDF de digitación');
    Storage::disk('public')->assertExists(str_replace('storage/', '', $pdf->url_recurso));

    $panel->call('abrirModalEditarLeccion', $leccion->id_leccion)
        ->assertSet('url_video_leccion', 'https://www.youtube.com/shorts/cbBM9pzVf0M')
        ->assertSet('tipo_recurso_extra', 'Ambos')
        ->assertSet('url_apoyo', 'https://example.org/material')
        ->set('url_video_leccion', 'https://vimeo.com/12345678')
        ->set('tipo_recurso_extra', 'Partitura')
        ->assertSee('Arrastra y suelta tu archivo PDF aquí')
        ->assertSee('PDF de digitación')
        ->set('archivo_pdf', UploadedFile::fake()->create('reemplazo.pdf', 1024, 'application/pdf'))
        ->assertSee('reemplazo.pdf')
        ->assertSee('1.00 MB')
        ->call('quitarPdfTemporal')
        ->assertSet('archivo_pdf', null)
        ->assertDontSee('reemplazo.pdf')
        ->call('guardarLeccion')
        ->assertHasNoErrors();

    expect($leccion->recursos()->count())->toBe(2)
        ->and($pdf->fresh()->url_recurso)->toBe($pdf->url_recurso)
        ->and($recursos->firstWhere('titulo_recurso', 'Video principal')->fresh()->url_recurso)->toBe('https://vimeo.com/12345678');
});

test('asigna y edita una tarea con video opcional y ambos adjuntos', function () {
    Storage::fake('public');
    [$usuario, , , $curso] = prepararEscenarioDocenteMvp2();
    $sesion = $curso->sesiones()->create(['numero_orden' => 1, 'titulo_sesion' => 'Introducción']);

    $panel = Livewire::actingAs($usuario)->test(Sesiones::class, ['idCursoInicial' => $curso->id_curso])
        ->call('cambiarSubPestana', 'tareas')
        ->call('abrirModalTarea')
        ->assertSet('fecha_limite_tarea', '')
        ->set('titulo_tarea', 'Práctica')
        ->set('descripcion_tarea', 'Practica la escala.')
        ->set('url_video_tarea', 'https://player.vimeo.com/video/12345678')
        ->set('tipo_recurso_extra', 'Ambos')
        ->set('url_apoyo', 'https://example.org/guia')
        ->set('archivo_pdf', UploadedFile::fake()->create('guia.pdf', 100, 'application/pdf'))
        ->assertSee('guia.pdf')
        ->assertSee('Arrastra y suelta tu archivo PDF aquí')
        ->call('guardarTarea')
        ->assertHasNoErrors()
        ->assertSet('toastMensaje', 'Cambios guardados correctamente')
        ->assertSeeHtml('src="https://player.vimeo.com/video/12345678"')
        ->assertSeeHtml('href="https://example.org/guia"');

    $tarea = $sesion->tareaAsignada()->sole();
    expect($tarea->fecha_limite)->toBeNull()
        ->and($tarea->recursos()->count())->toBe(3)
        ->and($tarea->recursos()->whereNotNull('id_leccion')->exists())->toBeFalse();

    $panel->call('abrirModalTarea')
        ->assertSet('url_video_tarea', 'https://player.vimeo.com/video/12345678')
        ->assertSet('tipo_recurso_extra', 'Ambos')
        ->assertSet('url_apoyo', 'https://example.org/guia')
        ->set('url_video_tarea', '')
        ->set('tipo_recurso_extra', 'Enlace de referencia')
        ->set('url_apoyo', 'https://example.org/nueva-guia')
        ->call('guardarTarea')
        ->assertHasNoErrors();

    expect($tarea->recursos()->sole()->url_recurso)->toBe('https://example.org/nueva-guia');
});

test('permite editar una lección antigua conservando su MP4', function () {
    [$usuario, , , $curso] = prepararEscenarioDocenteMvp2();
    $sesion = $curso->sesiones()->create(['numero_orden' => 1, 'titulo_sesion' => 'Introducción']);
    $leccion = $sesion->lecciones()->create([
        'codigo_leccion' => 'LEC-01',
        'numero_orden' => 1,
        'titulo_leccion' => 'Lección anterior',
        'descripcion' => 'Demostración previa.',
    ]);
    $tipoMp4 = TipoRecurso::firstOrCreate(['nombre_tipo' => 'MP4']);
    $video = $leccion->recursos()->create([
        'id_tipo_recurso' => $tipoMp4->id_tipo_recurso,
        'titulo_recurso' => 'Video anterior',
        'url_recurso' => 'storage/recursos/videos/anterior.mp4',
    ]);

    Livewire::actingAs($usuario)->test(Sesiones::class, ['idCursoInicial' => $curso->id_curso])
        ->call('abrirModalEditarLeccion', $leccion->id_leccion)
        ->assertSet('url_video_leccion', '')
        ->assertSet('tieneMp4Leccion', true)
        ->set('titulo_leccion', 'Lección actualizada')
        ->call('guardarLeccion')
        ->assertHasNoErrors()
        ->assertSeeHtml('<video controls');

    expect($leccion->recursos()->sole()->id_recurso)->toBe($video->id_recurso);
});
