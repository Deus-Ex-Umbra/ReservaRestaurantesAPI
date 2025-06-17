<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MenuFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre_menu' => 'Menú ' . fake()->word(),
            'descripcion_menu' => fake()->sentence(),
            'tipo_menu' => fake()->randomElement(['comida-tradicional', 'parrilla', 'comida-rapida', 'italiana', 'china', 'internacional', 'postres', 'bebidas']),
        ];
    }
}
