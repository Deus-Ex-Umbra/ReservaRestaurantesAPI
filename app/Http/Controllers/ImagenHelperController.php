<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImagenHelperController extends Controller
{
    public static function guardarImagen(Request $request, $campo_archivo, $directorio)
    {
        if (!$request->hasFile($campo_archivo)) {
            return null;
        }

        $archivo = $request->file($campo_archivo);
        
        if (!$archivo->isValid()) {
            return null;
        }

        return $archivo->store($directorio, 'public');
    }

    public static function obtenerImagenBase64($ruta_imagen)
    {
        if (!$ruta_imagen) {
            return null;
        }

        $ruta_completa = storage_path("app/public/{$ruta_imagen}");

        if (!file_exists($ruta_completa)) {
            return null;
        }

        return base64_encode(file_get_contents($ruta_completa));
    }

    public static function eliminarImagen($ruta_imagen)
    {
        if (!$ruta_imagen) {
            return false;
        }

        $ruta_completa = storage_path("app/public/{$ruta_imagen}");

        if (file_exists($ruta_completa)) {
            return unlink($ruta_completa);
        }

        return false;
    }

    public static function validarTipoImagen($request, $campo_archivo)
    {
        return $request->validate([
            $campo_archivo => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);
    }

    public static function procesarImagenParaModelo($request, $modelo, $campo_archivo, $campo_ruta, $directorio)
    {
        if ($request->hasFile($campo_archivo)) {
            if ($modelo->$campo_ruta) {
                self::eliminarImagen($modelo->$campo_ruta);
            }
            
            $nueva_ruta = self::guardarImagen($request, $campo_archivo, $directorio);
            if ($nueva_ruta) {
                $modelo->$campo_ruta = $nueva_ruta;
                $modelo->save();
            }
        }

        return $modelo;
    }
}