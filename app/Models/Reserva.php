<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\UsuarioCliente;
use App\Models\UsuarioRestaurante;
use App\Models\Mesa;
use App\Models\Calificacion;
use Carbon\Carbon;

class Reserva extends Model
{
    use HasFactory;

    protected $table = 'reservas';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario_cliente',
        'id_restaurante',
        'id_mesa',
        'fecha_reserva',
        'hora_reserva',
        'precio_reserva',
        'comentarios_reserva',
        'estado_reserva',
        'ruta_imagen_comprobante_reserva',
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

    public function mesa()
    {
        return $this->belongsTo(Mesa::class, 'id_mesa');
    }

    public function calificacion()
    {
        return $this->hasOne(Calificacion::class, 'id_reserva');
    }

    public function obtenerComprobanteBase64()
    {
        if (!$this->ruta_imagen_comprobante_reserva) {
            return null;
        }

        $ruta_archivo = storage_path("app/public/{$this->ruta_imagen_comprobante_reserva}");

        if (!file_exists($ruta_archivo)) {
            return null;
        }

        return base64_encode(file_get_contents($ruta_archivo));
    }

    public function puedeCalificar()
    {
        if ($this->estado_reserva !== 'aceptada') {
            return false;
        }

        $fecha_hora_reserva = Carbon::parse($this->fecha_reserva . ' ' . $this->hora_reserva);
        $ahora = Carbon::now();
        
        return $ahora->diffInHours($fecha_hora_reserva) >= 1;
    }

    public function yaFueCalificada()
    {
        return $this->calificacion !== null;
    }

    public function normalizarDatos()
    {
        $estados = [
            'pendiente' => 1,
            'aceptada' => 2,
            'rechazada' => 3,
            'completada' => 4,
            'cancelada' => 5
        ];

        return [
            'id' => $this->id,
            'precio_reserva' => $this->precio_reserva,
            'personas_reserva' => $this->personas_reserva,
            'estado_numerico' => $estados[$this->estado_reserva] ?? 0,
            'fue_calificada' => $this->yaFueCalificada() ? 1 : 0,
            'id_usuario_cliente' => $this->id_usuario_cliente,
            'id_restaurante' => $this->id_restaurante
        ];
    }
}