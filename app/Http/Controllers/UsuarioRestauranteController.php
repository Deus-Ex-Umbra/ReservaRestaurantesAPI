<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\UsuarioRestaurante;
use App\Models\Usuario;
use App\Models\Reserva;
use App\Models\Calificacion;
use App\Http\Controllers\ImagenHelperController;

class UsuarioRestauranteController extends Controller
{
    public static function crearUsuario(Request $request){
        $validador = Validator::make($request->all(), [
            'id_usuario' => 'required|exists:usuarios,id',
            'nombre_restaurante' => 'required|string|max:255',
            'direccion' => 'required|string|max:255',
            'telefono' => 'required|string|max:15',
            'categoria' => 'required|string|max:100',
            'horario_apertura' => 'required|date_format:H:i',
            'horario_cierre' => 'required|date_format:H:i',
            'tipo_restaurante' => 'required|in:comida-tradicional,parrilla,comida-rapida,italiana,china,internacional,postres,bebidas',
            'calificacion' => 'nullable|numeric|min:0|max:5',
            'ruta_imagen_restaurante' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        if ($validador->fails()) {
            return response()->json(['error' => $validador->errors()], 422);
        }

        $usuario_restaurante = UsuarioRestaurante::create($request->except(['ruta_imagen_restaurante']));

        ImagenHelperController::procesarImagenParaModelo(
            $request, 
            $usuario_restaurante, 
            'ruta_imagen_restaurante', 
            'ruta_imagen_restaurante', 
            'imagenes_restaurantes'
        );

        return response()->json(['message' => 'Usuario restaurante creado exitosamente', 'usuario_restaurante' => $usuario_restaurante], 201);
    }

    public static function obtenerUsuarios(Request $request) {
        $usuarios_restaurantes = UsuarioRestaurante::with('usuario')->get();
        foreach ($usuarios_restaurantes as $usuario_restaurante) {
            $usuario_restaurante->imagen_base64 = $usuario_restaurante->obtenerImagenBase64();
        }
        
        return response()->json(['usuarios_restaurantes' => $usuarios_restaurantes], 200);
    }  

    public static function obtenerUsuarioPorId($id) {
        $usuario_restaurante = UsuarioRestaurante::with(['usuario', 'mesas', 'menus.platos', 'calificaciones'])
            ->find($id);
        
        if (!$usuario_restaurante) {
            return response()->json(['error' => 'Usuario restaurante no encontrado'], 404);
        }
        
        $usuario_restaurante->imagen_base64 = $usuario_restaurante->obtenerImagenBase64();
        
        return response()->json(['usuario_restaurante' => $usuario_restaurante], 200);
    }

    public static function buscarReservas(Request $request, $id_restaurante)
    {
        $validador = Validator::make($request->all(), [
            'estado_reserva' => 'nullable|in:pendiente,aceptada,rechazada,completada,cancelada',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'id_usuario_cliente' => 'nullable|exists:usuarios_clientes,id',
            'personas_reserva' => 'nullable|integer|min:1'
        ]);

        if ($validador->fails()) {
            return response()->json(['error' => $validador->errors()], 422);
        }

        $query = Reserva::with(['usuarioCliente.usuario', 'mesas', 'platos', 'calificacion'])
            ->where('id_restaurante', $id_restaurante);

        if ($request->estado_reserva) {
            $query->where('estado_reserva', $request->estado_reserva);
        }

        if ($request->fecha_inicio) {
            $query->where('fecha_reserva', '>=', $request->fecha_inicio);
        }

        if ($request->fecha_fin) {
            $query->where('fecha_reserva', '<=', $request->fecha_fin);
        }

        if ($request->id_usuario_cliente) {
            $query->where('id_usuario_cliente', $request->id_usuario_cliente);
        }

        if ($request->personas_reserva) {
            $query->where('personas_reserva', '>=', $request->personas_reserva);
        }

        $reservas = $query->orderBy('fecha_reserva', 'desc')
                         ->orderBy('hora_reserva', 'desc')
                         ->get();

        return response()->json(['reservas' => $reservas], 200);
    }

    public static function obtenerCalificacionesPorCliente($id_restaurante)
    {
        $calificaciones = Calificacion::with(['usuarioCliente.usuario', 'reserva'])
            ->where('id_restaurante', $id_restaurante)
            ->where('reportada', false)
            ->orderBy('id_usuario_cliente')
            ->orderBy('fecha_calificacion', 'desc')
            ->get();

        $calificaciones_agrupadas = $calificaciones->groupBy('id_usuario_cliente');

        return response()->json(['calificaciones_por_cliente' => $calificaciones_agrupadas], 200);
    }

    public static function editarUsuario(Request $request, $id) {
        $validador = Validator::make($request->all(), [
            'nombre_restaurante' => 'sometimes|required|string|max:255',
            'direccion' => 'sometimes|required|string|max:255',
            'telefono' => 'sometimes|required|string|max:15',
            'categoria' => 'sometimes|required|string|max:100',
            'horario_apertura' => 'sometimes|required|date_format:H:i',
            'horario_cierre' => 'sometimes|required|date_format:H:i',
            'tipo_restaurante' => 'sometimes|required|in:comida-tradicional,parrilla,comida-rapida,italiana,china,internacional,postres,bebidas',
            'calificacion' => 'nullable|numeric|min:0|max:5',
            'ruta_imagen_restaurante' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        if ($validador->fails()) {
            return response()->json(['error' => $validador->errors()], 422);
        }   
        
        $usuario_restaurante = UsuarioRestaurante::find($id);
        if (!$usuario_restaurante) {
            return response()->json(['error' => 'Usuario restaurante no encontrado'], 404);
        }

        $usuario_restaurante->fill($request->except(['ruta_imagen_restaurante']));
        $usuario_restaurante->save();

        ImagenHelperController::procesarImagenParaModelo(
            $request, 
            $usuario_restaurante, 
            'ruta_imagen_restaurante', 
            'ruta_imagen_restaurante', 
            'imagenes_restaurantes'
        );

        return response()->json(['message' => 'Usuario restaurante actualizado exitosamente', 'usuario_restaurante' => $usuario_restaurante], 200);
    }

    public static function eliminarUsuarioPorId($id)
    {
        $usuario_restaurante = UsuarioRestaurante::find($id);
        if (!$usuario_restaurante) {
            return response()->json(['error' => 'Usuario restaurante no encontrado'], 404);
        }
        
        $id_usuario = $usuario_restaurante->id_usuario;
        
        if ($usuario_restaurante->ruta_imagen_restaurante) {
            ImagenHelperController::eliminarImagen($usuario_restaurante->ruta_imagen_restaurante);
        }
        
        $usuario_restaurante->delete();
        $usuario = Usuario::find($id_usuario);
        if ($usuario) {
            $usuario->delete();
        }
        
        return response()->json(['message' => 'Usuario restaurante eliminado exitosamente'], 200);
    }

    public static function obtenerRestaurantesPorTipo($tipo)
    {
        $restaurantes = UsuarioRestaurante::with('usuario')
            ->where('tipo_restaurante', $tipo)
            ->orderBy('calificacion', 'desc')
            ->get();

        foreach ($restaurantes as $restaurante) {
            $restaurante->imagen_base64 = $restaurante->obtenerImagenBase64();
        }

        return response()->json(['restaurantes' => $restaurantes], 200);
    }

    public static function buscarRestaurantes(Request $request)
    {
        $validador = Validator::make($request->all(), [
            'nombre' => 'nullable|string|max:255',
            'tipo_restaurante' => 'nullable|in:comida-tradicional,parrilla,comida-rapida,italiana,china,internacional,postres,bebidas',
            'calificacion_minima' => 'nullable|numeric|min:0|max:5',
            'direccion' => 'nullable|string|max:255'
        ]);

        if ($validador->fails()) {
            return response()->json(['error' => $validador->errors()], 422);
        }

        $query = UsuarioRestaurante::with('usuario');

        if ($request->nombre) {
            $query->where('nombre_restaurante', 'LIKE', '%' . $request->nombre . '%');
        }

        if ($request->tipo_restaurante) {
            $query->where('tipo_restaurante', $request->tipo_restaurante);
        }

        if ($request->calificacion_minima) {
            $query->where('calificacion', '>=', $request->calificacion_minima);
        }

        if ($request->direccion) {
            $query->where('direccion', 'LIKE', '%' . $request->direccion . '%');
        }

        $restaurantes = $query->orderBy('calificacion', 'desc')->get();

        foreach ($restaurantes as $restaurante) {
            $restaurante->imagen_base64 = $restaurante->obtenerImagenBase64();
        }

        return response()->json(['restaurantes' => $restaurantes], 200);
    }
}