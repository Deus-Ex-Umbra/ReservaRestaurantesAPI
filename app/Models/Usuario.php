<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Models\UsuarioRestaurante;
use App\Models\UsuarioCliente;
use App\Models\UsuarioAdministrador;

class Usuario extends Authenticatable implements JWTSubject {
    use Notifiable;

    public $timestamps = false;

    protected $fillable = [
        'correo',
        'contraseña',
        'rol'
    ];

    protected $hidden = [
        'contraseña',
        'remember_token',
    ];

    public $casts = [
        'contraseña' => 'hashed',
    ];
    
    public function getAuthPasswordName()
    {
        return 'contraseña';
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'correo' => $this->correo,
            'rol' => $this->rol,
        ];
    }

    public function usuarioRestaurante()
    {
        return $this->hasOne(UsuarioRestaurante::class, 'id_usuario');
    }
    
    public function usuarioCliente()
    {
        return $this->hasOne(UsuarioCliente::class, 'id_usuario');
    }

    public function usuarioAdministrador()
    {
        return $this->hasOne(UsuarioAdministrador::class, 'id_usuario');
    }

    public function usuarioAsociado()
    {
        return $this->usuarioRestaurante()->first() ?: 
               ($this->usuarioCliente()->first() ?: 
                $this->usuarioAdministrador()->first());
    }

    public function normalizarDatos()
    {
        $roles_numericos = [
            'administrador' => 1,
            'cliente' => 2,
            'restaurante' => 3
        ];

        return [
            'id' => $this->id,
            'correo' => $this->correo,
            'rol_numerico' => $roles_numericos[$this->rol] ?? 0,
            'tiene_datos_asociados' => $this->usuarioAsociado() ? 1 : 0
        ];
    }

    public static function obtenerEstadisticasUsuarios()
    {
        return [
            'total_usuarios' => self::count(),
            'administradores' => self::where('rol', 'administrador')->count(),
            'clientes' => self::where('rol', 'cliente')->count(),
            'restaurantes' => self::where('rol', 'restaurante')->count(),
            'usuarios_por_mes' => self::selectRaw('MONTH(created_at) as mes, COUNT(*) as cantidad')
                ->groupBy('mes')
                ->get()
        ];
    }
}