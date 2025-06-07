<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\UsuarioController;

Route::group([
    'prefix' => 'autenticacion'
], function ($router) {
    Route::post('registrarse', [UsuarioController::class, 'registrarse']);
    Route::post('iniciar-sesion', [UsuarioController::class, 'iniciarSesion']);
    Route::group(['middleware' => 'auth:api'], function () {
        Route::get('cerrar-sesion', [UsuarioController::class, 'cerrarSesion']);
        Route::get('usuario', [UsuarioController::class, 'obtenerUsuarioAutenticado']);
    });
});