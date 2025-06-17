<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mesas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_restaurante');
            $table->integer('numero_mesa');
            $table->integer('capacidad_mesa');
            $table->enum('estado_mesa', ['disponible', 'reservada', 'ocupada'])->default('disponible');
            $table->decimal('precio_mesa', 8, 2)->default(0.00);
            $table->string('ruta_imagen_mesa')->nullable();
            $table->foreign('id_restaurante')
                ->references('id')
                ->on('usuarios_restaurantes')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mesas');
    }
};