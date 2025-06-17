<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Reserva extends Model
{
    use HasFactory;

    protected $table = 'reservas';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario_cliente',
        'id_restaurante',
        'fecha_reserva',
        'hora_reserva',
        'precio_total',
        'precio_reserva',
        'comentarios_reserva',
        'estado_reserva',
        'fecha_creacion_reserva',
        'personas_reserva',
        'telefono_contacto_reserva'
    ];

    public function usuarioCliente()
    {
        return $this->belongsTo(UsuarioCliente::class, 'id_usuario_cliente');
    }

    public function restaurante()
    {
        return $this->belongsTo(UsuarioRestaurante::class, 'id_restaurante');
    }

    public function mesas()
    {
        return $this->belongsToMany(Mesa::class, 'reserva_mesa', 'id_reserva', 'id_mesa');
    }

    public function platos()
    {
        return $this->belongsToMany(Plato::class, 'reserva_plato', 'id_reserva', 'id_plato')->withPivot('cantidad');
    }

    public function calificacion()
    {
        return $this->hasOne(Calificacion::class, 'id_reserva');
    }

    public function puedeCalificar()
    {
        if ($this->estado_reserva !== 'completada') {
            return false;
        }

        $fecha_hora_reserva = Carbon::parse($this->fecha_reserva . ' ' . $this->hora_reserva);
        $ahora = Carbon::now();
        
        return $ahora->greaterThanOrEqualTo($fecha_hora_reserva->addHour());
    }

    public function yaFueCalificada()
    {
        return $this->calificacion !== null;
    }
}
