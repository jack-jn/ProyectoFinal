<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios_sistema', function (Blueprint $table) {
            $table->bigIncrements('id_usuario');
            $table->unsignedBigInteger('id_persona')->unique();
            $table->foreign('id_persona')->references('id_persona')->on('personas')->restrictOnDelete();
            $table->string('correo_usuario')->unique();
            $table->string('contrasena');
            $table->enum('rol', ['docente', 'secretaria', 'alumno']);
            $table->enum('estado_cuenta', ['Activo', 'Inactivo'])->default('Activo');
            $table->boolean('debe_cambiar_contrasena')->default(false);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios_sistema');
    }
};
