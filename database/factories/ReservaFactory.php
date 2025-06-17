<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class ReservaFactory extends Factory
{
    public function definition(): array
    {
        $precio_total = fake()->randomFloat(2, 80, 500);

        return [
            'fecha_reserva' => fake()->dateTimeBetween('-1 year', '-1 day')->format('Y-m-d'),
            'hora_reserva' => fake()->time('H:i'),
            'precio_total' => $precio_total,
            'precio_reserva' => $precio_total * 0.25,
            'comentarios_reserva' => fake()->optional()->sentence(),
            'estado_reserva' => 'completada',
            'fecha_creacion_reserva' => Carbon::now(),
            'personas_reserva' => fake()->numberBetween(1, 8),
            'telefono_contacto_reserva' => fake()->phoneNumber(),
        ];
    }
}
