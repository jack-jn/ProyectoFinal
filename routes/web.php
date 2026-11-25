<?php

use App\Livewire\Auth\CambiarClave;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\RecuperarClave;
use App\Livewire\Docente\Panel as DocentePanel;
use App\Livewire\Estudiante\Aula as EstudianteAula;
use App\Livewire\Publico\Galeria as PublicoGaleria;
use App\Livewire\Publico\Inicio as PublicoInicio;
use App\Livewire\Publico\Matricula as PublicoMatricula;
use App\Livewire\Publico\Reclamaciones as PublicoReclamaciones;
use App\Livewire\Reclamaciones\Gestion as ReclamacionesGestion;
use App\Livewire\Secretaria\Pagos as SecretariaPagos;
use App\Models\Pago;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', PublicoInicio::class)->name('home');
Route::get('/matricula', PublicoMatricula::class)->name('publico.matricula');
Route::get('/galeria', PublicoGaleria::class)->name('publico.galeria');
Route::get('/reclamaciones', PublicoReclamaciones::class)->name('publico.reclamaciones');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', Login::class)->name('login');
    Route::get('/recuperar-clave', RecuperarClave::class)->name('recuperar-clave');
});

Route::post('/logout', function (Request $request): RedirectResponse {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login');
})->middleware('auth')->name('logout');

Route::get('/cambiar-clave', CambiarClave::class)
    ->middleware(['auth', 'rol.dunamys'])
    ->name('cambiar-clave');

Route::get('/docente/panel', DocentePanel::class)
    ->middleware(['auth', 'rol.dunamys:docente'])
    ->name('docente.panel');

Route::get('/docente/reclamaciones', ReclamacionesGestion::class)
    ->middleware(['auth', 'rol.dunamys:docente', 'permission:atender_reclamaciones'])
    ->name('docente.reclamaciones');

Route::get('/secretaria/panel', SecretariaPagos::class)
    ->middleware(['auth', 'rol.dunamys:secretaria'])
    ->name('secretaria.panel');

Route::get('/secretaria/reclamaciones', ReclamacionesGestion::class)
    ->middleware(['auth', 'rol.dunamys:secretaria', 'permission:atender_reclamaciones'])
    ->name('secretaria.reclamaciones');

Route::get('/estudiante/aula', EstudianteAula::class)
    ->middleware(['auth', 'rol.dunamys:alumno'])
    ->name('estudiante.aula');

Route::get('/comprobantes/{pago}', function (Pago $pago) {
    abort_unless(Auth::user()->rol === 'docente'
        || collect(['resumen', 'pendientes', 'historial', 'acceso'])->contains(fn (string $permiso) => Auth::user()->can($permiso)), 403);

    if (! Storage::disk('local')->exists($pago->comprobante_url)) {
        abort(404);
    }

    return Storage::disk('local')->response($pago->comprobante_url);
})->middleware(['auth', 'rol.dunamys:secretaria,docente'])->name('comprobantes.ver');
