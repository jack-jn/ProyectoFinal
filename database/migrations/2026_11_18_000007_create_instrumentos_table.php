<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instrumentos', function (Blueprint $table) {
            $table->bigIncrements('id_instrumento');
            $table->string('nombre_instrumento');
            $table->string('icono');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instrumentos');
    }
};
