<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('secretarias', function (Blueprint $table) {
            $table->bigIncrements('id_secretaria');
            $table->unsignedBigInteger('id_persona')->unique();
            $table->foreign('id_persona')->references('id_persona')->on('personas')->restrictOnDelete();
            $table->string('codigo_secretaria')->unique();
            $table->string('turno_atencion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('secretarias');
    }
};
