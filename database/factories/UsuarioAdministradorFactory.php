<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Usuario;

class UsuarioAdministradorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id_usuario' => Usuario::factory()->create(['rol' => 'administrador']),
            'nombres' => fake()->firstName(),
            'apellidos' => fake()->lastName(),
            'telefono' => fake()->phoneNumber(),
        ];
    }
}
