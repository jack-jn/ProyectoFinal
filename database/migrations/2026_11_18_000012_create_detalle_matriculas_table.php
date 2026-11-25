<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_matriculas', function (Blueprint $table) {
            $table->bigIncrements('id_detalle_matricula');
            $table->unsignedBigInteger('id_matricula');
            $table->foreign('id_matricula')->references('id_matricula')->on('matriculas')->cascadeOnDelete();
            $table->unsignedBigInteger('id_curso');
            $table->foreign('id_curso')->references('id_curso')->on('cursos')->restrictOnDelete();
            $table->dateTime('fecha_inscripcion');
            $table->decimal('precio_cobrado', 8, 2);
            $table->enum('estado_acceso', ['Bloqueado', 'Activo'])->default('Bloqueado');
            $table->timestamps();

            $table->unique(['id_matricula', 'id_curso']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_matriculas');
    }
};
