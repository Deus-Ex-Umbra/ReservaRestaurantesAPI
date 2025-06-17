<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Mesa;
use App\Models\UsuarioRestaurante;
use App\Http\Controllers\ImagenHelperController;

class MesaController extends Controller
{
    public function obtenerMesasPorRestaurante(Request $request, $id_restaurante) {
        $usuario_restaurante = UsuarioRestaurante::find($id_restaurante);
        if (!$usuario_restaurante) {
            return response()->json(['error' => 'Restaurante no encontrado'], 404);
        }
        
        $mesas = Mesa::where('id_restaurante', $id_restaurante)->get();
        foreach ($mesas as $mesa) {
            $mesa->imagen_base64 = ImagenHelperController::obtenerImagenBase64($mesa->ruta_imagen_mesa);
        }
        
        return response()->json(['mesas' => $mesas], 200);
    }

    public function crearMesa(Request $request) {
        $validador = Validator::make($request->all(), [
            'id_restaurante' => 'required|exists:usuarios_restaurantes,id',
            'numero_mesa' => 'required|integer|min:1',
            'capacidad_mesa' => 'required|integer|min:1',
            'precio_mesa' => 'required|numeric|min:0',
            'estado_mesa' => 'sometimes|in:disponible,reservada,ocupada',
            'ruta_imagen_mesa' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        if ($validador->fails()) {
            return response()->json(['error' => $validador->errors()], 422);
        }

        $mesa_existente = Mesa::where('id_restaurante', $request->id_restaurante)
            ->where('numero_mesa', $request->numero_mesa)
            ->first();

        if ($mesa_existente) {
            return response()->json(['error' => 'Ya existe una mesa con ese número en el restaurante'], 400);
        }

        $mesa = Mesa::create($request->except('ruta_imagen_mesa'));

        ImagenHelperController::procesarImagenParaModelo(
            $request, 
            $mesa, 
            'ruta_imagen_mesa', 
            'ruta_imagen_mesa', 
            'imagenes_mesas'
        );

        return response()->json(['message' => 'Mesa creada exitosamente', 'mesa' => $mesa], 201);
    }
    
    public function editarMesa(Request $request, $id) {
        $validador = Validator::make($request->all(), [
            'numero_mesa' => 'sometimes|required|integer|min:1',
            'capacidad_mesa' => 'sometimes|required|integer|min:1',
            'precio_mesa' => 'sometimes|required|numeric|min:0',
            'estado_mesa' => 'sometimes|required|in:disponible,reservada,ocupada',
            'ruta_imagen_mesa' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);
        
        if ($validador->fails()) {
            return response()->json(['error' => $validador->errors()], 422);
        }
        
        $mesa = Mesa::find($id);
        if (!$mesa) {
            return response()->json(['error' => 'Mesa no encontrada'], 404);
        }

        $usuario_autenticado = auth('api')->user();
        $restaurante = $mesa->restaurante;
        if ($restaurante->id_usuario != $usuario_autenticado->id) {
            return response()->json(['error' => 'No puedes editar mesas que no son de tu restaurante'], 403);
        }

        if ($request->numero_mesa && $request->numero_mesa != $mesa->numero_mesa) {
            $mesa_existente = Mesa::where('id_restaurante', $mesa->id_restaurante)
                ->where('numero_mesa', $request->numero_mesa)
                ->where('id', '!=', $id)
                ->first();

            if ($mesa_existente) {
                return response()->json(['error' => 'Ya existe una mesa con ese número en el restaurante'], 400);
            }
        }
        
        $mesa->fill($request->except('ruta_imagen_mesa'));
        $mesa->save();
        
        ImagenHelperController::procesarImagenParaModelo(
            $request, 
            $mesa, 
            'ruta_imagen_mesa', 
            'ruta_imagen_mesa', 
            'imagenes_mesas'
        );
        
        return response()->json(['message' => 'Mesa actualizada exitosamente', 'mesa' => $mesa], 200);
    }
    
    public function eliminarMesa($id) {
        $mesa = Mesa::find($id);
        if (!$mesa) {
            return response()->json(['error' => 'Mesa no encontrada'], 404);
        }

        $usuario_autenticado = auth('api')->user();
        $restaurante = $mesa->restaurante;
        if ($restaurante->id_usuario != $usuario_autenticado->id) {
            return response()->json(['error' => 'No puedes eliminar mesas que no son de tu restaurante'], 403);
        }

        if ($mesa->ruta_imagen_mesa) {
            ImagenHelperController::eliminarImagen($mesa->ruta_imagen_mesa);
        }

        $mesa->delete();
        return response()->json(['message' => 'Mesa eliminada exitosamente'], 200);
    }

    public function obtenerMesaPorId($id) {
        $mesa = Mesa::with('restaurante')->find($id);
        if (!$mesa) {
            return response()->json(['error' => 'Mesa no encontrada'], 404);
        }
        
        $mesa->imagen_base64 = ImagenHelperController::obtenerImagenBase64($mesa->ruta_imagen_mesa);
        return response()->json(['mesa' => $mesa], 200);
    }

    public function obtenerMesasDisponibles($id_restaurante, Request $request)
    {
        $validador = Validator::make($request->all(), [
            'fecha' => 'required|date',
            'hora' => 'required|date_format:H:i',
            'personas' => 'required|integer|min:1'
        ]);

        if ($validador->fails()) {
            return response()->json(['error' => $validador->errors()], 422);
        }

        $mesas_ocupadas = \App\Models\Reserva::where('id_restaurante', $id_restaurante)
            ->where('fecha_reserva', $request->fecha)
            ->where('hora_reserva', $request->hora)
            ->whereIn('estado_reserva', ['pendiente', 'aceptada'])
            ->pluck('id_mesa');

        $mesas_disponibles = Mesa::where('id_restaurante', $id_restaurante)
            ->where('capacidad_mesa', '>=', $request->personas)
            ->where('estado_mesa', 'disponible')
            ->whereNotIn('id', $mesas_ocupadas)
            ->get();

        foreach ($mesas_disponibles as $mesa) {
            $mesa->imagen_base64 = ImagenHelperController::obtenerImagenBase64($mesa->ruta_imagen_mesa);
        }

        return response()->json(['mesas_disponibles' => $mesas_disponibles], 200);
    }

    public function cambiarEstadoMesa(Request $request, $id)
    {
        $validador = Validator::make($request->all(), [
            'estado_mesa' => 'required|in:disponible,reservada,ocupada'
        ]);

        if ($validador->fails()) {
            return response()->json(['error' => $validador->errors()], 422);
        }

        $mesa = Mesa::find($id);
        if (!$mesa) {
            return response()->json(['error' => 'Mesa no encontrada'], 404);
        }

        $usuario_autenticado = auth('api')->user();
        $restaurante = $mesa->restaurante;
        if ($restaurante->id_usuario != $usuario_autenticado->id) {
            return response()->json(['error' => 'No puedes cambiar el estado de mesas que no son de tu restaurante'], 403);
        }

        $mesa->estado_mesa = $request->estado_mesa;
        $mesa->save();

        return response()->json(['message' => 'Estado de mesa actualizado exitosamente', 'mesa' => $mesa], 200);
    }
}