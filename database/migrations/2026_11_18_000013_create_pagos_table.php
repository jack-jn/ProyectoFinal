<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->bigIncrements('id_pago');
            $table->unsignedBigInteger('id_matricula');
            $table->foreign('id_matricula')->references('id_matricula')->on('matriculas')->restrictOnDelete();
            $table->unsignedBigInteger('id_secretaria')->nullable();
            $table->foreign('id_secretaria')->references('id_secretaria')->on('secretarias')->restrictOnDelete();
            $table->enum('metodo_pago', ['Yape']);
            $table->string('numero_operacion')->nullable()->unique();
            $table->string('comprobante_url');
            $table->decimal('monto_pagado', 8, 2);
            $table->dateTime('fecha_pago');
            $table->enum('estado_validacion', ['Pendiente', 'Validado', 'Observado'])->default('Pendiente');
            $table->text('observacion')->nullable();
            $table->dateTime('fecha_validacion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
