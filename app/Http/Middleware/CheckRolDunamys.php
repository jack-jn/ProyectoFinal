<?php

namespace App\Http\Middleware;

use App\Models\UsuarioSistema;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRolDunamys
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        /** @var UsuarioSistema $usuario */
        $usuario = Auth::user();

        if ($usuario->estado_cuenta !== 'Activo') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('auth_error', 'Tu cuenta se encuentra inactiva.');
        }

        if ($usuario->debe_cambiar_contrasena && ! $request->routeIs('cambiar-clave', 'logout')) {
            return redirect()->route('cambiar-clave');
        }

        if (! empty($roles) && ! in_array($usuario->rol, $roles, true)) {
            abort(403, 'No tienes autorización para acceder a este módulo.');
        }

        return $next($request);
    }
}
