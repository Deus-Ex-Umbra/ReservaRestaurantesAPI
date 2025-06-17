<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UsuarioCliente;
use App\Models\UsuarioRestaurante;

class KMeansRecomendadorController extends Controller
{
    /**
     * Pesos del Modelo One-vs-All: 6 características + 1 sesgo (bias).
     * Cada fila es un clasificador para una clase de tipo de restaurante.
     * 
     * DATOS DE ENTRADA NECESARIOS (6 características normalizadas 0-1):
     * 1. tipo_restaurante_preferencia_numerico (0-1): Preferencia de tipo de comida normalizada
     * 2. calificacion_minima_preferencia (0-1): Calificación mínima deseada / 5.0
     * 3. precio_maximo_preferencia (0-1): Precio máximo dispuesto a pagar normalizado
     * 4. precio_ultima_reserva (0-1): Precio de la última reserva normalizada
     * 5. personas_ultima_reserva (0-1): Cantidad de personas de última reserva normalizada
     * 6. frecuencia_reservas (0-1): Frecuencia de reservas del usuario normalizada
     * 
     * @var array
     */
    private $pesos_modelo = [
        // --- COPIA Y PEGA TUS PESOS AQUÍ DESPUÉS DEL ENTRENAMIENTO ---
        // Clasificador para comida-tradicional [sesgo, característica1, característica2, ...]
        [1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0],
        // Clasificador para parrilla
        [1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0],
        // Clasificador para comida-rapida
        [1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0],
        // Clasificador para italiana
        [1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0],
        // Clasificador para china
        [1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0],
        // Clasificador para internacional
        [1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0],
        // Clasificador para postres
        [1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0],
        // Clasificador para bebidas
        [1.0, 1.0, 1.0, 1.0, 1.0, 1.0, 1.0]
    ];

    /**
     * Mapeo de índices a tipos de restaurante.
     * El orden DEBE coincidir con los pesos del modelo.
     * @var array
     */
    private $mapa_tipos = [
        0 => 'comida-tradicional',
        1 => 'parrilla',
        2 => 'comida-rapida', 
        3 => 'italiana',
        4 => 'china',
        5 => 'internacional',
        6 => 'postres',
        7 => 'bebidas'
    ];

    private function funcionSigmoide(float $z): float
    {
        return 1.0 / (1.0 + exp(-$z));
    }

    public function predecirTipoRestaurante(array $datos_normalizados): string
    {
        if (count($datos_normalizados) !== 6) {
            throw new \InvalidArgumentException("Se esperan exactamente 6 características de entrada.");
        }

        // Agregar sesgo (bias) al inicio del vector
        $entrada_con_sesgo = array_merge([1.0], $datos_normalizados);

        $puntuaciones = [];

        // Calcular probabilidad para cada clase usando One-vs-All
        foreach ($this->pesos_modelo as $indice => $pesos_clase) {
            $producto_punto = 0;
            
            for ($i = 0; $i < count($entrada_con_sesgo); $i++) {
                $producto_punto += $pesos_clase[$i] * $entrada_con_sesgo[$i];
            }
            
            $puntuaciones[$indice] = $this->funcionSigmoide($producto_punto);
        }

        // Encontrar la clase con mayor probabilidad
        arsort($puntuaciones);
        $indice_predicho = key($puntuaciones);

        return $this->mapa_tipos[$indice_predicho] ?? 'comida-rapida';
    }

    public static function obtenerRecomendacionesParaCliente($id_usuario_cliente)
    {
        $cliente = UsuarioCliente::with(['preferencias', 'reservas.restaurante'])
            ->find($id_usuario_cliente);

        if (!$cliente) {
            return ['error' => 'Usuario cliente no encontrado'];
        }

        $recomendador = new self();
        
        // Normalizar datos del cliente
        $datos_normalizados = self::normalizarDatosCliente($cliente);
        
        // Predecir tipo de restaurante preferido
        $tipo_predicho = $recomendador->predecirTipoRestaurante($datos_normalizados);
        
        // Obtener top 5 restaurantes
        $top_restaurantes = self::obtenerTop5Restaurantes();
        
        // Obtener restaurantes del tipo predicho
        $restaurantes_tipo_predicho = self::obtenerRestaurantesPorTipo($tipo_predicho);

        // Combinar sin duplicados
        $todos_ids = [];
        $recomendaciones_finales = [];
        
        // Agregar top 5 primero
        foreach ($top_restaurantes as $restaurante) {
            if (!in_array($restaurante->id, $todos_ids)) {
                $todos_ids[] = $restaurante->id;
                $recomendaciones_finales[] = $restaurante;
            }
        }
        
        // Agregar restaurantes del tipo predicho
        foreach ($restaurantes_tipo_predicho as $restaurante) {
            if (!in_array($restaurante->id, $todos_ids) && count($recomendaciones_finales) < 10) {
                $todos_ids[] = $restaurante->id;
                $recomendaciones_finales[] = $restaurante;
            }
        }

        return [
            'recomendaciones' => $recomendaciones_finales,
            'tipo_predicho' => $tipo_predicho,
            'top_5_restaurantes' => $top_restaurantes,
            'restaurantes_tipo_predicho' => $restaurantes_tipo_predicho,
            'datos_normalizados' => $datos_normalizados
        ];
    }

    private static function normalizarDatosCliente($cliente)
    {
        $preferencia = $cliente->preferencias;
        $ultima_reserva = $cliente->reservas()
            ->where('estado_reserva', 'completada')
            ->orderBy('fecha_reserva', 'desc')
            ->first();

        // Mapeo de tipos a números
        $tipos_map = [
            'comida-tradicional' => 1, 'parrilla' => 2, 'comida-rapida' => 3,
            'italiana' => 4, 'china' => 5, 'internacional' => 6,
            'postres' => 7, 'bebidas' => 8
        ];

        // 1. Tipo de restaurante preferencia (normalizado 0-1)
        $tipo_pref_num = $preferencia ? 
            ($tipos_map[$preferencia->tipo_restaurante_preferencia] ?? 3) : 3;
        $tipo_pref_normalizado = ($tipo_pref_num - 1) / 7; // 0-1

        // 2. Calificación mínima preferencia (ya está 0-5, dividir por 5)
        $calificacion_min_norm = $preferencia ? 
            ($preferencia->calificacion_minima_preferencia / 5.0) : 0.6;

        // 3. Precio máximo preferencia (normalizar por rango esperado 50-500)
        $precio_max_norm = $preferencia ? 
            min(($preferencia->precio_maximo_preferencia - 50) / 450, 1.0) : 0.5;

        // 4. Precio última reserva (normalizar por rango 10-300)
        $precio_ultima_norm = $ultima_reserva ? 
            min(($ultima_reserva->precio_reserva - 10) / 290, 1.0) : 0.3;

        // 5. Personas última reserva (normalizar por rango 1-8)
        $personas_ultima_norm = $ultima_reserva ? 
            min(($ultima_reserva->personas_reserva - 1) / 7, 1.0) : 0.25;

        // 6. Frecuencia de reservas (normalizar por rango 0-20)
        $frecuencia = $cliente->reservas()->where('estado_reserva', 'completada')->count();
        $frecuencia_norm = min($frecuencia / 20, 1.0);

        return [
            $tipo_pref_normalizado,
            $calificacion_min_norm,
            $precio_max_norm,
            $precio_ultima_norm,
            $personas_ultima_norm,
            $frecuencia_norm
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
                $restaurante->qr_pago_base64 = $restaurante->obtenerQrPagoBase64();
                return $restaurante;
            });
    }

    private static function obtenerRestaurantesPorTipo($tipo)
    {
        return UsuarioRestaurante::with('usuario')
            ->where('tipo_restaurante', $tipo)
            ->where('calificacion', '>=', 3.0)
            ->orderBy('calificacion', 'desc')
            ->take(5)
            ->get()
            ->map(function($restaurante) {
                $restaurante->imagen_base64 = $restaurante->obtenerImagenBase64();
                $restaurante->qr_pago_base64 = $restaurante->obtenerQrPagoBase64();
                return $restaurante;
            });
    }

    public function generarCsvParaEntrenamiento()
    {
        $clientes = UsuarioCliente::with(['preferencias', 'reservas'])
            ->whereHas('reservas', function($query) {
                $query->where('estado_reserva', 'completada');
            })
            ->get();

        $datos_csv = [];
        $datos_csv[] = [
            'id_usuario_cliente',
            'tipo_restaurante_preferencia_numerico',
            'calificacion_minima_preferencia', 
            'precio_maximo_preferencia',
            'precio_ultima_reserva',
            'personas_ultima_reserva',
            'frecuencia_reservas'
        ];

        foreach ($clientes as $cliente) {
            $datos_norm = self::normalizarDatosCliente($cliente);
            $datos_csv[] = array_merge([$cliente->id], $datos_norm);
        }

        $contenido = '';
        foreach ($datos_csv as $fila) {
            $contenido .= implode(',', $fila) . "\n";
        }

        $nombre_archivo = 'datos_entrenamiento_' . date('Y_m_d_H_i_s') . '.csv';
        file_put_contents(public_path($nombre_archivo), $contenido);

        return public_path($nombre_archivo);
    }
}