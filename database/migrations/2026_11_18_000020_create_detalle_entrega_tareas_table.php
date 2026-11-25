<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_entrega_tareas', function (Blueprint $table) {
            $table->bigIncrements('id_detalle_entrega');
            $table->unsignedBigInteger('id_tarea');
            $table->foreign('id_tarea')->references('id_tarea')->on('tareas_asignadas')->cascadeOnDelete();
            $table->unsignedBigInteger('id_detalle_matricula');
            $table->foreign('id_detalle_matricula')->references('id_detalle_matricula')->on('detalle_matriculas')->cascadeOnDelete();
            $table->string('video_evidencia');
            $table->enum('estado_revision', ['Pendiente', 'Aprobada', 'Observada'])->default('Pendiente');
            $table->text('comentario_docente')->nullable();
            $table->dateTime('fecha_entrega');
            $table->dateTime('fecha_revision')->nullable();
            $table->timestamps();

            $table->unique(['id_tarea', 'id_detalle_matricula']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_entrega_tareas');
    }
};
