<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class CalificacionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'puntuacion' => fake()->randomFloat(1, 3, 5),
            'comentario' => fake()->paragraph(),
            'fecha_calificacion' => Carbon::now()->subDays(rand(1, 365))->toDateString(),
            'reportada' => false,
        ];
    }
}
