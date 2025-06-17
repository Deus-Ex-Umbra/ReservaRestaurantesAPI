<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Usuario;

class UsuarioClienteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id_usuario' => Usuario::factory()->create(['rol' => 'cliente']),
            'nombres' => fake()->firstName(),
            'apellidos' => fake()->lastName(),
            'telefono' => fake()->phoneNumber(),
        ];
    }
}
