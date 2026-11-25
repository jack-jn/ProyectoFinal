<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_sesion_completadas', function (Blueprint $table) {
            $table->bigIncrements('id_detalle_sesion');
            $table->unsignedBigInteger('id_detalle_matricula');
            $table->foreign('id_detalle_matricula')->references('id_detalle_matricula')->on('detalle_matriculas')->cascadeOnDelete();
            $table->unsignedBigInteger('id_sesion');
            $table->foreign('id_sesion')->references('id_sesion')->on('sesiones')->cascadeOnDelete();
            $table->dateTime('fecha_completada');
            $table->timestamps();

            $table->unique(['id_detalle_matricula', 'id_sesion']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_sesion_completadas');
    }
};
