<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lecciones', function (Blueprint $table) {
            $table->bigIncrements('id_leccion');
            $table->unsignedBigInteger('id_sesion');
            $table->foreign('id_sesion')->references('id_sesion')->on('sesiones')->cascadeOnDelete();
            $table->string('codigo_leccion');
            $table->integer('numero_orden');
            $table->string('titulo_leccion');
            $table->text('descripcion')->nullable();
            $table->timestamps();

            $table->unique(['id_sesion', 'codigo_leccion']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lecciones');
    }
};
