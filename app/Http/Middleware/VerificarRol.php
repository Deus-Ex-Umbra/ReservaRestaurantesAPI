<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarRol
{
    public function handle(Request $request, Closure $next, $_usuario, $_rol): Response
    {
        if ($_usuario->rol !== $_rol) {
            return response()->json([
                'error' => 'Acceso denegado. Rol no autorizado.'
            ], 403);
        }
        return $next($request);
    }
}
