<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios_administradores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_usuario')->unique();
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('telefono')->nullable();
            $table->string('ruta_imagen_administrador')->nullable();
            $table->foreign('id_usuario')
                ->references('id')
                ->on('usuarios')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios_administradores');
    }
};