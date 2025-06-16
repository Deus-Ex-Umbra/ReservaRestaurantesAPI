<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * protected $fillable = [
     *    'id_usuario',
     *    'nombre_restaurante',
     *    'direccion',
     *    'telefono',
     *    'categoria',
     *    'horario_apertura',
     *    'horario_cierre',
     *    'tipo_restaurante',
     *    'calificacion',
     *    'ruta_imagen_restaurante',
     *];
     */
    public function up(): void
    {
        Schema::create('usuario_restaurantes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_usuario')->unique();
            $table->string('nombre_restaurante');
            $table->string('direccion');
            $table->string('telefono')->nullable();
            $table->string('categoria')->nullable();
            $table->time('horario_apertura')->nullable();
            $table->time('horario_cierre')->nullable();
            $table->enum('tipo_restaurante', ['nacional', 'gourmet', 'internacional', 'casual', 'comida rápida', 'cafetería', 'vegetariano'])->default('casual');
            $table->decimal('calificacion', 2, 1)->default(0.0);
            $table->string('ruta_imagen_restaurante')->nullable();
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
        Schema::dropIfExists('usuario_restaurantes');
    }
};
