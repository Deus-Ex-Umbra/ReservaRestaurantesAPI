<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\UsuarioRestaurante;
use App\Models\Menu;
use App\Models\Mesa;
use App\Models\Plato;

class UsuarioRestauranteSeeder extends Seeder
{
    public function run(): void
    {
        UsuarioRestaurante::factory(100)->create()->each(function ($restaurante) {
            $restaurante->mesas()->saveMany(Mesa::factory(rand(5, 20))->make());

            $restaurante->menus()->saveMany(Menu::factory(rand(2, 5))->make())
                ->each(function ($menu) {
                    $menu->platos()->saveMany(Plato::factory(rand(5, 15))->make());
                });
        });
    }
}
