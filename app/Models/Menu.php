<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\UsuarioRestaurante;
use App\Models\Plato;

class Menu extends Model
{
    use HasFactory;

    protected $table = 'menus';
    
    public $timestamps = false;

    protected $fillable = [
        'id_restaurante',
        'nombre_menu',
        'descripcion_menu',
        'tipo_menu',
        'ruta_imagen_menu'
    ];

    public function restaurante() 
    {
        return $this->belongsTo(UsuarioRestaurante::class, 'id_restaurante');
    }

    public function platos()
    {
        return $this->hasMany(Plato::class, 'id_menu');
    }

    public function obtenerImagenBase64()
    {
        if (!$this->ruta_imagen_menu) {
            return null;
        }

        $ruta_archivo = storage_path("app/public/{$this->ruta_imagen_menu}");

        if (!file_exists($ruta_archivo)) {
            return null;
        }

        return base64_encode(file_get_contents($ruta_archivo));
    }

    public function calcularPrecioPromedio()
    {
        return $this->platos()->avg('precio_plato') ?? 0;
    }

    public function normalizarDatos()
    {
        $tipos_menu = [
            'comida-tradicional' => 1,
            'parrilla' => 2,
            'comida-rapida' => 3,
            'italiana' => 4,
            'china' => 5,
            'internacional' => 6,
            'postres' => 7,
            'bebidas' => 8
        ];

        return [
            'id' => $this->id,
            'tipo_menu_numerico' => $tipos_menu[$this->tipo_menu] ?? 0,
            'precio_promedio_platos' => $this->calcularPrecioPromedio(),
            'cantidad_platos' => $this->platos()->count(),
            'id_restaurante' => $this->id_restaurante
        ];
    }
}