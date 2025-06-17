<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Reporte;
use App\Models\Calificacion;
use App\Models\UsuarioCliente;
use App\Models\UsuarioRestaurante;
use App\Http\Controllers\CalificacionController;
use Carbon\Carbon;

class ReporteController extends Controller
{
    public static function crearReporte(Request $request)
    {
        $validador = Validator::make($request->all(), [
            'id_usuario_reportante' => 'required|integer',
            'tipo_usuario_reportante' => 'required|in:cliente,restaurante',
            'id_usuario_reportado' => 'nullable|integer',
            'tipo_usuario_reportado' => 'nullable|in:cliente,restaurante',
            'id_calificacion' => 'nullable|exists:calificaciones,id',
            'motivo_reporte' => 'required|in:contenido-inapropiado,informacion-falsa,spam,acoso,discriminacion,otro',
            'descripcion_reporte' => 'required|string|max:1000'
        ]);

        if ($validador->fails()) {
            return response()->json(['error' => $validador->errors()], 422);
        }

        if (!$request->id_usuario_reportado && !$request->id_calificacion) {
            return response()->json(['error' => 'Debe especificar un usuario reportado o una calificación'], 400);
        }

        $reporte = Reporte::create([
            'id_usuario_reportante' => $request->id_usuario_reportante,
            'tipo_usuario_reportante' => $request->tipo_usuario_reportante,
            'id_usuario_reportado' => $request->id_usuario_reportado,
            'tipo_usuario_reportado' => $request->tipo_usuario_reportado,
            'id_calificacion' => $request->id_calificacion,
            'motivo_reporte' => $request->motivo_reporte,
            'descripcion_reporte' => $request->descripcion_reporte,
            'fecha_reporte' => Carbon::now()->toDateString()
        ]);

        if ($request->id_calificacion) {
            CalificacionController::marcarComoReportada($request->id_calificacion);
        }

        return response()->json([
            'message' => 'Reporte creado exitosamente', 
            'reporte' => $reporte
        ], 201);
    }

    public static function obtenerReportes(Request $request)
    {
        $query = Reporte::with(['calificacion', 'usuarioReportante', 'usuarioReportado']);

        if ($request->has('estado')) {
            $query->where('estado_reporte', $request->estado);
        }

        if ($request->has('tipo_reportante')) {
            $query->where('tipo_usuario_reportante', $request->tipo_reportante);
        }

        if ($request->has('motivo')) {
            $query->where('motivo_reporte', $request->motivo);
        }

        $reportes = $query->orderBy('fecha_reporte', 'desc')->get();

        return response()->json(['reportes' => $reportes], 200);
    }

    public static function obtenerReportePorId($id)
    {
        $reporte = Reporte::with(['calificacion.usuarioCliente', 'calificacion.restaurante'])
            ->find($id);
        
        if (!$reporte) {
            return response()->json(['error' => 'Reporte no encontrado'], 404);
        }

        return response()->json(['reporte' => $reporte], 200);
    }

    public static function procesarReporte(Request $request, $id)
    {
        $validador = Validator::make($request->all(), [
            'accion' => 'required|in:aceptar,rechazar',
            'comentario_admin' => 'nullable|string|max:500'
        ]);

        if ($validador->fails()) {
            return response()->json(['error' => $validador->errors()], 422);
        }

        $reporte = Reporte::find($id);
        if (!$reporte) {
            return response()->json(['error' => 'Reporte no encontrado'], 404);
        }

        $usuario_admin = auth('api')->user();
        if ($usuario_admin->rol !== 'administrador') {
            return response()->json(['error' => 'Solo administradores pueden procesar reportes'], 403);
        }

        $reporte->estado_reporte = $request->accion === 'aceptar' ? 'aceptado' : 'rechazado';
        $reporte->revisado_por_admin = true;
        $reporte->save();

        if ($request->accion === 'aceptar' && $reporte->id_calificacion) {
            $calificacion = Calificacion::find($reporte->id_calificacion);
            if ($calificacion) {
                CalificacionController::eliminarCalificacion($calificacion->id);
            }
        }

        $mensaje = $request->accion === 'aceptar' ? 'Reporte aceptado' : 'Reporte rechazado';
        
        return response()->json([
            'message' => $mensaje, 
            'reporte' => $reporte
        ], 200);
    }

    public static function obtenerReportesPendientes()
    {
        $reportes = Reporte::with(['calificacion', 'usuarioReportante', 'usuarioReportado'])
            ->where('estado_reporte', 'pendiente')
            ->orderBy('fecha_reporte', 'asc')
            ->get();

        return response()->json(['reportes_pendientes' => $reportes], 200);
    }
}