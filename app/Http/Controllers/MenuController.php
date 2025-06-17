<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Menu;
use App\Models\UsuarioRestaurante;
use App\Http\Controllers\ImagenHelperController;

class MenuController extends Controller
{
    public function obtenerMenusPorRestaurante(Request $request, $id_restaurante) {
        $restaurante = UsuarioRestaurante::find($id_restaurante);
        if (!$restaurante) {
            return response()->json(['error' => 'Restaurante no encontrado'], 404);
        }

        $menus = Menu::with('platos')
            ->where('id_restaurante', $id_restaurante)
            ->get();

        if ($menus->isEmpty()) {
            return response()->json(['error' => 'No se encontraron menús para este restaurante'], 404);
        }

        foreach ($menus as $menu) {
            $menu->imagen_base64 = $menu->obtenerImagenBase64();
            $menu->precio_promedio = $menu->calcularPrecioPromedio();
            
            foreach ($menu->platos as $plato) {
                $plato->imagen_base64 = $plato->obtenerImagenBase64();
            }
        }

        return response()->json(['menus' => $menus], 200);
    }

    public function crearMenu(Request $request)
    {
        $validador = Validator::make($request->all(), [
            'id_restaurante' => 'required|exists:usuarios_restaurantes,id',
            'nombre_menu' => 'required|string|max:255',
            'descripcion_menu' => 'nullable|string',
            'tipo_menu' => 'required|in:comida-tradicional,parrilla,comida-rapida,italiana,china,internacional,postres,bebidas',
            'ruta_imagen_menu' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        if ($validador->fails()) {
            return response()->json(['error' => $validador->errors()], 422);
        }

        $usuario_autenticado = auth('api')->user();
        $restaurante = UsuarioRestaurante::find($request->id_restaurante);
        
        if ($restaurante->id_usuario != $usuario_autenticado->id) {
            return response()->json(['error' => 'No puedes crear menús para un restaurante que no es tuyo'], 403);
        }

        $menu = Menu::create($request->except('ruta_imagen_menu'));

        ImagenHelperController::procesarImagenParaModelo(
            $request, 
            $menu, 
            'ruta_imagen_menu', 
            'ruta_imagen_menu', 
            'imagenes_menus'
        );

        return response()->json(['message' => 'Menú creado exitosamente', 'menu' => $menu], 201);
    }

    public function obtenerMenuPorId($id)
    {
        $menu = Menu::with(['platos', 'restaurante'])->find($id);
        if (!$menu) {
            return response()->json(['error' => 'Menú no encontrado'], 404);
        }

        $menu->imagen_base64 = $menu->obtenerImagenBase64();
        $menu->precio_promedio = $menu->calcularPrecioPromedio();
        
        foreach ($menu->platos as $plato) {
            $plato->imagen_base64 = $plato->obtenerImagenBase64();
        }

        return response()->json(['menu' => $menu], 200);
    }

    public function editarMenu(Request $request, $id)
    {
        $validador = Validator::make($request->all(), [
            'nombre_menu' => 'sometimes|required|string|max:255',
            'descripcion_menu' => 'sometimes|nullable|string',
            'tipo_menu' => 'sometimes|required|in:comida-tradicional,parrilla,comida-rapida,italiana,china,internacional,postres,bebidas',
            'ruta_imagen_menu' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        if ($validador->fails()) {
            return response()->json(['error' => $validador->errors()], 422);
        }

        $menu = Menu::find($id);
        if (!$menu) {
            return response()->json(['error' => 'Menú no encontrado'], 404);
        }

        $usuario_autenticado = auth('api')->user();
        if ($menu->restaurante->id_usuario != $usuario_autenticado->id) {
            return response()->json(['error' => 'No puedes editar menús que no son de tu restaurante'], 403);
        }

        $menu->fill($request->except('ruta_imagen_menu'));
        $menu->save();

        ImagenHelperController::procesarImagenParaModelo(
            $request, 
            $menu, 
            'ruta_imagen_menu', 
            'ruta_imagen_menu', 
            'imagenes_menus'
        );

        return response()->json(['message' => 'Menú actualizado exitosamente', 'menu' => $menu], 200);
    }

    public function eliminarMenu($id)
    {
        $menu = Menu::find($id);
        if (!$menu) {
            return response()->json(['error' => 'Menú no encontrado'], 404);
        }

        $usuario_autenticado = auth('api')->user();
        if ($menu->restaurante->id_usuario != $usuario_autenticado->id) {
            return response()->json(['error' => 'No puedes eliminar menús que no son de tu restaurante'], 403);
        }

        if ($menu->ruta_imagen_menu) {
            ImagenHelperController::eliminarImagen($menu->ruta_imagen_menu);
        }

        $menu->delete();
        return response()->json(['message' => 'Menú eliminado exitosamente'], 200);
    }

    public function obtenerMenusPorTipo($tipo)
    {
        $menus = Menu::with(['platos', 'restaurante'])
            ->where('tipo_menu', $tipo)
            ->get();

        foreach ($menus as $menu) {
            $menu->imagen_base64 = $menu->obtenerImagenBase64();
            $menu->precio_promedio = $menu->calcularPrecioPromedio();
            
            foreach ($menu->platos as $plato) {
                $plato->imagen_base64 = $plato->obtenerImagenBase64();
            }
        }

        return response()->json(['menus' => $menus], 200);
    }

    public function obtenerTiposMenu()
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

        return response()->json(['tipos_menu' => $tipos], 200);
    }
}