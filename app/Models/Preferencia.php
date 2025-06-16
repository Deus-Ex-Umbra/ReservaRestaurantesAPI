<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Preferencia extends Model
{
    use HasFactory;

    protected $table = 'preferencias';
    
    public $timestamps = false;

    protected $fillable = [
        'id_usuario_cliente',
        'tipo_restaurante_preferencia',
        'precio_minimo_preferencia',
        'precio_maximo_preferencia',
        'calificacion_minima_preferencia',
    ];

    public function usuarioCliente()
    {
        return $this->hasOne(UsuarioCliente::class, 'id_usuario_cliente');
    }
}
