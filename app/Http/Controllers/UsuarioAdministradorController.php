<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UsuarioAdministrado;
use App\Models\UsuarioCliente;
use App\Models\UsuarioRestaurante;
use App\Models\Usuario;
use Illuminate\Support\Facades\Validator;

class UsuarioAdministradorController extends Controller
{
    public static function obtenerUsuarios(Request $request)
    {
        $usuarios_administradores = self::obtenerUsuariosPorRol('administrador');
        $usuarios_clientes = self::obtenerUsuariosPorRol('cliente');
        $usuarios_restaurantes = self::obtenerUsuariosPorRol('restaurante');
        return response()->json([
            'usuarios_administradores' => $usuarios_administradores,
            'usuarios_clientes' => $usuarios_clientes,
            'usuarios_restaurantes' => $usuarios_restaurantes
        ], 200);
    }

    public static function obtenerUsuariosSegunRol(Request $request, $rol)
    {
        $usuarios = self::obtenerUsuariosPorRol($rol);
        return response()->json(['usuarios' => $usuarios], 200);
    }

    public static function obtenerDatosParaKMeans(Request $request)
    {
        
    }

    protected static function obtenerUsuariosPorRol($rol)
    {
        switch ($rol) {
            case 'administrador':
                return UsuarioAdministrado::with('usuario')->get();
            case 'cliente':
                return UsuarioCliente::with('usuario')->get();
            case 'restaurante':
                return UsuarioRestaurante::with('usuario')->get();
            default:
                return response()->json(['error' => 'Rol no válido'], 400);
        }
    }

    public static function obtenerUsuarioPorId($id)
    {
        $usuario = Usuario::find($id);
        if (!$usuario) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        switch ($usuario->rol) {
            case 'administrador':
                $usuario_detalle = UsuarioAdministrado::with('usuario')->find($id);
                break;
            case 'cliente':
                $usuario_detalle = UsuarioCliente::with('usuario')->find($id);
                break;
            case 'restaurante':
                $usuario_detalle = UsuarioRestaurante::with('usuario')->find($id);
                break;
            default:
                return response()->json(['error' => 'Rol no válido'], 400);
        }

        return response()->json(['usuario' => $usuario_detalle], 200);
    }

    public static function crearUsuarioAdministrador(Request $request) {
        $validador = Validator::make($request->all(), [
            'id_usuario' => 'required|exists:usuarios,id',
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'telefono' => 'required|string|max:15',
            'ruta_imagen_administrador' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        if ($validador->fails()) {
            return response()->json(['error' => $validador->errors()], 422);
        }

        $usuario_administrador = UsuarioAdministrado::create($request->except('ruta_imagen_administrador'));

        if ($request->hasFile('ruta_imagen_administrador')) {
            $ruta_imagen = $request->file('ruta_imagen_administrador')->store('imagenes_administradores', 'public');
            $usuario_administrador->ruta_imagen_administrador = $ruta_imagen;
            $usuario_administrador->save();
        }

        return response()->json(['message' => 'Usuario administrador creado exitosamente', 'usuario_administrador' => $usuario_administrador], 201);
    }

    public static function actualizarUsuariAdministrador(Request $request) {
        $validador = Validator::make($request->all(), [
            'id_usuario' => 'required|exists:usuarios,id',
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'telefono' => 'required|string|max:15',
            'ruta_imagen_administrador' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        if ($validador->fails()) {
            return response()->json(['error' => $validador->errors()], 422);
        }

        $usuario_administrador = UsuarioAdministrado::find($request->id_usuario);
        if (!$usuario_administrador) {
            return response()->json(['error' => 'Usuario administrador no encontrado'], 404);
        }
        
        $usuario_administrador->nombres = $request->nombres;
        $usuario_administrador->apellidos = $request->apellidos;
        $usuario_administrador->telefono = $request->telefono;
        if ($request->hasFile('ruta_imagen_administrador')) {
            $ruta_imagen = $request->file('ruta_imagen_administrador')->store('imagenes_administradores', 'public');
            $usuario_administrador->ruta_imagen_administrador = $ruta_imagen;
        }

        $usuario_administrador->save();

        return response()->json(['message' => 'Usuario administrador actualizado exitosamente', 'usuario_administrador' => $usuario_administrador], 200);
    }

    public static function eliminarUsuarioPorId($id)
    {
        $usuario = Usuario::find($id);
        if (!$usuario) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        switch ($usuario->rol) {
            case 'administrador':
                $usuario_detalle = UsuarioAdministrado::find($id);
                break;
            case 'cliente':
                $usuario_detalle = UsuarioCliente::find($id);
                break;
            case 'restaurante':
                $usuario_detalle = UsuarioRestaurante::find($id);
                break;
            default:
                return response()->json(['error' => 'Rol no válido'], 400);
        }

        $usuario_detalle->delete();
        return response()->json(['message' => 'Usuario eliminado exitosamente'], 200);
    }

    public static function normalizarDatos($_usuarios_clientes, $_reservas) {
        // ...
    }
}
