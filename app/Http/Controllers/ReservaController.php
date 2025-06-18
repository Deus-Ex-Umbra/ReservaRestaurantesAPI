<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Reserva;
use App\Models\Mesa;
use App\Models\Plato;
use App\Models\UsuarioCliente;
use App\Models\UsuarioRestaurante;
use Carbon\Carbon;

class ReservaController extends Controller
{
    public static function crearReserva(Request $request)
    {
        $validador = Validator::make($request->all(), [
            'id_usuario_cliente' => 'required|exists:usuarios_clientes,id',
            'id_restaurante' => 'required|exists:usuarios_restaurantes,id',
            'id_mesas' => 'required|array',
            'id_mesas.*' => 'required|exists:mesas,id',
            'id_platos' => 'required|array',
            'id_platos.*' => 'required|exists:platos,id',
            'fecha_reserva' => 'required|date|after_or_equal:today',
            'hora_reserva' => 'required|date_format:H:i',
            'personas_reserva' => 'required|integer|min:1',
            'comentarios_reserva' => 'nullable|string|max:500',
            'telefono_contacto_reserva' => 'nullable|string|max:15',
        ]);

        if ($validador->fails()) {
            return response()->json(['error' => $validador->errors()], 422);
        }

        DB::beginTransaction();

        try {
            $mesas = Mesa::whereIn('id', $request->id_mesas)->get();
            $capacidad_total_mesas = 0;

            foreach ($mesas as $mesa) {
                if ($mesa->id_restaurante != $request->id_restaurante) {
                    throw new \Exception('La mesa con ID ' . $mesa->id . ' no pertenece al restaurante especificado.');
                }
                if (!$mesa->estaDisponible($request->fecha_reserva, $request->hora_reserva)) {
                    throw new \Exception('La mesa ' . $mesa->numero_mesa . ' ya está reservada para esa fecha y hora.');
                }
                $capacidad_total_mesas += $mesa->capacidad_mesa;
            }

            if ($capacidad_total_mesas < $request->personas_reserva) {
                throw new \Exception('La capacidad total de las mesas seleccionadas no es suficiente.');
            }

            $platos = Plato::whereIn('id', $request->id_platos)->get();
            $precio_total_platos = $platos->sum('precio_plato');
            $precio_reserva = $precio_total_platos * 0.25;

            $reserva = Reserva::create([
                'id_usuario_cliente' => $request->id_usuario_cliente,
                'id_restaurante' => $request->id_restaurante,
                'fecha_reserva' => $request->fecha_reserva,
                'hora_reserva' => $request->hora_reserva,
                'precio_total' => $precio_total_platos,
                'precio_reserva' => $precio_reserva,
                'personas_reserva' => $request->personas_reserva,
                'comentarios_reserva' => $request->comentarios_reserva,
                'telefono_contacto_reserva' => $request->telefono_contacto_reserva,
                'fecha_creacion_reserva' => Carbon::now()
            ]);

            $reserva->mesas()->attach($request->id_mesas);
            $reserva->platos()->attach($request->id_platos);

            DB::commit();

            return response()->json([
                'message' => 'Pre-reserva creada exitosamente. Por favor, contacta al restaurante para confirmar.', 
                'reserva_id' => $reserva->id,
                'detalles_whatsapp' => [
                    'numero_restaurante' => $reserva->restaurante->telefono_restaurante,
                    'numero_reserva' => $reserva->id,
                    'precio_total' => $precio_total_platos,
                    'monto_adelanto' => $precio_reserva,
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public static function obtenerReservasPorCliente($id_usuario_cliente)
    {
        $reservas = Reserva::with(['restaurante', 'mesas', 'platos', 'calificacion'])
            ->where('id_usuario_cliente', $id_usuario_cliente)
            ->orderBy('fecha_reserva', 'desc')
            ->orderBy('hora_reserva', 'desc')
            ->get();

        foreach ($reservas as $reserva) {
            $reserva->puede_calificar = $reserva->puedeCalificar() && !$reserva->yaFueCalificada();
        }

        return response()->json(['reservas' => $reservas], 200);
    }

    public static function obtenerReservasPorRestaurante($id_restaurante)
    {
        $reservas = Reserva::with(['usuarioCliente.usuario', 'mesas', 'platos'])
            ->where('id_restaurante', $id_restaurante)
            ->orderBy('fecha_reserva', 'desc')
            ->orderBy('hora_reserva', 'desc')
            ->get();

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

        $usuario_autenticado = auth('api')->user();
        $restaurante = UsuarioRestaurante::where('id_usuario', $usuario_autenticado->id)->first();
        if ($reserva->id_restaurante != $restaurante->id) {
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

        $usuario_autenticado = auth('api')->user();
        $cliente = UsuarioCliente::where('id_usuario', $usuario_autenticado->id)->first();
        
        if ($reserva->id_usuario_cliente != $cliente->id) {
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

        $reservas = Reserva::with(['usuarioCliente.usuario', 'mesas', 'platos'])
            ->where('id_restaurante', $id_restaurante)
            ->where('fecha_reserva', $request->fecha)
            ->orderBy('hora_reserva', 'asc')
            ->get();

        return response()->json(['reservas' => $reservas], 200);
    }
}
