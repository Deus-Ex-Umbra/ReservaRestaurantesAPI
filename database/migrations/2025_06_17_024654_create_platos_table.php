<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_menu');
            $table->string('nombre_plato');
            $table->text('descripcion_plato')->nullable();
            $table->decimal('precio_plato', 8, 2);
            $table->string('ruta_imagen_plato')->nullable();
            $table->boolean('disponible')->default(true);
            $table->foreign('id_menu')
                ->references('id')
                ->on('menus')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platos');
    }
};