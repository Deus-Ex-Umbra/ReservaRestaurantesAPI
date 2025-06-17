<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Usuario;
use App\Models\Mesa;
use App\Models\Menu;
use App\Models\Reserva;
use App\Models\Calificacion;

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
        'ruta_qr_pago',
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

    public function obtenerQrPagoBase64()
    {
        if (!$this->ruta_qr_pago) {
            return null;
        }

        $ruta_archivo = storage_path("app/public/{$this->ruta_qr_pago}");

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

    public function normalizarDatos()
    {
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
            'id' => $this->id,
            'tipo_restaurante_numerico' => $tipos_restaurante[$this->tipo_restaurante] ?? 0,
            'calificacion' => $this->calificacion,
            'precio_promedio_mesas' => $this->mesas()->avg('precio_mesa') ?? 0,
            'cantidad_mesas' => $this->mesas()->count(),
            'cantidad_calificaciones' => $this->calificaciones()->count()
        ];
    }
}