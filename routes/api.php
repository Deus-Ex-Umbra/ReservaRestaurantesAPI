<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\UsuarioAdministradorController;
use App\Http\Controllers\UsuarioClienteController;
use App\Http\Controllers\UsuarioRestauranteController;
use App\Http\Controllers\PreferenciaController;
use App\Http\Controllers\MesaController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PlatoController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\CalificacionController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\KMeansRecomendadorController;
use App\Http\Middleware\VerificarRol;

Route::group([
    'prefix' => 'autenticacion'
], function ($router) {
    Route::post('registrarse', [UsuarioController::class, 'registrarse']);
    Route::post('iniciar-sesion', [UsuarioController::class, 'iniciarSesion']);
    Route::group(['middleware' => 'auth:api'], function () {
        Route::get('cerrar-sesion', [UsuarioController::class, 'cerrarSesion']);
        Route::get('perfil', [UsuarioController::class, 'obtenerPerfilUsuario']);
    });
});

Route::group([
    'prefix' => 'administrador',
    'middleware' => ['auth:api', VerificarRol::class . ':administrador']], 
    function ($router) {
        Route::get('usuarios', [UsuarioAdministradorController::class, 'obtenerUsuarios']);
        Route::post('crear-usuario', [UsuarioAdministradorController::class, 'crearUsuarioAdministrador']);
        Route::put('actualizar-usuario/{id}', [UsuarioAdministradorController::class, 'actualizarUsuarioAdministrador']);
        Route::get('usuarios/{rol}', [UsuarioAdministradorController::class, 'obtenerUsuariosSegunRol']);
        Route::get('generar-dataset-kmeans', [UsuarioAdministradorController::class, 'generarDatasetKmeans']);
        Route::get('usuario/{id}', [UsuarioAdministradorController::class, 'obtenerUsuarioPorId']);
        Route::delete('eliminar-usuario/{id}', [UsuarioAdministradorController::class, 'eliminarUsuarioPorId']);
        
        Route::group(['prefix' => 'buscar'], function () {
            Route::get('usuarios', [UsuarioAdministradorController::class, 'buscarUsuarios']);
        });
        
        Route::get('todas-reservas', [UsuarioAdministradorController::class, 'obtenerTodasReservas']);
        Route::get('todos-reportes', [UsuarioAdministradorController::class, 'obtenerTodosReportes']);
        Route::get('todas-calificaciones', [UsuarioAdministradorController::class, 'obtenerTodasCalificaciones']);
        
        Route::get('reportes', [ReporteController::class, 'obtenerReportes']);
        Route::get('reportes-pendientes', [ReporteController::class, 'obtenerReportesPendientes']);
        Route::get('reporte/{id}', [ReporteController::class, 'obtenerReportePorId']);
        Route::put('procesar-reporte/{id}', [ReporteController::class, 'procesarReporte']);
});

Route::group([
    'prefix' => 'cliente',
    'middleware' => ['auth:api', VerificarRol::class . ':cliente']], 
    function ($router) {
        Route::post('crear-cliente', [UsuarioClienteController::class, 'crearUsuario']);
        Route::get('{id}', [UsuarioClienteController::class, 'obtenerUsuarioPorId']);
        Route::put('editar/{id}', [UsuarioClienteController::class, 'editarUsuario']);
        
        Route::group(['prefix' => 'buscar'], function () {
            Route::get('restaurantes', [UsuarioClienteController::class, 'buscarRestaurantes']);
        });
        
        Route::get('reservas-por-restaurante/{id_usuario_cliente}', [UsuarioClienteController::class, 'obtenerTodasReservasPorRestaurante']);
        
        Route::post('crear-preferencia', [PreferenciaController::class, 'crearPreferencia']);
        Route::get('preferencia/{id_usuario_cliente}', [PreferenciaController::class, 'obtenerPreferenciaPorUsuarioCliente']);
        Route::put('editar-preferencia/{id}', [PreferenciaController::class, 'editarPreferencia']);
        Route::delete('eliminar-preferencia/{id}', [PreferenciaController::class, 'eliminarPreferenciaPorId']);
        
        Route::get('recomendaciones/{id_usuario_cliente}', [UsuarioClienteController::class, 'obtenerRecomendaciones']);
        
        Route::post('crear-reserva', [ReservaController::class, 'crearReserva']);
        Route::get('reservas/{id_usuario_cliente}', [ReservaController::class, 'obtenerReservasPorCliente']);
        Route::put('cancelar-reserva/{id}', [ReservaController::class, 'cancelarReserva']);
        
        Route::post('crear-calificacion', [CalificacionController::class, 'crearCalificacion']);
        Route::get('calificaciones/{id_usuario_cliente}', [CalificacionController::class, 'obtenerCalificacionesPorCliente']);
        Route::put('editar-calificacion/{id}', [CalificacionController::class, 'editarCalificacion']);
        Route::delete('eliminar-calificacion/{id}', [CalificacionController::class, 'eliminarCalificacion']);
        
        Route::post('crear-reporte', [ReporteController::class, 'crearReporte']);
});

Route::group([
    'prefix' => 'restaurante',
    'middleware' => ['auth:api', VerificarRol::class . ':restaurante']], 
    function ($router) {
        Route::post('crear-usuario', [UsuarioRestauranteController::class, 'crearUsuario']);
        Route::get('{id}', [UsuarioRestauranteController::class, 'obtenerUsuarioPorId']);
        Route::put('editar-usuario/{id}', [UsuarioRestauranteController::class, 'editarUsuario']);
        Route::delete('eliminar-usuario/{id}', [UsuarioRestauranteController::class, 'eliminarUsuarioPorId']);
        
        Route::group(['prefix' => 'buscar'], function () {
            Route::get('{id_restaurante}/reservas', [UsuarioRestauranteController::class, 'buscarReservas']);
        });
        
        Route::get('calificaciones-por-cliente/{id_restaurante}', [UsuarioRestauranteController::class, 'obtenerCalificacionesPorCliente']);
        
        Route::get('{id}/mesas', [MesaController::class, 'obtenerMesasPorRestaurante']);
        Route::post('crear-mesa', [MesaController::class, 'crearMesa']);
        Route::get('mesa/{id}', [MesaController::class, 'obtenerMesaPorId']);
        Route::put('editar-mesa/{id}', [MesaController::class, 'editarMesa']);
        Route::delete('eliminar-mesa/{id}', [MesaController::class, 'eliminarMesa']);
        Route::put('cambiar-estado-mesa/{id}', [MesaController::class, 'cambiarEstadoMesa']);
        
        Route::get('{id}/menus', [MenuController::class, 'obtenerMenusPorRestaurante']);
        Route::post('crear-menu', [MenuController::class, 'crearMenu']);
        Route::get('menu/{id}', [MenuController::class, 'obtenerMenuPorId']);
        Route::put('editar-menu/{id}', [MenuController::class, 'editarMenu']);
        Route::delete('eliminar-menu/{id}', [MenuController::class, 'eliminarMenu']);
        
        Route::get('menu/{id_menu}/platos', [PlatoController::class, 'obtenerPlatosPorMenu']);
        Route::post('crear-plato', [PlatoController::class, 'crearPlato']);
        Route::get('plato/{id}', [PlatoController::class, 'obtenerPlatoPorId']);
        Route::put('editar-plato/{id}', [PlatoController::class, 'editarPlato']);
        Route::delete('eliminar-plato/{id}', [PlatoController::class, 'eliminarPlato']);
        Route::put('cambiar-disponibilidad-plato/{id}', [PlatoController::class, 'cambiarDisponibilidad']);
        
        Route::get('reservas/{id_restaurante}', [ReservaController::class, 'obtenerReservasPorRestaurante']);
        Route::get('reservas-por-fecha/{id_restaurante}', [ReservaController::class, 'obtenerReservasPorFecha']);
        Route::put('procesar-reserva/{id}', [ReservaController::class, 'procesarReserva']);
        Route::put('completar-reserva/{id}', [ReservaController::class, 'completarReserva']);
        
        Route::get('calificaciones/{id_restaurante}', [CalificacionController::class, 'obtenerCalificacionesPorRestaurante']);
        
        Route::post('crear-reporte', [ReporteController::class, 'crearReporte']);
});

Route::group(['prefix' => 'publico'], function () {
    Route::get('restaurantes', [UsuarioRestauranteController::class, 'obtenerUsuarios']);
    Route::get('restaurante/{id}', [UsuarioRestauranteController::class, 'obtenerUsuarioPorId']);
    Route::get('restaurantes/tipo/{tipo}', [UsuarioRestauranteController::class, 'obtenerRestaurantesPorTipo']);
    Route::get('buscar/restaurantes', [UsuarioRestauranteController::class, 'buscarRestaurantes']);
    Route::get('restaurante/{id_restaurante}/mesas-disponibles', [MesaController::class, 'obtenerMesasDisponibles']);
    Route::get('tipos-restaurante', [PreferenciaController::class, 'obtenerTiposRestaurante']);
    Route::get('tipos-menu', [MenuController::class, 'obtenerTiposMenu']);
    Route::get('menus/tipo/{tipo}', [MenuController::class, 'obtenerMenusPorTipo']);
});