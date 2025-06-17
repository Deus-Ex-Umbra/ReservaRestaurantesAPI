<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsuarioCliente extends Model
{
    use HasFactory;

    protected $table = 'usuarios_clientes';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'nombres',
        'apellidos',
        'telefono',
        'ruta_imagen_cliente'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function preferencias()
    {
        return $this->hasOne(Preferencia::class, 'id_usuario_cliente');
    }

    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'id_usuario_cliente');
    }

    public function calificaciones()
    {
        return $this->hasMany(Calificacion::class, 'id_usuario_cliente');
    }

    public function reportes()
    {
        return $this->hasMany(Reporte::class, 'id_usuario_cliente');
    }

    public function obtenerImagenBase64()
    {
        if (!$this->ruta_imagen_cliente) {
            return null;
        }

        $ruta_archivo = storage_path("app/public/{$this->ruta_imagen_cliente}");

        if (!file_exists($ruta_archivo)) {
            return null;
        }

        return base64_encode(file_get_contents($ruta_archivo));
    }

    public function obtenerUltimaReserva()
    {
        return $this->reservas()
            ->where('estado_reserva', 'completada')
            ->orderBy('fecha_reserva', 'desc')
            ->orderBy('hora_reserva', 'desc')
            ->first();
    }
}
