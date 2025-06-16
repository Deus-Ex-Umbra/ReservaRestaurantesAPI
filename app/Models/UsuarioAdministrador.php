<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Usuario;

class UsuarioAdministrador extends Model
{
    use HasFactory;

    protected $table = 'usuarios_administradores';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'nombres',
        'apellidos',
        'telefono',
        'ruta_imagen_administrador',
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
