<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Menu;

class Plato extends Model
{
    use HasFactory;

    protected $table = 'platos';

    public $timestamps = false;

    protected $fillable = [
        'id_menu',
        'nombre_plato',
        'descripcion_plato',
        'precio_plato',
        'ruta_imagen_plato',
        'disponible'
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'id_menu');
    }

    public function reservas()
    {
        return $this->belongsToMany(Reserva::class, 'reserva_plato', 'id_plato', 'id_reserva')->withPivot('cantidad');
    }

    public function obtenerImagenBase64()
    {
        if (!$this->ruta_imagen_plato) {
            return null;
        }

        $ruta_archivo = storage_path("app/public/{$this->ruta_imagen_plato}");

        if (!file_exists($ruta_archivo)) {
            return null;
        }

        return base64_encode(file_get_contents($ruta_archivo));
    }

    public function normalizarDatos()
    {
        return [
            'id' => $this->id,
            'precio_plato' => $this->precio_plato,
            'disponible_numerico' => $this->disponible ? 1 : 0,
            'id_menu' => $this->id_menu
        ];
    }
}
