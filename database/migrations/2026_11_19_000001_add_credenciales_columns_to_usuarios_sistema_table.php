<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios_sistema', function (Blueprint $table) {
            $table->text('password_temporal')->nullable()->after('contrasena');
            $table->timestamp('correo_enviado_at')->nullable()->after('debe_cambiar_contrasena');
        });
    }

    public function down(): void
    {
        Schema::table('usuarios_sistema', function (Blueprint $table) {
            $table->dropColumn(['password_temporal', 'correo_enviado_at']);
        });
    }
};
