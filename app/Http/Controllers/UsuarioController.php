<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UsuarioController extends Controller
{
    public function iniciarSesion(Request $request) {
        $validador = Validator::make($request->all(), [
            'correo' => 'required|email',
            'contraseña' => 'required|string|min:6'
        ]);

        if ($validador->fails()) {
            return response()->json(['error' => $validador->errors()], 422);
        }

        $credenciales = [
            'correo' => $request->correo,
            'password' => $request->contraseña
        ];

        if (!$token = auth('api')->attempt($credenciales)) {
            return response()->json(['error' => 'No autorizado'], 401);
        }

        return $this->responderConToken($token);
    }

    public function registrarse(Request $request) {
        $validador = Validator::make($request->all(), [
            'correo' => 'required|string|email|unique:usuarios',
            'contraseña' => 'required|string|min:6',
            'rol' => 'required|in:administrador,cliente,restaurante'
        ]);

        if ($validador->fails()) {
            return response()->json(['error' => $validador->errors()], 422);
        }

        $usuario = Usuario::create([
            'correo' => $request->correo,
            'contraseña' => bcrypt($request->contraseña),
            'rol' => $request->rol
        ]);

        return response()->json([
            'message' => 'Usuario registrado exitosamente',
            'usuario' => $usuario
        ], 201);
    }

    public function cerrarSesion() {
        auth('api')->logout();
        return response()->json(['message' => 'Sesión cerrada exitosamente'], 200);
    }

    public function obtenerUsuarioAutenticado() {
        return response()->json(auth('api')->user());
    }

    protected function responderConToken($token) {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => auth('api')->user()
        ]);
    }
}
