<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matriculas', function (Blueprint $table) {
            $table->bigIncrements('id_matricula');
            $table->unsignedBigInteger('id_alumno');
            $table->foreign('id_alumno')->references('id_alumno')->on('alumnos')->restrictOnDelete();
            $table->string('codigo_matricula')->unique();
            $table->dateTime('fecha_matricula');
            $table->decimal('monto_total', 8, 2);
            $table->enum('estado_matricula', ['Pendiente de pago', 'Confirmada', 'Rechazada']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matriculas');
    }
};
