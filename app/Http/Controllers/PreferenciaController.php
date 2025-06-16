<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class PreferenciaController extends Controller
{
    public static function crearPreferencia(Request $request){
        $validador = Validator::make($request->all(), [
            'id_usuario_cliente' => 'required|exists:usuarios,id',
            'tipo_restaurante_preferencia' => 'required|string|max:255',
            'precio_minimo_preferencia' => 'required|numeric|min:0',
            'precio_maximo_preferencia' => 'required|numeric|min:0',
            'calificacion_minima_preferencia' => 'required|numeric|min:0|max:5',
        ]);

        if ($validador->fails()) {
            return response()->json(['error' => $validador->errors()], 422);
        }

        $preferencia = Preferencia::create($request->all());

        return response()->json(['message' => 'Preferencia creada exitosamente', 'preferencia' => $preferencia], 201);
    }

    public static function obtenerPreferencias(Request $request) {
        $preferencias = Preferencia::with('usuarioCliente')->get();

        foreach ($preferencias as $preferencia) {
            $preferencia->usuario_cliente = $preferencia->usuarioCliente;
        }

        return response()->json(['preferencias' => $preferencias], 200);
    }

    public static function obtenerPreferenciaPorId($id) {
        $preferencia = Preferencia::with('usuarioCliente')->find($id);
        if (!$preferencia) {
            return response()->json(['error' => 'Preferencia no encontrada'], 404);
        }

        $preferencia->usuario_cliente = $preferencia->usuarioCliente;
        return response()->json(['preferencia' => $preferencia], 200);
    }
}
