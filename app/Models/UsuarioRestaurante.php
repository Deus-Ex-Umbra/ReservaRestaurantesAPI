<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Usuario;

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

    public function obtenerImagenBase64()
    {
        if (!$this->ruta_imagen_cliente) {
            return null;
        }

        $path = storage_path("app/public/{$this->ruta_imagen_cliente}");

        if (!file_exists($path)) {
            return null;
        }

        return base64_encode(file_get_contents($path));
    }
}
