<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\UsuarioCliente;
use App\Models\UsuarioRestaurante;
use App\Models\Reserva;
use App\Models\Reporte;

class Calificacion extends Model
{
    use HasFactory;

    protected $table = 'calificaciones';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario_cliente',
        'id_restaurante',
        'id_reserva',
        'puntuacion',
        'comentario',
        'fecha_calificacion',
        'reportada'
    ];

    public function usuarioCliente()
    {
        return $this->belongsTo(UsuarioCliente::class, 'id_usuario_cliente');
    }

    public function restaurante()
    {
        return $this->belongsTo(UsuarioRestaurante::class, 'id_restaurante');
    }

    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'id_reserva');
    }

    public function reportes()
    {
        return $this->hasMany(Reporte::class, 'id_calificacion');
    }

    public function normalizarDatos()
    {
        return [
            'id' => $this->id,
            'puntuacion' => $this->puntuacion,
            'longitud_comentario' => strlen($this->comentario ?? ''),
            'reportada_numerico' => $this->reportada ? 1 : 0,
            'id_usuario_cliente' => $this->id_usuario_cliente,
            'id_restaurante' => $this->id_restaurante
        ];
    }
}