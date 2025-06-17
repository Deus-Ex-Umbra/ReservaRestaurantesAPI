<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Usuario;
use App\Models\UsuarioAdministrador;

class UsuarioAdministradorSeeder extends Seeder
{
    public function run(): void
    {
        $usuario1 = Usuario::create([
            'correo' => 'gab.aparicio.ll@gmail.com',
            'contraseña' => bcrypt('B4phy_B4lph0m3t'),
            'rol' => 'administrador'
        ]);

        UsuarioAdministrador::create([
            'id_usuario' => $usuario1->id,
            'nombres' => 'Gabriel',
            'apellidos' => 'Aparicio',
            'telefono' => '12345678'
        ]);

        $usuario2 = Usuario::create([
            'correo' => 'admin@example.com',
            'contraseña' => bcrypt('password'),
            'rol' => 'administrador'
        ]);

        UsuarioAdministrador::create([
            'id_usuario' => $usuario2->id,
            'nombres' => 'Admin',
            'apellidos' => 'User',
            'telefono' => '87654321'
        ]);
    }
}
