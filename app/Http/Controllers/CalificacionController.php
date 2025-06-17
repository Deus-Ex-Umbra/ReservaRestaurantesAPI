<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Calificacion;
use App\Models\Reserva;
use App\Models\UsuarioRestaurante;
use Carbon\Carbon;

class CalificacionController extends Controller
{
    public static function crearCalificacion(Request $request)
    {
        $validador = Validator::make($request->all(), [
            'id_usuario_cliente' => 'required|exists:usuarios_clientes,id',
            'id_restaurante' => 'required|exists:usuarios_restaurantes,id',
            'id_reserva' => 'required|exists:reservas,id',
            'puntuacion' => 'required|numeric|min:1|max:5',
            'comentario' => 'nullable|string|max:1000'
        ]);

        if ($validador->fails()) {
            return response()->json(['error' => $validador->errors()], 422);
        }

        $reserva = Reserva::find($request->id_reserva);
        
        if ($reserva->id_usuario_cliente != $request->id_usuario_cliente) {
            return response()->json(['error' => 'No puedes calificar una reserva que no es tuya'], 403);
        }

        if (!$reserva->puedeCalificar()) {
            return response()->json(['error' => 'No puedes calificar hasta 1 hora después de la reserva'], 403);
        }

        if ($reserva->yaFueCalificada()) {
            return response()->json(['error' => 'Esta reserva ya fue calificada'], 400);
        }

        $calificacion = Calificacion::create([
            'id_usuario_cliente' => $request->id_usuario_cliente,
            'id_restaurante' => $request->id_restaurante,
            'id_reserva' => $request->id_reserva,
            'puntuacion' => $request->puntuacion,
            'comentario' => $request->comentario,
            'fecha_calificacion' => Carbon::now()->toDateString()
        ]);

        self::actualizarCalificacionRestaurante($request->id_restaurante);

        return response()->json([
            'message' => 'Calificación creada exitosamente', 
            'calificacion' => $calificacion
        ], 201);
    }

    public static function obtenerCalificacionesPorRestaurante($id_restaurante)
    {
        $calificaciones = Calificacion::with(['usuarioCliente.usuario'])
            ->where('id_restaurante', $id_restaurante)
            ->where('reportada', false)
            ->orderBy('fecha_calificacion', 'desc')
            ->get();

        return response()->json(['calificaciones' => $calificaciones], 200);
    }

    public static function obtenerCalificacionesPorCliente($id_usuario_cliente)
    {
        $calificaciones = Calificacion::with(['restaurante', 'reserva'])
            ->where('id_usuario_cliente', $id_usuario_cliente)
            ->orderBy('fecha_calificacion', 'desc')
            ->get();

        return response()->json(['calificaciones' => $calificaciones], 200);
    }

    public static function editarCalificacion(Request $request, $id)
    {
        $validador = Validator::make($request->all(), [
            'puntuacion' => 'sometimes|required|numeric|min:1|max:5',
            'comentario' => 'sometimes|nullable|string|max:1000'
        ]);

        if ($validador->fails()) {
            return response()->json(['error' => $validador->errors()], 422);
        }

        $calificacion = Calificacion::find($id);
        if (!$calificacion) {
            return response()->json(['error' => 'Calificación no encontrada'], 404);
        }

        $usuario_autenticado = auth('api')->user();
        if ($calificacion->usuarioCliente->id_usuario != $usuario_autenticado->id) {
            return response()->json(['error' => 'No puedes editar una calificación que no es tuya'], 403);
        }

        $calificacion->update($request->only(['puntuacion', 'comentario']));
        self::actualizarCalificacionRestaurante($calificacion->id_restaurante);

        return response()->json([
            'message' => 'Calificación actualizada exitosamente', 
            'calificacion' => $calificacion
        ], 200);
    }

    public static function eliminarCalificacion($id)
    {
        $calificacion = Calificacion::find($id);
        if (!$calificacion) {
            return response()->json(['error' => 'Calificación no encontrada'], 404);
        }

        $usuario_autenticado = auth('api')->user();
        
        if ($usuario_autenticado->rol === 'cliente' && 
            $calificacion->usuarioCliente->id_usuario != $usuario_autenticado->id) {
            return response()->json(['error' => 'No puedes eliminar una calificación que no es tuya'], 403);
        }

        $id_restaurante = $calificacion->id_restaurante;
        $calificacion->delete();
        
        self::actualizarCalificacionRestaurante($id_restaurante);

        return response()->json(['message' => 'Calificación eliminada exitosamente'], 200);
    }

    private static function actualizarCalificacionRestaurante($id_restaurante)
    {
        $restaurante = UsuarioRestaurante::find($id_restaurante);
        if ($restaurante) {
            $nueva_calificacion = $restaurante->calcularPromedioCalificacion();
            $restaurante->calificacion = $nueva_calificacion;
            $restaurante->save();
        }
    }

    public static function marcarComoReportada($id)
    {
        $calificacion = Calificacion::find($id);
        if (!$calificacion) {
            return response()->json(['error' => 'Calificación no encontrada'], 404);
        }

        $calificacion->reportada = true;
        $calificacion->save();

        return response()->json([
            'message' => 'Calificación marcada como reportada', 
            'calificacion' => $calificacion
        ], 200);
    }
}