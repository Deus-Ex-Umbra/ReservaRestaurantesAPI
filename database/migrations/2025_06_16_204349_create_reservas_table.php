<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_usuario_cliente');
            $table->unsignedBigInteger('id_restaurante');
            $table->date('fecha_reserva');
            $table->time('hora_reserva');
            $table->decimal('precio_total', 8, 2);
            $table->decimal('precio_reserva', 8, 2);
            $table->text('comentarios_reserva')->nullable();
            $table->enum('estado_reserva', ['pendiente', 'aceptada', 'rechazada', 'completada', 'cancelada'])->default('pendiente');
            $table->timestamp('fecha_creacion_reserva')->useCurrent();
            $table->integer('personas_reserva')->default(1);
            $table->string('telefono_contacto_reserva')->nullable();

            $table->foreign('id_usuario_cliente')
                ->references('id')
                ->on('usuarios_clientes')
                ->onDelete('cascade');
            $table->foreign('id_restaurante')
                ->references('id')
                ->on('usuarios_restaurantes')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};
