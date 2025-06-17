<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reserva_plato', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_reserva');
            $table->unsignedBigInteger('id_plato');
            $table->integer('cantidad')->default(1);
            
            $table->foreign('id_reserva')
                  ->references('id')
                  ->on('reservas')
                  ->onDelete('cascade');

            $table->foreign('id_plato')
                  ->references('id')
                  ->on('platos')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reserva_plato');
    }
};
