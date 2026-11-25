<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumnos', function (Blueprint $table) {
            $table->bigIncrements('id_alumno');
            $table->unsignedBigInteger('id_persona')->unique();
            $table->foreign('id_persona')->references('id_persona')->on('personas')->restrictOnDelete();
            $table->unsignedBigInteger('id_apoderado')->nullable();
            $table->foreign('id_apoderado')->references('id_apoderado')->on('apoderados')->restrictOnDelete();
            $table->string('codigo_alumno')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumnos');
    }
};
