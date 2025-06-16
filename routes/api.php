<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\UsuarioAdministradorController;
use App\Http\Controllers\UsuarioClienteController;
use App\Http\Controllers\UsuarioRestauranteController;
use App\Http\Controllers\PreferenciaController;
use App\Http\Middleware\VerificarRol;

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

Route::group([
    'prefix' => 'administrador',
    'middleware' => ['auth:api', VerificarRol::class . ':usuario,administrador']], 
    function ($router) {
        Route::get('usuarios', [UsuarioAdministradorController::class, 'obtenerUsuarios']);
        Route::post('crear-usuario', [UsuarioAdministradorController::class, 'crearUsuarioAdministrador']);
        Route::get('usuarios/{rol}', [UsuarioAdministradorController::class, 'obtenerUsuariosSegunRol']);
        Route::get('datos-para-kmeans', [UsuarioAdministradorController::class, 'obtenerDatosParaKMeans']);
        Route::get('{id}', [UsuarioAdministradorController::class, 'obtenerUsuarioPorId']);
        Route::delete('eliminar-usuario/{id}', [UsuarioAdministradorController::class, 'eliminarUsuarioPorId']);
});

Route::group([
    'prefix' => 'cliente',
    'middleware' => ['auth:api', VerificarRol::class . ':usuario,cliente']], function ($router) {
        Route::post('crear-cliente', [UsuarioClienteController::class, 'crearUsuario']);
        Route::get('{id}', [UsuarioClienteController::class, 'obtenerUsuarioPorId']);
        Route::post('crear-preferencia', [PreferenciaController::class, 'crearPreferencia']);
});


Route::group([
    'prefix' => 'restaurante',
    'middleware' => ['auth:api', VerificarRol::class . ':usuario,restaurante']], function ($router) {
});