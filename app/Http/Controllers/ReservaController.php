<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Reserva;
use App\Models\Mesa;
use App\Models\UsuarioCliente;
use App\Models\UsuarioRestaurante;
use App\Http\Controllers\ImagenHelperController;
use Carbon\Carbon;

class ReservaController extends Controller
{
    public static function crearReserva(Request $request)
    {
        $validador = Validator::make($request->all(), [
            'id_usuario_cliente' => 'required|exists:usuarios_clientes,id',
            'id_restaurante' => 'required|exists:usuarios_restaurantes,id',
            'id_mesa' => 'required|exists:mesas,id',
            'fecha_reserva' => 'required|date|after_or_equal:today',
            'hora_reserva' => 'required|date_format:H:i',
            'personas_reserva' => 'required|integer|min:1',
            'comentarios_reserva' => 'nullable|string|max:500',
            'telefono_contacto_reserva' => 'nullable|string|max:15',
            'ruta_imagen_comprobante_reserva' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        if ($validador->fails()) {
            return response()->json(['error' => $validador->errors()], 422);
        }

        $mesa = Mesa::find($request->id_mesa);
        
        if ($mesa->id_restaurante != $request->id_restaurante) {
            return response()->json(['error' => 'La mesa no pertenece al restaurante especificado'], 400);
        }

        if ($mesa->capacidad_mesa < $request->personas_reserva) {
            return response()->json(['error' => 'La mesa no tiene capacidad suficiente'], 400);
        }

        $reserva_existente = Reserva::where('id_mesa', $request->id_mesa)
            ->where('fecha_reserva', $request->fecha_reserva)
            ->where('hora_reserva', $request->hora_reserva)
            ->whereIn('estado_reserva', ['pendiente', 'aceptada'])
            ->first();

        if ($reserva_existente) {
            return response()->json(['error' => 'La mesa ya está reservada para esa fecha y hora'], 400);
        }

        $precio_reserva = $mesa->precio_mesa * 0.10;

        $reserva = Reserva::create([
            'id_usuario_cliente' => $request->id_usuario_cliente,
            'id_restaurante' => $request->id_restaurante,
            'id_mesa' => $request->id_mesa,
            'fecha_reserva' => $request->fecha_reserva,
            'hora_reserva' => $request->hora_reserva,
            'precio_reserva' => $precio_reserva,
            'personas_reserva' => $request->personas_reserva,
            'comentarios_reserva' => $request->comentarios_reserva,
            'telefono_contacto_reserva' => $request->telefono_contacto_reserva,
            'fecha_creacion_reserva' => Carbon::now()
        ]);

        ImagenHelperController::procesarImagenParaModelo(
            $request, 
            $reserva, 
            'ruta_imagen_comprobante_reserva', 
            'ruta_imagen_comprobante_reserva', 
            'comprobantes_reservas'
        );

        return response()->json([
            'message' => 'Reserva creada exitosamente', 
            'reserva' => $reserva
        ], 201);
    }

    public static function obtenerReservasPorCliente($id_usuario_cliente)
    {
        $reservas = Reserva::with(['restaurante', 'mesa', 'calificacion'])
            ->where('id_usuario_cliente', $id_usuario_cliente)
            ->orderBy('fecha_reserva', 'desc')
            ->orderBy('hora_reserva', 'desc')
            ->get();

        foreach ($reservas as $reserva) {
            $reserva->comprobante_base64 = $reserva->obtenerComprobanteBase64();
            $reserva->puede_calificar = $reserva->puedeCalificar() && !$reserva->yaFueCalificada();
        }

        return response()->json(['reservas' => $reservas], 200);
    }

    public static function obtenerReservasPorRestaurante($id_restaurante)
    {
        $reservas = Reserva::with(['usuarioCliente.usuario', 'mesa'])
            ->where('id_restaurante', $id_restaurante)
            ->orderBy('fecha_reserva', 'desc')
            ->orderBy('hora_reserva', 'desc')
            ->get();

        foreach ($reservas as $reserva) {
            $reserva->comprobante_base64 = $reserva->obtenerComprobanteBase64();
        }

        return response()->json(['reservas' => $reservas], 200);
    }

    public static function procesarReserva(Request $request, $id)
    {
        $validador = Validator::make($request->all(), [
            'accion' => 'required|in:aceptar,rechazar',
            'comentario_restaurante' => 'nullable|string|max:500'
        ]);

        if ($validador->fails()) {
            return response()->json(['error' => $validador->errors()], 422);
        }

        $reserva = Reserva::find($id);
        if (!$reserva) {
            return response()->json(['error' => 'Reserva no encontrada'], 404);
        }

        if ($reserva->estado_reserva !== 'pendiente') {
            return response()->json(['error' => 'Esta reserva ya fue procesada'], 400);
        }

        $usuario_restaurante = auth('api')->user();
        if ($reserva->restaurante->id_usuario != $usuario_restaurante->id) {
            return response()->json(['error' => 'No puedes procesar una reserva que no es de tu restaurante'], 403);
        }

        $reserva->estado_reserva = $request->accion === 'aceptar' ? 'aceptada' : 'rechazada';
        if ($request->comentario_restaurante) {
            $reserva->comentarios_reserva .= "\nComentario del restaurante: " . $request->comentario_restaurante;
        }
        $reserva->save();

        $mensaje = $request->accion === 'aceptar' ? 'Reserva aceptada' : 'Reserva rechazada';
        
        return response()->json([
            'message' => $mensaje, 
            'reserva' => $reserva
        ], 200);
    }

    public static function cancelarReserva($id)
    {
        $reserva = Reserva::find($id);
        if (!$reserva) {
            return response()->json(['error' => 'Reserva no encontrada'], 404);
        }

        $usuario_cliente = auth('api')->user();
        if ($reserva->usuarioCliente->id_usuario != $usuario_cliente->id) {
            return response()->json(['error' => 'No puedes cancelar una reserva que no es tuya'], 403);
        }

        if (!in_array($reserva->estado_reserva, ['pendiente', 'aceptada'])) {
            return response()->json(['error' => 'No puedes cancelar esta reserva'], 400);
        }

        $reserva->estado_reserva = 'cancelada';
        $reserva->save();

        return response()->json([
            'message' => 'Reserva cancelada exitosamente', 
            'reserva' => $reserva
        ], 200);
    }

    public static function completarReserva($id)
    {
        $reserva = Reserva::find($id);
        if (!$reserva) {
            return response()->json(['error' => 'Reserva no encontrada'], 404);
        }

        if ($reserva->estado_reserva !== 'aceptada') {
            return response()->json(['error' => 'Solo se pueden completar reservas aceptadas'], 400);
        }

        $reserva->estado_reserva = 'completada';
        $reserva->save();

        return response()->json([
            'message' => 'Reserva marcada como completada', 
            'reserva' => $reserva
        ], 200);
    }

    public static function obtenerReservasPorFecha(Request $request, $id_restaurante)
    {
        $validador = Validator::make($request->all(), [
            'fecha' => 'required|date'
        ]);

        if ($validador->fails()) {
            return response()->json(['error' => $validador->errors()], 422);
        }

        $reservas = Reserva::with(['usuarioCliente.usuario', 'mesa'])
            ->where('id_restaurante', $id_restaurante)
            ->where('fecha_reserva', $request->fecha)
            ->orderBy('hora_reserva', 'asc')
            ->get();

        return response()->json(['reservas' => $reservas], 200);
    }
}