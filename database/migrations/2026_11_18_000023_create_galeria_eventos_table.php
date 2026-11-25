<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('galeria_eventos', function (Blueprint $table) {
            $table->bigIncrements('id_galeria');
            $table->unsignedBigInteger('id_docente');
            $table->foreign('id_docente')->references('id_docente')->on('docentes')->restrictOnDelete();
            $table->string('titulo_evento');
            $table->enum('tipo_multimedia', ['Foto', 'Video']);
            $table->string('archivo_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('galeria_eventos');
    }
};
