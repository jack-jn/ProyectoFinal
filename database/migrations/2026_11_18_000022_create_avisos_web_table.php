<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('avisos_web', function (Blueprint $table) {
            $table->bigIncrements('id_aviso');
            $table->unsignedBigInteger('id_docente');
            $table->foreign('id_docente')->references('id_docente')->on('docentes')->restrictOnDelete();
            $table->string('titulo_aviso');
            $table->text('texto_aviso');
            $table->dateTime('fecha_publicacion');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avisos_web');
    }
};
