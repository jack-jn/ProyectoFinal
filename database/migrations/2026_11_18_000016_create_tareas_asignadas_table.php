<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tareas_asignadas', function (Blueprint $table) {
            $table->bigIncrements('id_tarea');
            $table->unsignedBigInteger('id_sesion')->unique();
            $table->foreign('id_sesion')->references('id_sesion')->on('sesiones')->cascadeOnDelete();
            $table->string('titulo_tarea');
            $table->text('descripcion');
            $table->date('fecha_limite')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tareas_asignadas');
    }
};
