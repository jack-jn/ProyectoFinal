<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cursos', function (Blueprint $table) {
            $table->bigIncrements('id_curso');
            $table->unsignedBigInteger('id_docente');
            $table->foreign('id_docente')->references('id_docente')->on('docentes')->restrictOnDelete();
            $table->unsignedBigInteger('id_instrumento');
            $table->foreign('id_instrumento')->references('id_instrumento')->on('instrumentos')->restrictOnDelete();
            $table->string('codigo_curso')->unique();
            $table->string('nombre_curso');
            $table->enum('nivel', ['Básico', 'Intermedio', 'Avanzado']);
            $table->enum('modalidad', ['Presencial', 'Virtual', 'Presencial y virtual']);
            $table->decimal('costo', 8, 2);
            $table->integer('cupos');
            $table->string('imagen_url')->nullable();
            $table->boolean('estado_habilitado')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cursos');
    }
};
