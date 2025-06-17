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
        if (!$this->ruta_imagen_administrador) {
            return null;
        }

        $ruta_archivo = storage_path("app/public/{$this->ruta_imagen_administrador}");

        if (!file_exists($ruta_archivo)) {
            return null;
        }

        return base64_encode(file_get_contents($ruta_archivo));
    }

    public function normalizarDatos()
    {
        return [
            'id' => $this->id,
            'nombres' => $this->nombres,
            'apellidos' => $this->apellidos,
            'telefono' => $this->telefono,
            'correo' => $this->usuario->correo ?? '',
            'rol_numerico' => 1
        ];
    }
}