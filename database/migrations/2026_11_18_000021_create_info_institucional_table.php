<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('info_institucional', function (Blueprint $table) {
            $table->bigIncrements('id_info');
            $table->unsignedBigInteger('id_docente');
            $table->foreign('id_docente')->references('id_docente')->on('docentes')->restrictOnDelete();
            $table->text('quienes_somos');
            $table->string('telefono_contacto', 9);
            $table->string('correo_contacto');
            $table->string('direccion');
            $table->time('hora_apertura');
            $table->time('hora_cierre');
            $table->dateTime('fecha_actualizado');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('info_institucional');
    }
};
