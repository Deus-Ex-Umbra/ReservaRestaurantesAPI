<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\UsuarioRestaurante;
use App\Models\Usuario;
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
            'ruta_imagen_restaurante' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'ruta_qr_pago' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        if ($validador->fails()) {
            return response()->json(['error' => $validador->errors()], 422);
        }

        $usuario_restaurante = UsuarioRestaurante::create($request->except(['ruta_imagen_restaurante', 'ruta_qr_pago']));

        ImagenHelperController::procesarImagenParaModelo(
            $request, 
            $usuario_restaurante, 
            'ruta_imagen_restaurante', 
            'ruta_imagen_restaurante', 
            'imagenes_restaurantes'
        );

        ImagenHelperController::procesarImagenParaModelo(
            $request, 
            $usuario_restaurante, 
            'ruta_qr_pago', 
            'ruta_qr_pago', 
            'qr_pagos'
        );

        return response()->json(['message' => 'Usuario restaurante creado exitosamente', 'usuario_restaurante' => $usuario_restaurante], 201);
    }

    public static function obtenerUsuarios(Request $request) {
        $usuarios_restaurantes = UsuarioRestaurante::with('usuario')->get();
        foreach ($usuarios_restaurantes as $usuario_restaurante) {
            $usuario_restaurante->imagen_base64 = $usuario_restaurante->obtenerImagenBase64();
            $usuario_restaurante->qr_pago_base64 = $usuario_restaurante->obtenerQrPagoBase64();
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
        $usuario_restaurante->qr_pago_base64 = $usuario_restaurante->obtenerQrPagoBase64();
        $usuario_restaurante->datos_normalizados = $usuario_restaurante->normalizarDatos();
        
        return response()->json(['usuario_restaurante' => $usuario_restaurante], 200);
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
            'ruta_imagen_restaurante' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'ruta_qr_pago' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        if ($validador->fails()) {
            return response()->json(['error' => $validador->errors()], 422);
        }   
        
        $usuario_restaurante = UsuarioRestaurante::find($id);
        if (!$usuario_restaurante) {
            return response()->json(['error' => 'Usuario restaurante no encontrado'], 404);
        }

        $usuario_restaurante->fill($request->except(['ruta_imagen_restaurante', 'ruta_qr_pago']));
        $usuario_restaurante->save();

        ImagenHelperController::procesarImagenParaModelo(
            $request, 
            $usuario_restaurante, 
            'ruta_imagen_restaurante', 
            'ruta_imagen_restaurante', 
            'imagenes_restaurantes'
        );

        ImagenHelperController::procesarImagenParaModelo(
            $request, 
            $usuario_restaurante, 
            'ruta_qr_pago', 
            'ruta_qr_pago', 
            'qr_pagos'
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
        
        if ($usuario_restaurante->ruta_qr_pago) {
            ImagenHelperController::eliminarImagen($usuario_restaurante->ruta_qr_pago);
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
            $restaurante->qr_pago_base64 = $restaurante->obtenerQrPagoBase64();
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
            $restaurante->qr_pago_base64 = $restaurante->obtenerQrPagoBase64();
        }

        return response()->json(['restaurantes' => $restaurantes], 200);
    }
}