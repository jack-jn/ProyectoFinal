<?php

use App\Livewire\Auth\CambiarClave;
use App\Livewire\Auth\Login;
use App\Livewire\Docente\AlumnosMatriculados as DocenteAlumnosMatriculados;
use App\Livewire\Docente\UsuariosPermisos;
use App\Livewire\Publico\Inicio;
use App\Livewire\Publico\Matricula as PublicoMatricula;
use App\Livewire\Secretaria\Pagos;
use App\Livewire\Secretaria\Pagos as SecretariaPagos;
use App\Models\Alumno;
use App\Models\Apoderado;
use App\Models\AuditoriaCredencial;
use App\Models\Curso;
use App\Models\DetalleMatricula;
use App\Models\Docente;
use App\Models\Instrumento;
use App\Models\Matricula;
use App\Models\Pago;
use App\Models\Persona;
use App\Models\Secretaria;
use App\Models\UsuarioSistema;
use Database\Seeders\RolesPermisosSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Mail\CredencialesAlumnoMail;
use Livewire\Livewire;

function prepararEscenarioMatriculaSecretaria(): array
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
    ]);

    $docente = Docente::query()->create([
        'id_persona' => $pDocente->id_persona,
        'especialidad' => 'Piano',
    ]);

    $pSec = Persona::query()->create([
        'nombres' => 'María',
        'apellido_paterno' => 'Condori',
        'apellido_materno' => 'Flores',
        'dni' => '70112233',
        'correo' => 'secretaria@dunamys.edu.pe',
        'celular' => '999739090',
    ]);

    $usuarioSecretaria = UsuarioSistema::query()->create([
        'id_persona' => $pSec->id_persona,
        'correo_usuario' => 'secretaria@dunamys.edu.pe',
        'contrasena' => Hash::make('12345678'),
        'rol' => 'secretaria',
        'estado_cuenta' => 'Activo',
    ]);

    test()->seed(RolesPermisosSeeder::class);

    $secretaria = Secretaria::query()->create([
        'id_persona' => $pSec->id_persona,
        'codigo_secretaria' => 'SEC-001',
        'turno_atencion' => 'Regular',
    ]);

    $inst = Instrumento::query()->create([
        'nombre_instrumento' => 'Piano',
        'icono' => 'i-inst-piano',
    ]);

    $cursoConCupo = Curso::query()->create([
        'id_docente' => $docente->id_docente,
        'id_instrumento' => $inst->id_instrumento,
        'codigo_curso' => 'C-001',
        'nombre_curso' => 'Piano Básico',
        'nivel' => 'Básico',
        'modalidad' => 'Virtual',
        'costo' => 80.00,
        'cupos' => 5,
        'estado_habilitado' => true,
    ]);

    $cursoSinCupo = Curso::query()->create([
        'id_docente' => $docente->id_docente,
        'id_instrumento' => $inst->id_instrumento,
        'codigo_curso' => 'C-002',
        'nombre_curso' => 'Piano Avanzado Lleno',
        'nivel' => 'Avanzado',
        'modalidad' => 'Virtual',
        'costo' => 100.00,
        'cupos' => 1,
        'estado_habilitado' => true,
    ]);

    // Llenar el único cupo de $cursoSinCupo con un matriculado Activo
    $pOcupado = Persona::query()->create([
        'nombres' => 'Pedro',
        'apellido_paterno' => 'Soto',
        'apellido_materno' => 'Paz',
        'dni' => '79998888',
        'correo' => 'pedro@dunamys.edu.pe',
        'celular' => '951112233',
    ]);
    $alOcupado = Alumno::query()->create(['id_persona' => $pOcupado->id_persona, 'codigo_alumno' => 'ALU-2026-099']);
    $matOcupada = Matricula::query()->create([
        'id_alumno' => $alOcupado->id_alumno,
        'codigo_matricula' => 'MAT-2026-099',
        'fecha_matricula' => now(),
        'monto_total' => 100.00,
        'estado_matricula' => 'Confirmada',
    ]);
    DetalleMatricula::query()->create([
        'id_matricula' => $matOcupada->id_matricula,
        'id_curso' => $cursoSinCupo->id_curso,
        'fecha_inscripcion' => now(),
        'precio_cobrado' => 100.00,
        'estado_acceso' => 'Activo',
    ]);

    return [$usuarioDocente, $usuarioSecretaria, $secretaria, $cursoConCupo, $cursoSinCupo];
}

test('Flujo 2: impide elegir curso sin cupos, exige apoderado si es menor de 18 y guarda voucher en disco privado', function () {
    Storage::fake('local');
    [, , , $cursoConCupo, $cursoSinCupo] = prepararEscenarioMatriculaSecretaria();

    $comp = Livewire::test(PublicoMatricula::class);

    // No permite elegir un curso sin cupos disponibles
    $comp->call('alternarCurso', $cursoSinCupo->id_curso)
        ->assertHasErrors(['cursosSeleccionados']);

    // Elegir curso con cupo
    $comp->call('alternarCurso', $cursoConCupo->id_curso)
        ->assertSet('cursosSeleccionados', [$cursoConCupo->id_curso]);

    // Estudiante menor de 18 años (nacido hace 14 años) sin apoderado falla
    $fechaMenor = now()->subYears(14)->toDateString();
    $voucher = UploadedFile::fake()->image('voucher_yape.jpg', 400, 600);

    $comp->set('nombres', 'Diego')
        ->set('apellido_paterno', 'Quispe')
        ->set('apellido_materno', 'Ramos')
        ->set('dni', '76543210')
        ->set('fecha_nacimiento', $fechaMenor)
        ->set('correo', 'diego.menor@dunamys.edu.pe')
        ->set('celular', '987654321')
        ->call('continuarDesdeDatosPersonales')
        ->assertSet('esMenorDeEdad', true)
        ->assertSet('pasoActual', 4);

    // Intentar registrar sin apoderado produce error
    $comp->set('comprobante', $voucher)
        ->call('registrarMatricula')
        ->assertHasErrors(['apoderado_general']);

    // Completar datos del apoderado y registrar matrícula
    $comp->set('apoderado_nombres', 'Roberto')
        ->set('apoderado_apellido_paterno', 'Quispe')
        ->set('apoderado_apellido_materno', 'Lipa')
        ->set('apoderado_dni', '40112233')
        ->set('apoderado_celular', '952112233')
        ->set('apoderado_parentesco', 'Padre')
        ->call('continuarDesdeApoderado')
        ->assertSet('pasoActual', 5)
        ->set('numero_operacion', '99887766')
        ->set('comprobante', $voucher)
        ->call('registrarMatricula')
        ->assertHasNoErrors()
        ->assertSet('matriculaRegistrada', true);

    expect(Apoderado::query()->count())->toBe(1);

    $matricula = Matricula::query()->where('codigo_matricula', '!=', 'MAT-2026-099')->first();
    expect($matricula)->not->toBeNull()
        ->and($matricula->estado_matricula)->toBe('Pendiente de pago');

    $detalle = DetalleMatricula::query()->where('id_matricula', $matricula->id_matricula)->first();
    expect($detalle->estado_acceso)->toBe('Bloqueado');

    $pago = Pago::query()->where('id_matricula', $matricula->id_matricula)->first();
    expect($pago->estado_validacion)->toBe('Pendiente');
    Storage::disk('local')->assertExists($pago->comprobante_url);
});

test('Flujo 3: secretaria valida el pago en transaccion activando matricula, curso y cuenta de usuario con clave aleatoria cifrada sin alterar cursos.cupos', function () {
    Storage::fake('local');
    [, $usuarioSecretaria, $secretaria, $cursoConCupo] = prepararEscenarioMatriculaSecretaria();

    $pAlumno = Persona::query()->create([
        'nombres' => 'Valeria',
        'apellido_paterno' => 'López',
        'apellido_materno' => 'Rojas',
        'dni' => '74125896',
        'fecha_nacimiento' => now()->subYears(15)->toDateString(),
        'correo' => 'valeria@dunamys.edu.pe',
        'celular' => '987112233',
    ]);
    $personaApoderada = Persona::query()->create([
        'nombres' => 'Elena',
        'apellido_paterno' => 'López',
        'apellido_materno' => 'Rojas',
        'dni' => '74998564',
        'celular' => '987554433',
    ]);
    $apoderada = Apoderado::query()->create(['id_persona' => $personaApoderada->id_persona, 'parentesco' => 'Madre']);
    $alumno = Alumno::query()->create([
        'id_persona' => $pAlumno->id_persona,
        'id_apoderado' => $apoderada->id_apoderado,
        'codigo_alumno' => 'ALU-2026-010',
    ]);
    $matricula = Matricula::query()->create([
        'id_alumno' => $alumno->id_alumno,
        'codigo_matricula' => 'MAT-2026-010',
        'fecha_matricula' => now(),
        'monto_total' => 80.00,
        'estado_matricula' => 'Pendiente de pago',
    ]);
    $detalle = DetalleMatricula::query()->create([
        'id_matricula' => $matricula->id_matricula,
        'id_curso' => $cursoConCupo->id_curso,
        'fecha_inscripcion' => now(),
        'precio_cobrado' => 80.00,
        'estado_acceso' => 'Bloqueado',
    ]);

    Storage::disk('local')->put('comprobantes/valeria.jpg', 'imagen-voucher');
    $pago = Pago::query()->create([
        'id_matricula' => $matricula->id_matricula,
        'metodo_pago' => 'Yape',
        'numero_operacion' => '11223344',
        'comprobante_url' => 'comprobantes/valeria.jpg',
        'monto_pagado' => 80.00,
        'fecha_pago' => now(),
        'estado_validacion' => 'Pendiente',
    ]);

    $cuposTotalesAntes = $cursoConCupo->cupos;

    $componenteSec = Livewire::actingAs($usuarioSecretaria)
        ->test(SecretariaPagos::class)
        ->call('cambiarPantalla', 'pendientes')
        ->assertSeeHtml('wire:click="verDetallePago('.$pago->id_pago.')"')
        ->call('verDetallePago', $pago->id_pago)
        ->assertSeeHtml('class="overlay is-open" id="detailOverlay"')
        ->assertSee('Elena López Rojas')
        ->assertSee('11223344')
        ->assertSee('MONTO TOTAL DE MATRÍCULA')
        ->assertSeeHtml('src="'.route('comprobantes.ver', $pago->id_pago).'"')
        ->assertSee('Validar pago')
        ->call('validarPago', $pago->id_pago)
        ->assertSet('toastMensaje', 'Cambios guardados correctamente')
        ->assertSet('pantallaActiva', 'acceso');

    expect($pago->fresh()->estado_validacion)->toBe('Validado')
        ->and($pago->fresh()->id_secretaria)->toBe($secretaria->id_secretaria)
        ->and($pago->fresh()->fecha_validacion)->not->toBeNull()
        ->and($matricula->fresh()->estado_matricula)->toBe('Confirmada')
        ->and($detalle->fresh()->estado_acceso)->toBe('Activo')
        ->and($cursoConCupo->fresh()->cupos)->toBe($cuposTotalesAntes)
        ->and($cursoConCupo->fresh()->cupos_disponibles)->toBe($cuposTotalesAntes - 1);

    $usuarioAlumno = UsuarioSistema::query()->where('correo_usuario', 'valeria@dunamys.edu.pe')->first();
    expect($usuarioAlumno)->not->toBeNull()
        ->and($usuarioAlumno->rol)->toBe('alumno')
        ->and($usuarioAlumno->estado_cuenta)->toBe('Activo')
        ->and($usuarioAlumno->debe_cambiar_contrasena)->toBeTrue()
        ->and($usuarioAlumno->password_temporal)->not->toBeNull()
        ->and(array_key_exists('password_temporal', $usuarioAlumno->toArray()))->toBeFalse();

    expect($usuarioAlumno->hasRole('alumno'))->toBeTrue()
        ->and($usuarioAlumno->can('aula_virtual'))->toBeTrue();

    $claveGenerada = Crypt::decryptString($usuarioAlumno->password_temporal);
    expect(strlen($claveGenerada))->toBe(10)
        ->and(Hash::check($claveGenerada, $usuarioAlumno->contrasena))->toBeTrue();

    $componenteSec->assertSeeHtml('wire:click="verDetallePago('.$pago->id_pago.')"')
        ->call('verDetallePago', $pago->id_pago)
        ->assertSeeHtml('class="overlay is-open" id="detailOverlay"')
        ->assertSee('Validado')
        ->assertDontSeeHtml('wire:click="validarPago('.$pago->id_pago.')"')
        ->call('cerrarModalDetalle')
        ->assertDontSeeHtml('id="detailOverlay"');

    // En secretaria NO aparece la columna CONTRASEÑA ni los botones Mostrar / Restablecer
    $componenteSec->assertDontSee($claveGenerada)
        ->assertDontSee('••••••••••')
        ->assertDontSee('Restablecer');

    $componenteSec->call('cambiarPantalla', 'historial')
        ->set('busquedaHistorial', $matricula->codigo_matricula)
        ->set('filtroEstadoHistorial', 'Validado')
        ->set('filtroCursoHistorial', (string) $cursoConCupo->id_curso)
        ->set('filtroEstadoCuentaHistorial', 'Inactivo')
        ->assertSee('No se encontraron resultados.')
        ->set('filtroEstadoCuentaHistorial', 'Activo')
        ->assertSee($matricula->codigo_matricula)
        ->call('limpiarFiltrosHistorial')
        ->assertSet('filtroEstadoCuentaHistorial', 'todos');

    // En AlumnosMatriculados se muestran datos académicos del alumno (sin contraseñas)
    [$usuarioDocente] = [UsuarioSistema::query()->where('rol', 'docente')->first()];
    Livewire::actingAs($usuarioDocente)
        ->test(DocenteAlumnosMatriculados::class)
        ->assertSee($matricula->codigo_matricula)
        ->assertDontSee($claveGenerada);

    // En Usuarios y permisos solo el docente puede Mostrar y Restablecer credenciales
    Livewire::actingAs($usuarioDocente)
        ->test(UsuariosPermisos::class)
        ->call('cambiarPestana', 'alumnos')
        ->assertDontSee($claveGenerada)
        ->assertSee('••••••••••')
        ->call('alternarPassword', $usuarioAlumno->id_usuario)
        ->assertSee($claveGenerada);

    expect(AuditoriaCredencial::query()->where('id_usuario_alumno', $usuarioAlumno->id_usuario)->where('accion', 'ver')->count())->toBe(1);

    // La secretaria recibe 403 si intenta invocar el componente de alumnos o credenciales del docente
    Livewire::actingAs($usuarioSecretaria)
        ->test(DocenteAlumnosMatriculados::class)
        ->assertForbidden();

    // El alumno recién validado puede iniciar sesión con el login existente y es redirigido a cambiar-clave
    Livewire::test(Login::class)
        ->set('correo_usuario', 'valeria@dunamys.edu.pe')
        ->set('contrasena', $claveGenerada)
        ->call('ingresar')
        ->assertRedirect(route('cambiar-clave'));

    // Cuando el alumno cambia su contraseña, también se actualiza password_temporal cifrada para la secretaria
    Livewire::actingAs($usuarioAlumno)
        ->test(CambiarClave::class)
        ->set('nueva_contrasena', 'Valeria2026')
        ->set('repetir_contrasena', 'Valeria2026')
        ->call('guardarContrasena')
        ->assertRedirect(route('estudiante.aula'));

    $usuarioAlumno->refresh();
    expect($usuarioAlumno->debe_cambiar_contrasena)->toBeFalse()
        ->and(Hash::check('Valeria2026', $usuarioAlumno->contrasena))->toBeTrue()
        ->and(Crypt::decryptString($usuarioAlumno->password_temporal))->toBe('Valeria2026');

    // Ruta autenticada de comprobantes privados: secretaria tiene acceso, alumno recibe 403
    $this->actingAs($usuarioSecretaria)
        ->get(route('comprobantes.ver', $pago->id_pago))
        ->assertOk();

    $this->actingAs($usuarioAlumno)
        ->get(route('comprobantes.ver', $pago->id_pago))
        ->assertForbidden();
});

test('docente visualiza Sin contrasena visible para alumnos validados previamente sin password_temporal y permite restablecer contrasena con auditoria', function () {
    [$usuarioDocente, , $secretaria, $cursoConCupo] = prepararEscenarioMatriculaSecretaria();

    $pAntiguo = Persona::query()->create([
        'nombres' => 'Marco',
        'apellido_paterno' => 'Flores',
        'apellido_materno' => 'Díaz',
        'dni' => '73334444',
        'correo' => 'marco.antiguo@dunamys.edu.pe',
        'celular' => '951223344',
    ]);
    $uAntiguo = UsuarioSistema::query()->create([
        'id_persona' => $pAntiguo->id_persona,
        'correo_usuario' => 'marco.antiguo@dunamys.edu.pe',
        'contrasena' => Hash::make('12345678'),
        'password_temporal' => null,
        'rol' => 'alumno',
        'estado_cuenta' => 'Activo',
        'debe_cambiar_contrasena' => false,
    ]);
    $alAntiguo = Alumno::query()->create([
        'id_persona' => $pAntiguo->id_persona,
        'codigo_alumno' => 'ALU-2026-050',
    ]);
    $matAntigua = Matricula::query()->create([
        'id_alumno' => $alAntiguo->id_alumno,
        'codigo_matricula' => 'MAT-2026-050',
        'fecha_matricula' => now()->subDays(3),
        'monto_total' => 80.00,
        'estado_matricula' => 'Confirmada',
    ]);
    DetalleMatricula::query()->create([
        'id_matricula' => $matAntigua->id_matricula,
        'id_curso' => $cursoConCupo->id_curso,
        'fecha_inscripcion' => now()->subDays(3),
        'precio_cobrado' => 80.00,
        'estado_acceso' => 'Activo',
    ]);
    Pago::query()->create([
        'id_matricula' => $matAntigua->id_matricula,
        'id_secretaria' => $secretaria->id_secretaria,
        'metodo_pago' => 'Yape',
        'numero_operacion' => '77441122',
        'comprobante_url' => 'comprobantes/marco.jpg',
        'monto_pagado' => 80.00,
        'fecha_pago' => now()->subDays(3),
        'estado_validacion' => 'Validado',
        'fecha_validacion' => now()->subDays(2),
    ]);

    Livewire::actingAs($usuarioDocente)
        ->test(DocenteAlumnosMatriculados::class)
        ->assertSee('Marco Flores Díaz');

    $comp = Livewire::actingAs($usuarioDocente)
        ->test(UsuariosPermisos::class)
        ->call('cambiarPestana', 'alumnos')
        ->assertSee('Marco Flores Díaz')
        ->assertSee('Sin clave visible')
        ->assertSee('Restablecer clave');

    // Al restablecer contraseña se genera nueva clave temporal, debe_cambiar_contrasena = true y se audita
    $comp->call('restablecerClave', $uAntiguo->id_usuario)
        ->assertSet('mensaje', 'Contraseña restablecida correctamente')
        ->assertSet('modalCredencialesAbierto', true);

    $uAntiguo->refresh();
    expect($uAntiguo->debe_cambiar_contrasena)->toBeTrue()
        ->and($uAntiguo->password_temporal)->not->toBeNull();

    $nuevaClave = Crypt::decryptString($uAntiguo->password_temporal);
    expect(strlen($nuevaClave))->toBe(10)
        ->and(Hash::check($nuevaClave, $uAntiguo->contrasena))->toBeTrue();

    $comp->assertSee($nuevaClave);

    expect(AuditoriaCredencial::query()
        ->where('id_usuario_secretaria', $usuarioDocente->id_usuario)
        ->where('id_usuario_alumno', $uAntiguo->id_usuario)
        ->where('accion', 'restablecer')
        ->count())->toBe(1);
});

test('rechaza DNI 00000000 tanto en matricula publica como al validar pago', function () {
    [, $usuarioSecretaria, , $cursoConCupo] = prepararEscenarioMatriculaSecretaria();

    Livewire::test(PublicoMatricula::class)
        ->call('alternarCurso', $cursoConCupo->id_curso)
        ->set('nombres', 'Juan')
        ->set('apellido_paterno', 'Pérez')
        ->set('apellido_materno', 'Gómez')
        ->set('dni', '00000000')
        ->set('fecha_nacimiento', '2000-01-01')
        ->set('correo', 'juan@dunamys.edu.pe')
        ->set('celular', '987654321')
        ->call('continuarDesdeDatosPersonales')
        ->assertHasErrors(['dni']);

    $pInvalido = Persona::query()->create([
        'nombres' => 'Invalido',
        'apellido_paterno' => 'Cero',
        'apellido_materno' => 'Test',
        'dni' => '00000000',
        'correo' => 'cero@dunamys.edu.pe',
        'celular' => '987654321',
    ]);
    $alInvalido = Alumno::query()->create(['id_persona' => $pInvalido->id_persona, 'codigo_alumno' => 'ALU-2026-088']);
    $matInvalida = Matricula::query()->create([
        'id_alumno' => $alInvalido->id_alumno,
        'codigo_matricula' => 'MAT-2026-088',
        'fecha_matricula' => now(),
        'monto_total' => 80.00,
        'estado_matricula' => 'Pendiente de pago',
    ]);
    $pagoInvalido = Pago::query()->create([
        'id_matricula' => $matInvalida->id_matricula,
        'metodo_pago' => 'Yape',
        'numero_operacion' => '88889999',
        'comprobante_url' => 'comprobantes/cero.jpg',
        'monto_pagado' => 80.00,
        'fecha_pago' => now(),
        'estado_validacion' => 'Pendiente',
    ]);

    Livewire::actingAs($usuarioSecretaria)
        ->test(SecretariaPagos::class)
        ->call('validarPago', $pagoInvalido->id_pago)
        ->assertSet('toastTipo', 'error');

    expect($pagoInvalido->fresh()->estado_validacion)->toBe('Pendiente');
});

test('secretaria valida desde el detalle aunque el correo del alumno pertenezca a otra cuenta', function () {
    [$usuarioDocente, $usuarioSecretaria] = prepararEscenarioMatriculaSecretaria();
    $cuentaOriginal = $usuarioDocente->fresh()->getAttributes();
    $persona = Persona::query()->create([
        'nombres' => 'Valeria',
        'apellido_paterno' => 'Lopez',
        'apellido_materno' => 'Rojas',
        'dni' => '74125896',
        'correo' => $usuarioDocente->correo_usuario,
    ]);
    $alumno = Alumno::query()->create(['id_persona' => $persona->id_persona, 'codigo_alumno' => 'ALU-2026-011']);
    $matricula = Matricula::query()->create([
        'id_alumno' => $alumno->id_alumno,
        'codigo_matricula' => 'MAT-2026-011',
        'fecha_matricula' => now(),
        'monto_total' => 80.00,
        'estado_matricula' => 'Pendiente de pago',
    ]);
    $pago = Pago::query()->create([
        'id_matricula' => $matricula->id_matricula,
        'metodo_pago' => 'Yape',
        'numero_operacion' => '11223345',
        'comprobante_url' => 'comprobantes/valeria.jpg',
        'monto_pagado' => 80.00,
        'fecha_pago' => now(),
        'estado_validacion' => 'Pendiente',
    ]);

    Livewire::actingAs($usuarioSecretaria)
        ->test(SecretariaPagos::class)
        ->call('cambiarPantalla', 'pendientes')
        ->call('verDetallePago', $pago->id_pago)
        ->call('validarPago', $pago->id_pago)
        ->assertSet('modalDetalleAbierto', false)
        ->assertSee('El correo del alumno ya pertenece a otra cuenta.');

    expect($pago->fresh()->estado_validacion)->toBe('Validado')
        ->and($matricula->fresh()->estado_matricula)->toBe('Confirmada')
        ->and($persona->usuarioSistema()->exists())->toBeFalse()
        ->and($usuarioDocente->fresh()->getAttributes())->toEqual($cuentaOriginal);
});

test('secretaria observa un comprobante con motivo obligatorio y el alumno puede volver a subirlo', function () {
    Storage::fake('local');
    [, $usuarioSecretaria, , $cursoConCupo] = prepararEscenarioMatriculaSecretaria();

    $pAlumno = Persona::query()->create([
        'nombres' => 'Luis',
        'apellido_paterno' => 'Ramírez',
        'apellido_materno' => 'Paredes',
        'dni' => '71234567',
        'correo' => 'luis@dunamys.edu.pe',
        'celular' => '987000111',
    ]);
    $alumno = Alumno::query()->create(['id_persona' => $pAlumno->id_persona, 'codigo_alumno' => 'ALU-2026-011']);
    $matricula = Matricula::query()->create([
        'id_alumno' => $alumno->id_alumno,
        'codigo_matricula' => 'MAT-2026-011',
        'fecha_matricula' => now(),
        'monto_total' => 80.00,
        'estado_matricula' => 'Pendiente de pago',
    ]);
    DetalleMatricula::query()->create([
        'id_matricula' => $matricula->id_matricula,
        'id_curso' => $cursoConCupo->id_curso,
        'fecha_inscripcion' => now(),
        'precio_cobrado' => 80.00,
        'estado_acceso' => 'Bloqueado',
    ]);
    $pago = Pago::query()->create([
        'id_matricula' => $matricula->id_matricula,
        'metodo_pago' => 'Yape',
        'comprobante_url' => 'comprobantes/luis_borroso.jpg',
        'monto_pagado' => 80.00,
        'fecha_pago' => now(),
        'estado_validacion' => 'Pendiente',
    ]);

    Livewire::actingAs($usuarioSecretaria)
        ->test(SecretariaPagos::class)
        ->call('abrirModalObservar', $pago->id_pago)
        ->set('motivo_observacion', '')
        ->call('observarPago')
        ->assertHasErrors(['motivo_observacion'])
        ->set('motivo_observacion', 'La captura del comprobante está ilegible.')
        ->call('observarPago')
        ->assertHasNoErrors();

    expect($pago->fresh()->estado_validacion)->toBe('Observado')
        ->and($pago->fresh()->observacion)->toBe('La captura del comprobante está ilegible.');

    // El alumno busca su matrícula observada y vuelve a subir el comprobante
    $nuevoVoucher = UploadedFile::fake()->image('voucher_claro.png', 400, 600);

    Livewire::test(PublicoMatricula::class)
        ->set('codigoBusquedaReenvio', 'MAT-2026-011')
        ->call('buscarMatriculaObservada')
        ->assertSet('pagoObservadoId', $pago->id_pago)
        ->set('nuevo_numero_operacion', '55667788')
        ->set('nuevo_comprobante', $nuevoVoucher)
        ->call('reenviarComprobanteObservado')
        ->assertSet('mensajeExitoReenvio', 'Cambios guardados correctamente');

    expect($pago->fresh()->estado_validacion)->toBe('Pendiente')
        ->and($pago->fresh()->observacion)->toBeNull()
        ->and($matricula->fresh()->estado_matricula)->toBe('Pendiente de pago');
});

test('docente crea una cuenta y revoca permisos de Secretaría de forma persistente', function () {
    [$docente] = prepararEscenarioMatriculaSecretaria();

    $gestor = Livewire::actingAs($docente)
        ->test(UsuariosPermisos::class)
        ->set('nombres', 'Ana')
        ->set('apellido_paterno', 'Flores')
        ->set('apellido_materno', 'López')
        ->set('dni', '57991342')
        ->set('correo', 'ana@dunamys.edu.pe')
        ->set('clave', 'Clave2026')
        ->call('crearUsuario')
        ->assertHasNoErrors()
        ->assertSee('ana@dunamys.edu.pe');

    $cuenta = UsuarioSistema::where('correo_usuario', 'ana@dunamys.edu.pe')->firstOrFail();
    expect($cuenta->hasRole('secretaria'))->toBeTrue()
        ->and($cuenta->can('validar_observar'))->toBeTrue();

    $gestor->set("permisosSeleccionados.{$cuenta->id_usuario}", ['resumen'])
        ->call('guardarPermisos', $cuenta->id_usuario)
        ->assertHasNoErrors();

    test()->seed(RolesPermisosSeeder::class);
    $cuenta->refresh();
    expect($cuenta->can('resumen'))->toBeTrue()
        ->and($cuenta->can('validar_observar'))->toBeFalse();

    Livewire::actingAs($cuenta)
        ->test(Pagos::class)
        ->call('validarPago', 1)
        ->assertForbidden();

    Livewire::actingAs($docente)->test(UsuariosPermisos::class)
        ->call('cambiarEstado', $cuenta->id_usuario)->assertHasNoErrors();
    expect($cuenta->fresh()->estado_cuenta)->toBe('Inactivo');
});

test('el detalle público usa los datos del curso virtual habilitado', function () {
    [, , , $curso] = prepararEscenarioMatriculaSecretaria();

    $curso->sesiones()->create(['numero_orden' => 2, 'titulo_sesion' => 'Práctica guiada']);
    $curso->sesiones()->create(['numero_orden' => 1, 'titulo_sesion' => 'Postura inicial']);
    $imagenCatalogo = asset($curso->imagen_url ?: 'assets/img/cursos/guitarra.webp');

    Livewire::test(Inicio::class)
        ->assertSee('Iniciar sesión')
        ->assertSeeHtml('href="https://www.facebook.com/share/1BhdSWgurk/"')
        ->assertSeeHtml('href="https://www.instagram.com/academiamusicaldunamys?jplx=MXUyZHV4ZHM0aDU5Mw=="')
        ->assertSeeHtml('src="'.$imagenCatalogo.'"')
        ->set('idCursoDetalle', $curso->id_curso)
        ->assertSeeHtml('class="curso-detalle"')
        ->assertSeeHtmlInOrder(['Postura inicial', 'Práctica guiada'])
        ->assertSee('100% Virtual')
        ->assertSee($curso->codigo_curso)
        ->assertSee(route('publico.matricula', ['curso' => $curso->id_curso]))
        ->set('idCursoDetalle', null)
        ->assertDontSeeHtml('class="curso-detalle"')
        ->assertSeeHtml('src="'.$imagenCatalogo.'"');

    $curso->update(['estado_habilitado' => false]);
    Livewire::test(Inicio::class)
        ->set('idCursoDetalle', $curso->id_curso)
        ->assertDontSee($curso->codigo_curso);
});

test('al validar pago secretaria despacha CredencialesAlumnoMail y muestra modal de confirmacion sin exponer la clave', function () {
    Mail::fake();
    Storage::fake('local');
    [, $usuarioSecretaria, , $cursoConCupo] = prepararEscenarioMatriculaSecretaria();

    $persona = Persona::query()->create([
        'nombres' => 'Camila',
        'apellido_paterno' => 'Vega',
        'apellido_materno' => 'Torres',
        'dni' => '75554433',
        'correo' => 'camila.vega@dunamys.edu.pe',
        'celular' => '987111222',
    ]);
    $alumno = Alumno::query()->create(['id_persona' => $persona->id_persona, 'codigo_alumno' => 'ALU-2026-301']);
    $matricula = Matricula::query()->create([
        'id_alumno' => $alumno->id_alumno,
        'codigo_matricula' => 'MAT-2026-301',
        'fecha_matricula' => now(),
        'monto_total' => 80.00,
        'estado_matricula' => 'Pendiente de pago',
    ]);
    DetalleMatricula::query()->create([
        'id_matricula' => $matricula->id_matricula,
        'id_curso' => $cursoConCupo->id_curso,
        'fecha_inscripcion' => now(),
        'precio_cobrado' => 80.00,
        'estado_acceso' => 'Bloqueado',
    ]);
    $pago = Pago::query()->create([
        'id_matricula' => $matricula->id_matricula,
        'metodo_pago' => 'Yape',
        'numero_operacion' => '44556677',
        'comprobante_url' => 'comprobantes/camila.jpg',
        'monto_pagado' => 80.00,
        'fecha_pago' => now(),
        'estado_validacion' => 'Pendiente',
    ]);

    $comp = Livewire::actingAs($usuarioSecretaria)
        ->test(SecretariaPagos::class)
        ->call('validarPago', $pago->id_pago)
        ->assertSet('modalConfirmacionCorreoAbierto', true)
        ->assertSet('correoConfirmacionEnviado', true)
        ->assertSee('¡Alumno validado y correo enviado!')
        ->assertSee('camila.vega@dunamys.edu.pe');

    $usuarioAlumno = UsuarioSistema::query()->where('correo_usuario', 'camila.vega@dunamys.edu.pe')->firstOrFail();
    $claveGenerada = Crypt::decryptString($usuarioAlumno->password_temporal);

    $comp->assertDontSee($claveGenerada);

    Mail::assertSent(CredencialesAlumnoMail::class, function (CredencialesAlumnoMail $mail) use ($claveGenerada): bool {
        $mail->assertTo('camila.vega@dunamys.edu.pe')
            ->assertSeeInHtml('Camila Vega Torres')
            ->assertSeeInHtml('MAT-2026-301')
            ->assertSeeInHtml($claveGenerada)
            ->assertSeeInHtml('Recuerda que esta contraseña es temporal');

        return $mail->esCuentaNueva === true;
    });
});
