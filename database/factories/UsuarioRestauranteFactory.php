<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Usuario;

class UsuarioRestauranteFactory extends Factory
{
    public function definition(): array
    {
        $tipos_restaurante = [
            'comida-tradicional', 'parrilla', 'comida-rapida', 'italiana', 
            'china', 'internacional', 'postres', 'bebidas'
        ];

        $nombres_restaurante = [
            'El Rincón del Sabor', 'La Casona', 'Sabor Boliviano', 'Los Amigos', 'La Estancia',
            'El Buen Gusto', 'La Terraza', 'Parrilla del Chef', 'El Gauchito', 'La Vaca Loca',
            'El Fogón', 'Rápido y Sabroso', 'Burger Mania', 'Pizza Veloz', 'Taco Express',
            'La Nonna', 'Bella Italia', 'Pasta Fresca', 'Trattoria del Ponte', 'Don Corleone',
            'Dragón Rojo', 'Palacio de Jade', 'Wok In', 'China Town', 'Bambú Garden',
            'Mundo Gourmet', 'Sabores del Mundo', 'Fusión Andina', 'Cosmopolitan', 'El Viajero',
            'Dulce Tentación', 'El Oasis Dulce', 'Pastelería Delicias', 'Heladería Cremosa',
            'Café y Postres', 'La Fuente de Soda', 'Jugos y Más', 'El Barista', 'Refrescos Tropicales'
        ];

        $direcciones_bolivia = [
            'Avenida Arce 2132, La Paz', 'Calle Sagarnaga 345, La Paz', 'Plaza Murillo 100, La Paz',
            'Avenida 6 de Agosto 2543, La Paz', 'Calle Jaén 710, La Paz', 'El Prado 1600, La Paz',
            'Avenida Ballivián 567, Calacoto, La Paz', 'Calle 21 de Calacoto 8290, La Paz',
            'Avenida América 500, Cochabamba', 'Paseo del Prado 234, Cochabamba',
            'Avenida Uyuni 1200, Cochabamba', 'Calle España 310, Cochabamba',
            'Avenida Heroínas 450, Cochabamba', 'Plaza 14 de Septiembre 100, Cochabamba',
            'Avenida Libertador Bolívar 1800, Cochabamba', 'Calle Sucre 500, Cochabamba',
            'Avenida Monseñor Rivero 200, Santa Cruz de la Sierra', 'Equipetrol, Calle 9 Este, Santa Cruz de la Sierra',
            'Avenida San Martín 455, Santa Cruz de la Sierra', 'Tercer Anillo Externo, Avenida Busch, Santa Cruz de la Sierra',
            'Plaza 24 de Septiembre 50, Santa Cruz de la Sierra', 'Avenida Irala 789, Santa Cruz de la Sierra',
            'Barrio Urbarí, Calle 4, Santa Cruz de la Sierra', 'Avenida Velarde 300, Santa Cruz de la Sierra'
        ];

        return [
            'id_usuario' => Usuario::factory()->create(['rol' => 'restaurante']),
            'nombre_restaurante' => fake()->randomElement($nombres_restaurante) . ' ' . fake()->companySuffix(),
            'direccion' => fake()->randomElement($direcciones_bolivia),
            'telefono' => fake()->phoneNumber(),
            'categoria' => fake()->word(),
            'horario_apertura' => '09:00:00',
            'horario_cierre' => '23:00:00',
            'tipo_restaurante' => fake()->randomElement($tipos_restaurante),
            'calificacion' => fake()->randomFloat(1, 2.5, 5.0),
        ];
    }
}
