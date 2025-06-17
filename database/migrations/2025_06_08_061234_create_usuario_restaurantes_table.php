<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios_restaurantes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_usuario')->unique();
            $table->string('nombre_restaurante');
            $table->string('direccion');
            $table->string('telefono')->nullable();
            $table->string('categoria')->nullable();
            $table->time('horario_apertura')->nullable();
            $table->time('horario_cierre')->nullable();
            $table->enum('tipo_restaurante', [
                'comida-tradicional',
                'parrilla', 
                'comida-rapida',
                'italiana',
                'china',
                'internacional',
                'postres',
                'bebidas'
            ])->default('comida-rapida');
            $table->decimal('calificacion', 2, 1)->default(0.0);
            $table->string('ruta_imagen_restaurante')->nullable();
            $table->string('ruta_qr_pago')->nullable();
            $table->foreign('id_usuario')
                ->references('id')
                ->on('usuarios')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios_restaurantes');
    }
};