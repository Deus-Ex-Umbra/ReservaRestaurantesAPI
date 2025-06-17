<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reserva_mesa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_reserva');
            $table->unsignedBigInteger('id_mesa');

            $table->foreign('id_reserva')
                  ->references('id')
                  ->on('reservas')
                  ->onDelete('cascade');

            $table->foreign('id_mesa')
                  ->references('id')
                  ->on('mesas')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reserva_mesa');
    }
};
