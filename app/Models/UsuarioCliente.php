<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Usuario;
use App\Models\Preferencia;
use App\Models\Reserva;
use App\Models\Calificacion;
use App\Models\Reporte;

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

    public function normalizarDatos()
    {
        $preferencia = $this->preferencias;
        $ultima_reserva = $this->obtenerUltimaReserva();

        $tipos_restaurante = [
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
            'id_usuario_cliente' => $this->id,
            'tipo_restaurante_preferencia_numerico' => $preferencia ? 
                ($tipos_restaurante[$preferencia->tipo_restaurante_preferencia] ?? 0) : 0,
            'calificacion_minima_preferencia' => $preferencia ? 
                $preferencia->calificacion_minima_preferencia : 0,
            'precio_promedio_reservas' => $this->reservas()
                ->where('estado_reserva', 'completada')
                ->avg('precio_reserva') ?? 0,
            'frecuencia_reservas' => $this->reservas()
                ->where('estado_reserva', 'completada')
                ->count(),
            'tipo_restaurante_ultima_reserva_numerico' => $ultima_reserva ? 
                ($tipos_restaurante[$ultima_reserva->restaurante->tipo_restaurante] ?? 0) : 0,
            'calificacion_promedio_dada' => $this->calificaciones()->avg('puntuacion') ?? 0
        ];
    }
}