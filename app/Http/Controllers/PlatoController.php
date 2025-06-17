<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Plato;
use App\Models\Menu;
use App\Http\Controllers\ImagenHelperController;

class PlatoController extends Controller
{
    public static function crearPlato(Request $request)
    {
        $validador = Validator::make($request->all(), [
            'id_menu' => 'required|exists:menus,id',
            'nombre_plato' => 'required|string|max:255',
            'descripcion_plato' => 'nullable|string',
            'precio_plato' => 'required|numeric|min:0',
            'ruta_imagen_plato' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'disponible' => 'boolean'
        ]);

        if ($validador->fails()) {
            return response()->json(['error' => $validador->errors()], 422);
        }

        $plato = Plato::create($request->except('ruta_imagen_plato'));

        ImagenHelperController::procesarImagenParaModelo(
            $request, 
            $plato, 
            'ruta_imagen_plato', 
            'ruta_imagen_plato', 
            'imagenes_platos'
        );

        return response()->json([
            'message' => 'Plato creado exitosamente', 
            'plato' => $plato
        ], 201);
    }

    public static function obtenerPlatosPorMenu($id_menu)
    {
        $menu = Menu::find($id_menu);
        if (!$menu) {
            return response()->json(['error' => 'Menú no encontrado'], 404);
        }

        $platos = Plato::where('id_menu', $id_menu)->get();
        
        foreach ($platos as $plato) {
            $plato->imagen_base64 = $plato->obtenerImagenBase64();
        }

        return response()->json(['platos' => $platos], 200);
    }

    public static function obtenerPlatoPorId($id)
    {
        $plato = Plato::find($id);
        if (!$plato) {
            return response()->json(['error' => 'Plato no encontrado'], 404);
        }

        $plato->imagen_base64 = $plato->obtenerImagenBase64();
        return response()->json(['plato' => $plato], 200);
    }

    public static function editarPlato(Request $request, $id)
    {
        $validador = Validator::make($request->all(), [
            'nombre_plato' => 'sometimes|required|string|max:255',
            'descripcion_plato' => 'sometimes|nullable|string',
            'precio_plato' => 'sometimes|required|numeric|min:0',
            'ruta_imagen_plato' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'disponible' => 'sometimes|boolean'
        ]);

        if ($validador->fails()) {
            return response()->json(['error' => $validador->errors()], 422);
        }

        $plato = Plato::find($id);
        if (!$plato) {
            return response()->json(['error' => 'Plato no encontrado'], 404);
        }

        $plato->fill($request->except('ruta_imagen_plato'));
        $plato->save();

        ImagenHelperController::procesarImagenParaModelo(
            $request, 
            $plato, 
            'ruta_imagen_plato', 
            'ruta_imagen_plato', 
            'imagenes_platos'
        );

        return response()->json([
            'message' => 'Plato actualizado exitosamente', 
            'plato' => $plato
        ], 200);
    }

    public static function eliminarPlato($id)
    {
        $plato = Plato::find($id);
        if (!$plato) {
            return response()->json(['error' => 'Plato no encontrado'], 404);
        }

        if ($plato->ruta_imagen_plato) {
            ImagenHelperController::eliminarImagen($plato->ruta_imagen_plato);
        }

        $plato->delete();
        return response()->json(['message' => 'Plato eliminado exitosamente'], 200);
    }

    public static function cambiarDisponibilidad($id)
    {
        $plato = Plato::find($id);
        if (!$plato) {
            return response()->json(['error' => 'Plato no encontrado'], 404);
        }

        $plato->disponible = !$plato->disponible;
        $plato->save();

        $estado = $plato->disponible ? 'disponible' : 'no disponible';
        return response()->json([
            'message' => "Plato marcado como {$estado}", 
            'plato' => $plato
        ], 200);
    }
}