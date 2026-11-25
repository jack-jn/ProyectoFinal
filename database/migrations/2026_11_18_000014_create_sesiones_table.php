<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sesiones', function (Blueprint $table) {
            $table->bigIncrements('id_sesion');
            $table->unsignedBigInteger('id_curso');
            $table->foreign('id_curso')->references('id_curso')->on('cursos')->cascadeOnDelete();
            $table->integer('numero_orden');
            $table->string('titulo_sesion');
            $table->timestamps();

            $table->unique(['id_curso', 'numero_orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sesiones');
    }
};
