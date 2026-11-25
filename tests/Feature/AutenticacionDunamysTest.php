<?php

use App\Livewire\Auth\CambiarClave;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\RecuperarClave;
use App\Models\Persona;
use App\Models\UsuarioSistema;
use Database\Seeders\RolesPermisosSeeder;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

function crearUsuarioPrueba(string $rol, string $correo, bool $debeCambiar = false, string $estado = 'Activo'): UsuarioSistema
{
    $persona = Persona::query()->create([
        'nombres' => 'Usuario',
        'apellido_paterno' => ucfirst($rol),
        'apellido_materno' => 'Test',
        'dni' => (string) random_int(10000000, 99999999),
        'fecha_nacimiento' => '2000-01-01',
        'correo' => $correo,
        'celular' => '952233266',
    ]);

    $usuario = UsuarioSistema::query()->create([
        'id_persona' => $persona->id_persona,
        'correo_usuario' => $correo,
        'contrasena' => Hash::make('12345678'),
        'rol' => $rol,
        'estado_cuenta' => $estado,
        'debe_cambiar_contrasena' => $debeCambiar,
    ]);

    test()->seed(RolesPermisosSeeder::class);

    return $usuario;
}

test('muestra las pantallas de login y recuperacion de clave', function () {
    $this->get('/login')
        ->assertOk()
        ->assertSee('Iniciar')
        ->assertSee('Sesión');

    $this->get('/recuperar-clave')
        ->assertOk()
        ->assertSee('Recuperar')
        ->assertSee('Contraseña');
});

test('redirige al panel correspondiente segun el rol del usuario', function (string $rol, string $correo, string $rutaEsperada) {
    $usuario = crearUsuarioPrueba($rol, $correo);

    Livewire::test(Login::class)
        ->set('correo_usuario', $correo)
        ->set('contrasena', '12345678')
        ->call('ingresar')
        ->assertRedirect(route($rutaEsperada));

    $this->assertAuthenticatedAs($usuario);
})->with([
    'docente' => ['docente', 'docente@dunamys.edu.pe', 'docente.panel'],
    'secretaria' => ['secretaria', 'secretaria@dunamys.edu.pe', 'secretaria.panel'],
    'alumno' => ['alumno', 'alumno@dunamys.edu.pe', 'estudiante.aula'],
]);

test('rechaza credenciales incorrectas y bloquea tras 5 intentos fallidos', function () {
    crearUsuarioPrueba('alumno', 'alumno@dunamys.edu.pe');

    $componente = Livewire::test(Login::class)
        ->set('correo_usuario', 'alumno@dunamys.edu.pe')
        ->set('contrasena', 'clave-incorrecta');

    for ($i = 1; $i <= 4; $i++) {
        $componente->call('ingresar')
            ->assertSee("Correo o contraseña incorrectos. (Intento {$i} de 5)");
    }

    $componente->call('ingresar')
        ->assertSee('Demasiados intentos fallidos. Por seguridad, el acceso está bloqueado');

    $this->assertGuest();
});

test('middleware CheckRolDunamys impide que un alumno entre al panel de docente o secretaria', function () {
    $alumno = crearUsuarioPrueba('alumno', 'alumno@dunamys.edu.pe');

    $this->actingAs($alumno)
        ->get('/docente/panel')
        ->assertForbidden();

    $this->actingAs($alumno)
        ->get('/secretaria/panel')
        ->assertForbidden();

    $this->actingAs($alumno)
        ->get('/estudiante/aula')
        ->assertOk();
});

test('obliga a cambiar la contrasena inicial cuando debe_cambiar_contrasena es true', function () {
    $alumno = crearUsuarioPrueba('alumno', 'nuevo.alumno@dunamys.edu.pe', debeCambiar: true);

    Livewire::test(Login::class)
        ->set('correo_usuario', 'nuevo.alumno@dunamys.edu.pe')
        ->set('contrasena', '12345678')
        ->call('ingresar')
        ->assertRedirect(route('cambiar-clave'));

    $this->actingAs($alumno)
        ->get('/estudiante/aula')
        ->assertRedirect(route('cambiar-clave'));

    Livewire::actingAs($alumno)
        ->test(CambiarClave::class)
        ->set('nueva_contrasena', 'Dunamys2026')
        ->set('repetir_contrasena', 'Dunamys2026')
        ->call('guardarContrasena')
        ->assertRedirect(route('estudiante.aula'));

    expect($alumno->fresh()->debe_cambiar_contrasena)->toBeFalse()
        ->and(Hash::check('Dunamys2026', $alumno->fresh()->contrasena))->toBeTrue();
});

test('permite recuperar la contrasena mediante codigo de 6 caracteres', function () {
    $alumno = crearUsuarioPrueba('alumno', 'lucia@dunamys.edu.pe');

    $componente = Livewire::test(RecuperarClave::class)
        ->set('correo', 'noexiste@dunamys.edu.pe')
        ->call('enviarCodigo')
        ->assertHasErrors(['correo']);

    $componente->set('correo', 'lucia@dunamys.edu.pe')
        ->call('enviarCodigo')
        ->assertSet('paso', 2);

    $codigo = $componente->get('codigoGenerado');
    expect(strlen($codigo))->toBe(6);

    $componente->set('codigoIngresado', $codigo)
        ->call('verificarCodigo')
        ->assertSet('paso', 3);

    $componente->set('nueva_contrasena', 'NuevaClave2026')
        ->set('repetir_contrasena', 'NuevaClave2026')
        ->call('guardarNuevaContrasena')
        ->assertRedirect(route('login'));

    expect(Hash::check('NuevaClave2026', $alumno->fresh()->contrasena))->toBeTrue();
});
