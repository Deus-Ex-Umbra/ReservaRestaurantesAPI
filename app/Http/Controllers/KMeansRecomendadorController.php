<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UsuarioCliente;
use App\Models\UsuarioRestaurante;
use App\Models\Reserva;

class KMeansRecomendadorController extends Controller
{
    // Pesos obtenidos después de entrenar un modelo One vs All
    private $pesos_modelo = [
        ['comida-tradicional', -7.1649, 2.5152, 2.2121, -2.8266, 2.6713, -2.2663],
        ['parrilla', -6.4401, 0.1575, -2.958, -1.7565, -2.5468, -3.0919],
        ['comida-rapida', -6.705, 1.4072, -3.1445, 2.7064, 3.1858, -1.2736],
        ['italiana', -6.8581, -3.523, 3.294, 1.2343, -1.0335, -2.7751],
        ['china', -6.3861, 2.5098, 2.8159, 3.0776, -0.4051, 1.3269],
        ['internacional', -7.2881, -1.8589, -2.4916, 2.6711, -2.5548, 2.3933],
        ['postres', -6.3779, 1.5916, 0.5362, -3.3082, -2.2735, 3.1126],
        ['bebidas', -6.9208, -3.1767, -0.4252, -2.0575, 3.407, 2.3544]
    ];

    private $mapa_tipos = [
        'comida-tradicional', 'parrilla', 'comida-rapida', 'italiana', 
        'china', 'internacional', 'postres', 'bebidas'
    ];

    private function funcionSigmoide(float $z): float
    {
        return 1.0 / (1.0 + exp(-$z));
    }

    public function predecirTipoRestaurante(Reserva $reserva): string
    {
        $datos_normalizados = $this->normalizarDatosReserva($reserva);
        $entrada_con_sesgo = array_merge([1.0], array_values($datos_normalizados));

        $puntuaciones = [];

        foreach ($this->pesos_modelo as $pesos_clase) {
            $tipo_restaurante = array_shift($pesos_clase);
            $producto_punto = 0;
            
            for ($i = 0; $i < count($entrada_con_sesgo); $i++) {
                $producto_punto += $pesos_clase[$i] * $entrada_con_sesgo[$i];
            }
            
            $puntuaciones[$tipo_restaurante] = $this->funcionSigmoide($producto_punto);
        }

        arsort($puntuaciones);
        return key($puntuaciones);
    }

    public static function obtenerRecomendacionesParaCliente($id_usuario_cliente)
    {
        $cliente = UsuarioCliente::find($id_usuario_cliente);

        if (!$cliente) {
            return ['error' => 'Usuario cliente no encontrado'];
        }
        
        $ultima_reserva = $cliente->obtenerUltimaReserva();
        
        if (!$ultima_reserva) {
            return self::obtenerRecomendacionesGenerales();
        }

        $recomendador = new self();
        $tipo_predicho = $recomendador->predecirTipoRestaurante($ultima_reserva);
        
        $top_restaurantes = self::obtenerTop5Restaurantes();
        $restaurantes_tipo_predicho = self::obtenerRestaurantesPorTipo($tipo_predicho, $top_restaurantes->pluck('id')->toArray());

        $recomendaciones_finales = $top_restaurantes->merge($restaurantes_tipo_predicho)->unique('id');

        return [
            'recomendaciones' => $recomendaciones_finales
        ];
    }

    private static function obtenerRecomendacionesGenerales()
    {
        return [
            'recomendaciones' => self::obtenerTop5Restaurantes()
        ];
    }
    
    private function normalizarDatosReserva(Reserva $reserva)
    {
        $preferencia = $reserva->usuarioCliente->preferencias;
        $tipos_map = array_flip($this->mapa_tipos);

        $tipo_pref_normalizado = $preferencia ? (($tipos_map[$preferencia->tipo_restaurante_preferencia] ?? 2) / count($tipos_map)) : 0.5;
        $calificacion_min_norm = $preferencia ? ($preferencia->calificacion_minima_preferencia / 5.0) : 0.6;
        $precio_max_norm = $preferencia ? (min(max($preferencia->precio_maximo_preferencia, 50), 500) - 50) / 450 : 0.5;
        $precio_reserva_norm = (min(max($reserva->precio_reserva, 10), 300) - 10) / 290;
        $personas_reserva_norm = (min(max($reserva->personas_reserva, 1), 8) - 1) / 7;

        return [
            'tipo_restaurante_preferencia' => $tipo_pref_normalizado,
            'calificacion_minima_preferencia' => $calificacion_min_norm,
            'precio_maximo_preferencia' => $precio_max_norm,
            'precio_reserva' => $precio_reserva_norm,
            'cantidad_personas_reserva' => $personas_reserva_norm
        ];
    }

    private static function obtenerTop5Restaurantes()
    {
        return UsuarioRestaurante::with('usuario')
            ->orderBy('calificacion', 'desc')
            ->take(5)
            ->get()
            ->map(function($restaurante) {
                $restaurante->imagen_base64 = $restaurante->obtenerImagenBase64();
                return $restaurante;
            });
    }

    private static function obtenerRestaurantesPorTipo($tipo, $excluir_ids = [])
    {
        return UsuarioRestaurante::with('usuario')
            ->where('tipo_restaurante', $tipo)
            ->where('calificacion', '>=', 3.0)
            ->whereNotIn('id', $excluir_ids)
            ->orderBy('calificacion', 'desc')
            ->take(5)
            ->get()
            ->map(function($restaurante) {
                $restaurante->imagen_base64 = $restaurante->obtenerImagenBase64();
                return $restaurante;
            });
    }
}
