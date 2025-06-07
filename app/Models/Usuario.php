<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

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
}