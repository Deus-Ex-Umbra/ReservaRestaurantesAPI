<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsuarioRestaurante extends Model
{
    use HasFactory;

    protected $table = 'usuarios_restaurantes';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'nombre_restaurante',
        'direccion',
        'telefono',
        'categoria',
        'horario_apertura',
        'horario_cierre',
        'tipo_restaurante',
        'calificacion',
        'ruta_imagen_restaurante',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function mesas()
    {
        return $this->hasMany(Mesa::class, 'id_restaurante');
    }

    public function menus() 
    {
        return $this->hasMany(Menu::class, 'id_restaurante');
    }

    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'id_restaurante');
    }

    public function calificaciones()
    {
        return $this->hasMany(Calificacion::class, 'id_restaurante');
    }

    public function obtenerImagenBase64()
    {
        if (!$this->ruta_imagen_restaurante) {
            return null;
        }

        $ruta_archivo = storage_path("app/public/{$this->ruta_imagen_restaurante}");

        if (!file_exists($ruta_archivo)) {
            return null;
        }

        return base64_encode(file_get_contents($ruta_archivo));
    }

    public function calcularPromedioCalificacion()
    {
        $promedio = $this->calificaciones()->avg('puntuacion');
        return $promedio ? round($promedio, 1) : 0.0;
    }
}
