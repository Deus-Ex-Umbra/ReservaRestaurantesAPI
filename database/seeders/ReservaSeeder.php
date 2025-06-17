<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Reserva;
use App\Models\Calificacion;
use App\Models\UsuarioCliente;
use App\Models\UsuarioRestaurante;

class ReservaSeeder extends Seeder
{
    public function run(): void
    {
        $clientes = UsuarioCliente::all();
        $restaurantes = UsuarioRestaurante::with(['mesas', 'menus.platos'])->get();

        if ($restaurantes->isEmpty()) {
            return;
        }
        
        foreach ($clientes as $cliente) {
            for ($i = 0; $i < 5; $i++) {
                $restaurante_aleatorio = $restaurantes->random();
                $mesas_disponibles = $restaurante_aleatorio->mesas;
                $platos_disponibles = $restaurante_aleatorio->menus->flatMap->platos;

                if ($mesas_disponibles->isEmpty() || $platos_disponibles->isEmpty()) {
                    continue;
                }

                $reserva = Reserva::factory()->create([
                    'id_usuario_cliente' => $cliente->id,
                    'id_restaurante' => $restaurante_aleatorio->id,
                ]);

                $reserva->mesas()->attach($mesas_disponibles->random(rand(1, 2))->pluck('id'));
                $reserva->platos()->attach($platos_disponibles->random(rand(1, 5))->pluck('id'));

                Calificacion::factory()->create([
                    'id_usuario_cliente' => $cliente->id,
                    'id_restaurante' => $restaurante_aleatorio->id,
                    'id_reserva' => $reserva->id,
                ]);
            }
        }
    }
}
