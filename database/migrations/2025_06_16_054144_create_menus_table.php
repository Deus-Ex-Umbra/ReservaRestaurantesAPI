<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_restaurante');
            $table->string('nombre_menu');
            $table->text('descripcion_menu')->nullable();
            $table->enum('tipo_menu', [
                'comida-tradicional',
                'parrilla', 
                'comida-rapida',
                'italiana',
                'china',
                'internacional',
                'postres',
                'bebidas'
            ]);
            $table->string('ruta_imagen_menu')->nullable();
            $table->foreign('id_restaurante')
                ->references('id')
                ->on('usuarios_restaurantes')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};