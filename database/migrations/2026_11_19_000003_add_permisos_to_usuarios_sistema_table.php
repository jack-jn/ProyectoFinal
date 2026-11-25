<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('usuarios_sistema', 'permisos')) {
            Schema::table('usuarios_sistema', function (Blueprint $table) {
                $table->json('permisos')->nullable()->after('debe_cambiar_contrasena');
            });
        }

        $permisosSecretaria = json_encode([
            'resumen',
            'pendientes',
            'historial',
            'acceso',
            'validar_observar',
        ]);

        $permisosAlumno = json_encode([
            'aula_virtual',
            'ver_recursos',
            'entregar_tareas',
        ]);

        DB::table('usuarios_sistema')
            ->where('rol', 'secretaria')
            ->whereNull('permisos')
            ->update(['permisos' => $permisosSecretaria]);

        DB::table('usuarios_sistema')
            ->where('rol', 'alumno')
            ->whereNull('permisos')
            ->update(['permisos' => $permisosAlumno]);

        // Actualizar cursos existentes a modalidad Virtual para esta etapa 100% virtual
        if (Schema::hasTable('cursos')) {
            DB::table('cursos')
                ->where('modalidad', '!=', 'Virtual')
                ->update(['modalidad' => 'Virtual']);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('usuarios_sistema', 'permisos')) {
            Schema::table('usuarios_sistema', function (Blueprint $table) {
                $table->dropColumn('permisos');
            });
        }
    }
};
