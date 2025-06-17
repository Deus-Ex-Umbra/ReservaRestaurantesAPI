<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarRol
{
    public function handle(Request $request, Closure $next, ...$roles_permitidos): Response
    {
        $usuario = auth('api')->user();

        if (!$usuario) {
            return response()->json([
                'error' => 'Usuario no autenticado.'
            ], 401);
        }

        if (!in_array($usuario->rol, $roles_permitidos)) {
            return response()->json([
                'error' => 'Acceso denegado. Rol no autorizado.',
                'rol_usuario' => $usuario->rol,
                'roles_permitidos' => $roles_permitidos
            ], 403);
        }

        return $next($request);
    }
}