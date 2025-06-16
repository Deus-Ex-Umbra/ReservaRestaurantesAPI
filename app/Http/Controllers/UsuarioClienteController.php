<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

        if ($request->hasFile('ruta_imagen_cliente')) {
            $ruta_imagen = $request->file('ruta_imagen_cliente')->store('imagenes_clientes', 'public');
            $usuario_cliente->ruta_imagen_cliente = $ruta_imagen;
            $usuario_cliente->save();
        }

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
        $usuario_cliente = UsuarioCliente::with('usuario')->find($id);
        if (!$usuario_cliente) {
            return response()->json(['error' => 'Usuario cliente no encontrado'], 404);
        }

        $usuario_cliente->imagen_base64 = $usuario_cliente->obtenerImagenBase64();
        return response()->json(['usuario_cliente' => $usuario_cliente], 200);
    }
}
