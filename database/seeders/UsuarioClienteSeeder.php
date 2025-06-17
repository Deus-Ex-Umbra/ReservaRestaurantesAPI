<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\UsuarioCliente;
use App\Models\Preferencia;

class UsuarioClienteSeeder extends Seeder
{
    public function run(): void
    {
        UsuarioCliente::factory(10000)->create()->each(function ($cliente) {
            $cliente->preferencias()->save(Preferencia::factory()->make());
        });
    }
}
