<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_curso_horarios', function (Blueprint $table) {
            $table->bigIncrements('id_detalle_horario');
            $table->unsignedBigInteger('id_curso');
            $table->foreign('id_curso')->references('id_curso')->on('cursos')->cascadeOnDelete();
            $table->unsignedBigInteger('id_dia');
            $table->foreign('id_dia')->references('id_dia')->on('dias_semana')->restrictOnDelete();
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fin')->nullable();
            $table->timestamps();

            $table->unique(['id_curso', 'id_dia']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_curso_horarios');
    }
};
