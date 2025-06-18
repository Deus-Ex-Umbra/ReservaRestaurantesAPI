<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\UsuarioCliente;
use App\Models\Usuario;
use App\Models\Preferencia;
use App\Models\UsuarioRestaurante;
use App\Models\Reserva;
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

    public static function buscarRestaurantes(Request $request)
    {
        $validador = Validator::make($request->all(), [
            'nombre_restaurante' => 'nullable|string|max:255',
            'tipo_restaurante' => 'nullable|in:comida-tradicional,parrilla,comida-rapida,italiana,china,internacional,postres,bebidas',
            'calificacion_minima' => 'nullable|numeric|min:0|max:5',
            'direccion' => 'nullable|string|max:255',
            'categoria' => 'nullable|string|max:100',
            'precio_maximo' => 'nullable|numeric|min:0'
        ]);

        if ($validador->fails()) {
            return response()->json(['error' => $validador->errors()], 422);
        }

        $query = UsuarioRestaurante::with(['usuario', 'menus.platos']);

        if ($request->nombre_restaurante) {
            $query->where('nombre_restaurante', 'LIKE', '%' . $request->nombre_restaurante . '%');
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

        if ($request->categoria) {
            $query->where('categoria', 'LIKE', '%' . $request->categoria . '%');
        }

        $restaurantes = $query->orderBy('calificacion', 'desc')->get();

        if ($request->precio_maximo) {
            $restaurantes = $restaurantes->filter(function ($restaurante) use ($request) {
                $precio_promedio = $restaurante->menus->flatMap->platos->avg('precio_plato') ?? 0;
                return $precio_promedio <= $request->precio_maximo;
            });
        }

        foreach ($restaurantes as $restaurante) {
            $restaurante->imagen_base64 = $restaurante->obtenerImagenBase64();
            foreach ($restaurante->menus as $menu) {
                $menu->imagen_base64 = $menu->obtenerImagenBase64();
                foreach ($menu->platos as $plato) {
                    $plato->imagen_base64 = $plato->obtenerImagenBase64();
                }
            }
        }

        return response()->json(['restaurantes' => $restaurantes->values()], 200);
    }

    public static function obtenerTodasReservasPorRestaurante($id_usuario_cliente)
    {
        $cliente = UsuarioCliente::find($id_usuario_cliente);
        if (!$cliente) {
            return response()->json(['error' => 'Usuario cliente no encontrado'], 404);
        }

        $reservas = Reserva::with(['restaurante.usuario', 'mesas', 'platos', 'calificacion'])
            ->where('id_usuario_cliente', $id_usuario_cliente)
            ->orderBy('id_restaurante')
            ->orderBy('fecha_reserva', 'desc')
            ->orderBy('hora_reserva', 'desc')
            ->get();

        foreach ($reservas as $reserva) {
            $reserva->puede_calificar = $reserva->puedeCalificar() && !$reserva->yaFueCalificada();
            if ($reserva->restaurante) {
                $reserva->restaurante->imagen_base64 = $reserva->restaurante->obtenerImagenBase64();
            }
        }

        $reservas_agrupadas = $reservas->groupBy('id_restaurante');

        return response()->json(['reservas_por_restaurante' => $reservas_agrupadas], 200);
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