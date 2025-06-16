<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * protected $fillable = [
     *    'id_usuario',
     *    'nombres',
     *    'apellidos',
     *    'telefono',
     *    'ruta_imagen_cliente'
     *];
     */
    public function up(): void
    {
        Schema::create('usuario_clientes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_usuario')->unique();
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('telefono')->nullable();
            $table->string('ruta_imagen_cliente')->nullable();
            $table->foreign('id_usuario')
                ->references('id')
                ->on('usuarios')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuario_clientes');
    }
};
