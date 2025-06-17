<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\UsuarioCliente;
use App\Models\Usuario;
use App\Models\Preferencia;
use App\Http\Controllers\ImagenHelperController;
use App\Http\Controllers\KMeansRecomendadorController;

class UsuarioClienteController extends Controller
{
    public static function crearUsuario(Request $request) {
        $validador = Validator::make($request->all(), [
            'id_usuario' => 'required|exists:usuarios,id',
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'telefono' => 'required|string|max:15',
            'ruta_imagen_cliente' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        if ($validador->fails()) {
            return response()->json(['error' => $validador->errors()], 422);
        }

        $usuario_cliente = UsuarioCliente::create($request->except('ruta_imagen_cliente'));

        ImagenHelperController::procesarImagenParaModelo(
            $request, 
            $usuario_cliente, 
            'ruta_imagen_cliente', 
            'ruta_imagen_cliente', 
            'imagenes_clientes'
        );

        return response()->json(['message' => 'Usuario cliente creado exitosamente', 'usuario_cliente' => $usuario_cliente], 201);
    }

    public static function obtenerUsuarios(Request $request) {
        $usuarios_clientes = UsuarioCliente::with('usuario')->get();

        foreach ($usuarios_clientes as $usuario_cliente) {
            $usuario_cliente->imagen_base64 = $usuario_cliente->obtenerImagenBase64();
        }

        return response()->json(['usuarios_clientes' => $usuarios_clientes], 200);
    }
    
    public static function obtenerUsuarioPorId($id) {
        $usuario_cliente = UsuarioCliente::with(['usuario', 'preferencias', 'reservas.restaurante'])
            ->find($id);
        
        if (!$usuario_cliente) {
            return response()->json(['error' => 'Usuario cliente no encontrado'], 404);
        }

        $usuario_cliente->imagen_base64 = $usuario_cliente->obtenerImagenBase64();
        
        return response()->json(['usuario_cliente' => $usuario_cliente], 200);
    }

    public static function editarUsuario(Request $request, $id) {
        $validador = Validator::make($request->all(), [
            'nombres' => 'sometimes|required|string|max:255',
            'apellidos' => 'sometimes|required|string|max:255',
            'telefono' => 'sometimes|required|string|max:15',
            'ruta_imagen_cliente' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        if ($validador->fails()) {
            return response()->json(['error' => $validador->errors()], 422);
        }

        $usuario_cliente = UsuarioCliente::find($id);
        if (!$usuario_cliente) {
            return response()->json(['error' => 'Usuario cliente no encontrado'], 404);
        }

        $usuario_cliente->fill($request->except('ruta_imagen_cliente'));
        $usuario_cliente->save();

        ImagenHelperController::procesarImagenParaModelo(
            $request, 
            $usuario_cliente, 
            'ruta_imagen_cliente', 
            'ruta_imagen_cliente', 
            'imagenes_clientes'
        );

        return response()->json(['message' => 'Usuario cliente actualizado exitosamente', 'usuario_cliente' => $usuario_cliente], 200);
    }

    public static function eliminarUsuarioPorId($id) {
        $usuario_cliente = UsuarioCliente::find($id);
        if (!$usuario_cliente) {
            return response()->json(['error' => 'Usuario cliente no encontrado'], 404);
        }
        
        Preferencia::where('id_usuario_cliente', $id)->delete(); 
        $id_usuario = $usuario_cliente->id_usuario;
        
        if ($usuario_cliente->ruta_imagen_cliente) {
            ImagenHelperController::eliminarImagen($usuario_cliente->ruta_imagen_cliente);
        }
        
        $usuario_cliente->delete();
        $usuario = Usuario::find($id_usuario);
        if ($usuario) {
            $usuario->delete();
        }
        
        return response()->json(['message' => 'Usuario cliente eliminado exitosamente'], 200);
    }

    public static function obtenerRecomendaciones($id_usuario_cliente)
    {
        try {
            $resultado_recomendaciones = KMeansRecomendadorController::obtenerRecomendacionesParaCliente($id_usuario_cliente);
            
            if (isset($resultado_recomendaciones['error'])) {
                return response()->json($resultado_recomendaciones, 404);
            }

            return response()->json($resultado_recomendaciones, 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al generar recomendaciones: ' . $e->getMessage()
            ], 500);
        }
    }
}
