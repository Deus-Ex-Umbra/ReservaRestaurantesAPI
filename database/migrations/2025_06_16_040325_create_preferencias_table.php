<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * protected $fillable = [
     *    'id_usuario_cliente',
     *    'tipo_restaurante_preferencia',
     *    'precio_minimo_preferencia',
     *    'precio_maximo_preferencia',
     *    'calificacion_minima_preferencia',
     *];
     */
    public function up(): void
    {
        Schema::create('preferencias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_usuario_cliente')->unique();
            $table->string('tipo_restaurante_preferencia')->nullable();
            $table->decimal('precio_minimo_preferencia', 10, 2)->nullable();
            $table->decimal('precio_maximo_preferencia', 10, 2)->nullable();
            $table->decimal('calificacion_minima_preferencia', 3, 2)->nullable();
            $table->foreign('id_usuario_cliente')
                ->references('id')
                ->on('usuario_clientes')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('preferencias');
    }
};
