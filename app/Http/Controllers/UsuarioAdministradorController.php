<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UsuarioAdministrador;
use App\Models\UsuarioCliente;
use App\Models\UsuarioRestaurante;
use App\Models\Usuario;
use App\Models\Reserva;
use App\Models\Preferencia;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\UsuarioClienteController;
use App\Http\Controllers\UsuarioRestauranteController;
use App\Http\Controllers\ImagenHelperController;

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
        $usuarios_clientes = UsuarioCliente::with(['preferencias', 'reservas.restaurante'])
            ->whereHas('reservas', function($query) {
                $query->where('estado_reserva', 'completada');
            })
            ->get();

        $csv_data = [];
        $csv_data[] = [
            'id_usuario_cliente',
            'tipo_restaurante_preferencia_numerico',
            'calificacion_minima_preferencia',
            'precio_promedio_reservas',
            'frecuencia_reservas',
            'tipo_restaurante_ultima_reserva_numerico',
            'calificacion_promedio_dada'
        ];

        foreach ($usuarios_clientes as $cliente) {
            $datos_normalizados = $cliente->normalizarDatos();
            $csv_data[] = [
                $datos_normalizados['id_usuario_cliente'],
                $datos_normalizados['tipo_restaurante_preferencia_numerico'],
                $datos_normalizados['calificacion_minima_preferencia'],
                $datos_normalizados['precio_promedio_reservas'],
                $datos_normalizados['frecuencia_reservas'],
                $datos_normalizados['tipo_restaurante_ultima_reserva_numerico'],
                $datos_normalizados['calificacion_promedio_dada']
            ];
        }

        $nombre_archivo = 'datos_kmeans_' . date('Y_m_d_H_i_s') . '.csv';
        $ruta_archivo = storage_path('app/public/' . $nombre_archivo);

        $file = fopen($ruta_archivo, 'w');
        foreach ($csv_data as $row) {
            fputcsv($file, $row);
        }
        fclose($file);

        return response()->download($ruta_archivo)->deleteFileAfterSend(true);
    }

    protected static function obtenerUsuariosPorRol($rol)
    {
        switch ($rol) {
            case 'administrador':
                return UsuarioAdministrador::with('usuario')->get();
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
                $usuario_detalle = UsuarioAdministrador::with('usuario')->find($id);
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

        if ($usuario_detalle && method_exists($usuario_detalle, 'obtenerImagenBase64')) {
            $usuario_detalle->imagen_base64 = $usuario_detalle->obtenerImagenBase64();
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

        $usuario_administrador = UsuarioAdministrador::create($request->except('ruta_imagen_administrador'));

        ImagenHelperController::procesarImagenParaModelo(
            $request, 
            $usuario_administrador, 
            'ruta_imagen_administrador', 
            'ruta_imagen_administrador', 
            'imagenes_administradores'
        );

        return response()->json(['message' => 'Usuario administrador creado exitosamente', 'usuario_administrador' => $usuario_administrador], 201);
    }

    public static function actualizarUsuarioAdministrador(Request $request, $id) {
        $validador = Validator::make($request->all(), [
            'nombres' => 'sometimes|required|string|max:255',
            'apellidos' => 'sometimes|required|string|max:255',
            'telefono' => 'sometimes|required|string|max:15',
            'ruta_imagen_administrador' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        if ($validador->fails()) {
            return response()->json(['error' => $validador->errors()], 422);
        }

        $usuario_administrador = UsuarioAdministrador::find($id);
        if (!$usuario_administrador) {
            return response()->json(['error' => 'Usuario administrador no encontrado'], 404);
        }
        
        $usuario_administrador->fill($request->except('ruta_imagen_administrador'));
        $usuario_administrador->save();

        ImagenHelperController::procesarImagenParaModelo(
            $request, 
            $usuario_administrador, 
            'ruta_imagen_administrador', 
            'ruta_imagen_administrador', 
            'imagenes_administradores'
        );

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
                $usuario_detalle = UsuarioAdministrador::find($id);
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

        if ($usuario_detalle) {
            $usuario_detalle->delete();
        }
        $usuario->delete();
        return response()->json(['message' => 'Usuario eliminado exitosamente'], 200);
    }

    public static function crearUsuarioCliente(Request $request) {
        return UsuarioClienteController::crearUsuario($request);
    }

    public static function suspenderUsuario($id)
    {
        $usuario = Usuario::find($id);
        if (!$usuario) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        if ($usuario->rol === 'administrador') {
            return response()->json(['error' => 'No se puede suspender a un administrador'], 403);
        }

        return response()->json(['message' => 'Funcionalidad de suspensión pendiente de implementación'], 200);
    }
}