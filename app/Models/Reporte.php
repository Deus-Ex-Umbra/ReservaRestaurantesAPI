<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\UsuarioCliente;
use App\Models\UsuarioRestaurante;
use App\Models\Calificacion;

class Reporte extends Model
{
    use HasFactory;
    
    protected $table = 'reportes';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario_reportante',
        'tipo_usuario_reportante',
        'id_usuario_reportado',
        'tipo_usuario_reportado',
        'id_calificacion',
        'motivo_reporte',
        'descripcion_reporte',
        'fecha_reporte',
        'estado_reporte',
        'revisado_por_admin'
    ];

    public function usuarioReportante()
    {
        if ($this->tipo_usuario_reportante === 'cliente') {
            return $this->belongsTo(UsuarioCliente::class, 'id_usuario_reportante');
        } elseif ($this->tipo_usuario_reportante === 'restaurante') {
            return $this->belongsTo(UsuarioRestaurante::class, 'id_usuario_reportante');
        }
        return null;
    }

    public function usuarioReportado()
    {
        if ($this->tipo_usuario_reportado === 'cliente') {
            return $this->belongsTo(UsuarioCliente::class, 'id_usuario_reportado');
        } elseif ($this->tipo_usuario_reportado === 'restaurante') {
            return $this->belongsTo(UsuarioRestaurante::class, 'id_usuario_reportado');
        }
        return null;
    }

    public function calificacion()
    {
        return $this->belongsTo(Calificacion::class, 'id_calificacion');
    }

    public function normalizarDatos()
    {
        $motivos = [
            'contenido-inapropiado' => 1,
            'informacion-falsa' => 2,
            'spam' => 3,
            'acoso' => 4,
            'discriminacion' => 5,
            'otro' => 6
        ];

        $estados = [
            'pendiente' => 1,
            'revisado' => 2,
            'aceptado' => 3,
            'rechazado' => 4
        ];

        return [
            'id' => $this->id,
            'motivo_numerico' => $motivos[$this->motivo_reporte] ?? 0,
            'estado_numerico' => $estados[$this->estado_reporte] ?? 0,
            'revisado_numerico' => $this->revisado_por_admin ? 1 : 0,
            'longitud_descripcion' => strlen($this->descripcion_reporte ?? '')
        ];
    }
}