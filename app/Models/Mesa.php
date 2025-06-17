<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mesa extends Model
{
    use HasFactory;
    protected $table = 'mesas';
    
    public $timestamps = false;

    protected $fillable = [
        'id_restaurante',
        'numero_mesa',
        'capacidad_mesa',
        'numero_personas_mesa',
        'estado_mesa',
        'precio_mesa',
        'ruta_imagen_mesa'
    ];

    public function restaurante()
    {
        return $this->belongsTo(UsuarioRestaurante::class, 'id_restaurante');
    }

    public function reservas()
    {
        return $this->belongsToMany(Reserva::class, 'reserva_mesa', 'id_mesa', 'id_reserva');
    }

    public function estaDisponible($fecha, $hora)
    {
        if ($this->estado_mesa !== 'disponible') {
            return false;
        }

        $reserva_existente = $this->reservas()
            ->where('fecha_reserva', $fecha)
            ->where('hora_reserva', $hora)
            ->whereIn('estado_reserva', ['pendiente', 'aceptada'])
            ->exists();

        return !$reserva_existente;
    }

    public function obtenerImagenBase64()
    {
        if (!$this->ruta_imagen_mesa) {
            return null;
        }

        $ruta_archivo = storage_path("app/public/{$this->ruta_imagen_mesa}");

        if (!file_exists($ruta_archivo)) {
            return null;
        }

        return base64_encode(file_get_contents($ruta_archivo));
    }
}
