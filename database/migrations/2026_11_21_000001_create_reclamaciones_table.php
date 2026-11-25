<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('reclamaciones')) {
            return;
        }

        Schema::create('reclamaciones', function (Blueprint $table): void {
            $table->id('id_reclamacion');
            $table->string('codigo', 30)->unique();
            $table->enum('tipo', ['Reclamo', 'Queja']);
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('dni', 8);
            $table->string('telefono', 9);
            $table->string('correo');
            $table->string('domicilio');
            $table->string('servicio');
            $table->decimal('monto_involucrado', 10, 2)->nullable();
            $table->date('fecha_incidente');
            $table->text('descripcion');
            $table->text('pedido_consumidor');
            $table->enum('estado', ['Pendiente', 'Atendido'])->default('Pendiente');
            $table->text('respuesta')->nullable();
            $table->unsignedBigInteger('atendido_por')->nullable();
            $table->timestamp('fecha_atencion')->nullable();
            $table->timestamps();

            $table->foreign('atendido_por')
                ->references('id_usuario')
                ->on('usuarios_sistema')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reclamaciones');
    }
};
