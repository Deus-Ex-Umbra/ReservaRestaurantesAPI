<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\UsuarioAdministrador;
use App\Models\UsuarioCliente;
use App\Models\UsuarioRestaurante;
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

        $usuario = auth('api')->user();
        $usuario_detalle = $this->obtenerDetalleUsuario($usuario);

        return $this->responderConToken($token, $usuario_detalle);
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
            'usuario' => $usuario,
            'requiere_completar_perfil' => true
        ], 201);
    }

    public function cerrarSesion() {
        auth('api')->logout();
        return response()->json(['message' => 'Sesión cerrada exitosamente'], 200);
    }

    public function obtenerPerfilUsuario() {
        $usuario = auth('api')->user();
        $usuario_detalle = $this->obtenerDetalleUsuario($usuario);

        return response()->json([
            'usuario' => $usuario_detalle,
            'perfil_completo' => $usuario_detalle !== null
        ], 200);
    }

    private function obtenerDetalleUsuario($usuario)
    {
        if (!$usuario) {
            return null;
        }

        switch ($usuario->rol) {
            case 'administrador':
                $detalle = UsuarioAdministrador::with('usuario')->where('id_usuario', $usuario->id)->first();
                break;
            case 'cliente':
                $detalle = UsuarioCliente::with(['usuario', 'preferencias'])->where('id_usuario', $usuario->id)->first();
                break;
            case 'restaurante':
                $detalle = UsuarioRestaurante::with(['usuario', 'menus.platos'])->where('id_usuario', $usuario->id)->first();
                break;
            default:
                return null;
        }

        if ($detalle && method_exists($detalle, 'obtenerImagenBase64')) {
            $detalle->imagen_base64 = $detalle->obtenerImagenBase64();
        }

        return $detalle;
    }

    protected function responderConToken($token, $usuario_detalle = null) {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => auth('api')->user(),
            'usuario_detalle' => $usuario_detalle,
            'perfil_completo' => $usuario_detalle !== null
        ]);
    }
}