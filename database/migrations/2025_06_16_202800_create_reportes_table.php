<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reportes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_usuario_reportante');
            $table->enum('tipo_usuario_reportante', ['cliente', 'restaurante']);
            $table->unsignedBigInteger('id_usuario_reportado')->nullable();
            $table->enum('tipo_usuario_reportado', ['cliente', 'restaurante'])->nullable();
            $table->unsignedBigInteger('id_calificacion')->nullable();
            $table->enum('motivo_reporte', [
                'contenido-inapropiado', 
                'informacion-falsa', 
                'spam', 
                'acoso', 
                'discriminacion', 
                'otro'
            ]);
            $table->text('descripcion_reporte');
            $table->date('fecha_reporte');
            $table->enum('estado_reporte', ['pendiente', 'revisado', 'aceptado', 'rechazado'])->default('pendiente');
            $table->boolean('revisado_por_admin')->default(false);
            $table->foreign('id_calificacion')
                ->references('id')
                ->on('calificaciones')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reportes');
    }
};