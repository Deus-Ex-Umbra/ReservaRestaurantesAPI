<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calificaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_usuario_cliente');
            $table->unsignedBigInteger('id_restaurante');
            $table->unsignedBigInteger('id_reserva');
            $table->decimal('puntuacion', 2, 1);
            $table->text('comentario')->nullable();
            $table->date('fecha_calificacion');
            $table->boolean('reportada')->default(false);
            $table->foreign('id_usuario_cliente')
                ->references('id')
                ->on('usuarios_clientes')
                ->onDelete('cascade');
            $table->foreign('id_restaurante')
                ->references('id')
                ->on('usuarios_restaurantes')
                ->onDelete('cascade');
            $table->foreign('id_reserva')
                ->references('id')
                ->on('reservas')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calificaciones');
    }
};