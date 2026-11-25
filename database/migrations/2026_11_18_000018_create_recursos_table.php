<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recursos', function (Blueprint $table) {
            $table->bigIncrements('id_recurso');
            $table->unsignedBigInteger('id_tipo_recurso');
            $table->foreign('id_tipo_recurso')->references('id_tipo_recurso')->on('tipos_recurso')->restrictOnDelete();
            $table->unsignedBigInteger('id_leccion')->nullable();
            $table->foreign('id_leccion')->references('id_leccion')->on('lecciones')->cascadeOnDelete();
            $table->unsignedBigInteger('id_tarea')->nullable();
            $table->foreign('id_tarea')->references('id_tarea')->on('tareas_asignadas')->cascadeOnDelete();
            $table->string('titulo_recurso');
            $table->string('url_recurso');
            $table->integer('duracion')->nullable();
            $table->decimal('tamano_mb', 6, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recursos');
    }
};
