<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PreferenciaFactory extends Factory
{
    public function definition(): array
    {
        $tipos_restaurante = [
            'comida-tradicional', 'parrilla', 'comida-rapida', 'italiana',
            'china', 'internacional', 'postres', 'bebidas'
        ];

        return [
            'tipo_restaurante_preferencia' => fake()->randomElement($tipos_restaurante),
            'calificacion_minima_preferencia' => fake()->randomFloat(1, 3.0, 5.0),
            'precio_maximo_preferencia' => fake()->randomFloat(2, 50, 400),
        ];
    }
}
