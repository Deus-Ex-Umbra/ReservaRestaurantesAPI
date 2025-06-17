<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PlatoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre_plato' => fake()->words(2, true),
            'descripcion_plato' => fake()->paragraph(2),
            'precio_plato' => fake()->randomFloat(2, 20, 150),
            'disponible' => true,
        ];
    }
}
