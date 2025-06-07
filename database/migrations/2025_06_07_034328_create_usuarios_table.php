<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('correo')->unique()->notNull();
            $table->string('contraseña')->notNull();
            $table->enum('rol', ['administrador', 'cliente', 'restaurante'])->default('cliente')->notNull();
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
