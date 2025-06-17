<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsuarioFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'correo' => fake()->unique()->safeEmail(),
            'contraseña' => static::$password ??= Hash::make('password'),
            'rol' => 'cliente', 
        ];
    }
}
