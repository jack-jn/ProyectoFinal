<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditoria_credenciales', function (Blueprint $table) {
            $table->bigIncrements('id_auditoria');
            $table->unsignedBigInteger('id_usuario_secretaria');
            $table->foreign('id_usuario_secretaria')->references('id_usuario')->on('usuarios_sistema')->restrictOnDelete();
            $table->unsignedBigInteger('id_usuario_alumno');
            $table->foreign('id_usuario_alumno')->references('id_usuario')->on('usuarios_sistema')->restrictOnDelete();
            $table->enum('accion', ['ver', 'copiar', 'restablecer']);
            $table->dateTime('fecha_accion');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditoria_credenciales');
    }
};
