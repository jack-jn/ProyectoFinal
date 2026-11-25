<?php

namespace Database\Seeders;

use App\Models\UsuarioSistema;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesPermisosSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        DB::transaction(function (): void {
            foreach (['docente', 'admin', 'secretaria', 'alumno'] as $nombre) {
                Role::firstOrCreate(['name' => $nombre, 'guard_name' => 'web']);

                foreach (UsuarioSistema::permisosPorDefecto($nombre) as $permiso) {
                    Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'web']);
                }
            }

            UsuarioSistema::query()->with('roles')->eachById(function (UsuarioSistema $usuario): void {
                if (! $usuario->hasRole($usuario->rol)) {
                    $usuario->sincronizarAcceso($usuario->permisosIniciales());
                }
            });
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
