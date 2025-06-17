<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MesaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'numero_mesa' => 1,
            'capacidad_mesa' => fake()->randomElement([2, 4, 6, 8]),
            'estado_mesa' => 'disponible',
            'precio_mesa' => fake()->randomFloat(2, 10, 50),
        ];
    }
}
