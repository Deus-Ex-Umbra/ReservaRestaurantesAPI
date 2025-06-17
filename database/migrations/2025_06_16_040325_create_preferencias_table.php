<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preferencias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_usuario_cliente')->unique();
            $table->enum('tipo_restaurante_preferencia', [
                'comida-tradicional',
                'parrilla', 
                'comida-rapida',
                'italiana',
                'china',
                'internacional',
                'postres',
                'bebidas'
            ])->nullable();
            $table->decimal('calificacion_minima_preferencia', 3, 2)->nullable();
            $table->foreign('id_usuario_cliente')
                ->references('id')
                ->on('usuarios_clientes')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preferencias');
    }
};