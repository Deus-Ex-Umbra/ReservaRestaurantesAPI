<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Preferencia;

class PreferenciaController extends Controller
{
    public static function crearPreferencia(Request $request){
        $validador = Validator::make($request->all(), [
            'id_usuario_cliente' => 'required|exists:usuarios_clientes,id',
            'tipo_restaurante_preferencia' => 'required|in:comida-tradicional,parrilla,comida-rapida,italiana,china,internacional,postres,bebidas',
            'calificacion_minima_preferencia' => 'required|numeric|min:0|max:5',
        ]);

        if ($validador->fails()) {
            return response()->json(['error' => $validador->errors()], 422);
        }

        $preferencia_existente = Preferencia::where('id_usuario_cliente', $request->id_usuario_cliente)->first();
        if ($preferencia_existente) {
            return response()->json(['error' => 'El usuario ya tiene preferencias configuradas'], 400);
        }

        $preferencia = Preferencia::create($request->all());

        return response()->json(['message' => 'Preferencia creada exitosamente', 'preferencia' => $preferencia], 201);
    }

    public static function obtenerPreferencias(Request $request) {
        $preferencias = Preferencia::with('usuarioCliente.usuario')->get();

        return response()->json(['preferencias' => $preferencias], 200);
    }

    public static function obtenerPreferenciaPorId($id) {
        $preferencia = Preferencia::with('usuarioCliente.usuario')->find($id);
        if (!$preferencia) {
            return response()->json(['error' => 'Preferencia no encontrada'], 404);
        }

        return response()->json(['preferencia' => $preferencia], 200);
    }

    public static function obtenerPreferenciaPorUsuarioCliente($id_usuario_cliente) {
        $preferencia = Preferencia::with('usuarioCliente.usuario')
            ->where('id_usuario_cliente', $id_usuario_cliente)
            ->first();
        
        if (!$preferencia) {
            return response()->json(['error' => 'El usuario no tiene preferencias configuradas'], 404);
        }

        return response()->json(['preferencia' => $preferencia], 200);
    }

    public static function editarPreferencia(Request $request, $id) {
        $validador = Validator::make($request->all(), [
            'tipo_restaurante_preferencia' => 'sometimes|required|in:comida-tradicional,parrilla,comida-rapida,italiana,china,internacional,postres,bebidas',
            'calificacion_minima_preferencia' => 'sometimes|required|numeric|min:0|max:5',
        ]);

        if ($validador->fails()) {
            return response()->json(['error' => $validador->errors()], 422);
        }

        $preferencia = Preferencia::find($id);
        if (!$preferencia) {
            return response()->json(['error' => 'Preferencia no encontrada'], 404);
        }

        $usuario_autenticado = auth('api')->user();
        if ($preferencia->usuarioCliente->id_usuario != $usuario_autenticado->id) {
            return response()->json(['error' => 'No puedes editar preferencias que no son tuyas'], 403);
        }

        $preferencia->update($request->all());

        return response()->json(['message' => 'Preferencia actualizada exitosamente', 'preferencia' => $preferencia], 200);
    }

    public static function eliminarPreferenciaPorId($id) {
        $preferencia = Preferencia::find($id);
        if (!$preferencia) {
            return response()->json(['error' => 'Preferencia no encontrada'], 404);
        }

        $usuario_autenticado = auth('api')->user();
        if ($usuario_autenticado->rol === 'cliente' && 
            $preferencia->usuarioCliente->id_usuario != $usuario_autenticado->id) {
            return response()->json(['error' => 'No puedes eliminar preferencias que no son tuyas'], 403);
        }

        $preferencia->delete();

        return response()->json(['message' => 'Preferencia eliminada exitosamente'], 200);
    }

    public static function obtenerTiposRestaurante()
    {
        $tipos = [
            'comida-tradicional' => 'Comida Tradicional',
            'parrilla' => 'Parrilla',
            'comida-rapida' => 'Comida Rápida',
            'italiana' => 'Italiana',
            'china' => 'China',
            'internacional' => 'Internacional',
            'postres' => 'Postres',
            'bebidas' => 'Bebidas'
        ];

        return response()->json(['tipos_restaurante' => $tipos], 200);
    }
}