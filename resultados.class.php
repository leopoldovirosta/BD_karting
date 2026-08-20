<?php
require_once "DataObject.class.php";
require_once "config.php";


class Resultado extends DataObject {
    protected $data = array(
        // Resultado
        "id_resultado" =>  "",
        "id_carrera" => "",
        "fecha_carrera" => "",
        "id_carrera_tipo" => "",
        "nombre_carrera_tipo" => "",
        
        // Campeonato / Edición / Categoría
        "id_categoria" => "",
        "nombre_categoria" => "",
        "id_edicion" => "",
        "anio_edicion" => "",
        "id_cto" => "",
        "nombre_cto" => "",

        // Circuito
        "id_circuito" => "",
        "nombre_circuito" => "",
        "longitud" => "",
        
        // Piloto
        "id_piloto" => "",
        "nombre_piloto" => "",
        "apellido_piloto" => "",
        "foto_piloto" => "",
        "dorsal" => "",

        // Desempeño y puntuacion
        "tiempo_total" => "",
        "mejor_vuelta" => "",
        "num_vueltas" => "",
        "num_vueltas_completadas" => "",
        "posicion" => "",
        "comentario_posicion" => "",
        "puntos" => "",

        // Material
        "id_chasis" => "",
        "marca_chasis" => "",
        "modelo_chasis" => "",
        "id_motor" => "",
        "marca_motor" => "",
        "modelo_motor" => "",
        "id_rueda" => "",
        "marca_rueda" => "",
        "modelo_rueda" => ""
    );


    public static function getResultados($startRow, $numRows, $order) {

        $conn = parent::connect();
        if (!$conn) return [[], 0]; // Retorno consistente
        
        // Limpieza de seguridad para el ORDER BY
        $order = preg_replace("/[^a-zA-Z0-9\s_]/", "", $order);
        if (empty($order)) $order = "id_resultado DESC";
        
        try {
            // --- PRIMERA CONSULTA: Obtener el total de filas (COUNT) ---
            $sqlCount = "SELECT COUNT(*) FROM " . VIEW_RESULTADOS;
            $stCount = $conn->prepare($sqlCount);
            $stCount->execute();
            $totalRows = $stCount->fetchColumn();

            // Si el conteo es 0, podemos terminar aquí para ahorrar recursos
            if ($totalRows == 0) {
                parent::disconnect($conn);
                return [[], 0];
            }

            // --- SEGUNDA CONSULTA: Obtener los datos reales (SELECT *) ---
            $sqlData = "SELECT * FROM " . VIEW_RESULTADOS . " ORDER BY $order LIMIT :startRow, :numRows";
            
            $stData = $conn->prepare($sqlData);
            
            $stData->bindValue(":startRow", (int)$startRow, PDO::PARAM_INT);
            $stData->bindValue(":numRows", (int)$numRows, PDO::PARAM_INT);
            $stData->execute();
            $resultados = array();
            foreach ($stData->fetchAll() as $row) {
                $resultados[] = new Resultado($row);
            }

            parent::disconnect($conn);
            return array($resultados, (int)$totalRows);

        } catch (PDOException $e) {
            parent::disconnect($conn);
            error_log("Error en getResultados: " . $e->getMessage());
            return [[], 0]; // Devolvemos array vacío en caso de error
        }
    }

    public static function getResultado($id_resultado, $id_piloto, $id_edicion) {
        $conn = parent::connect();
        if (!$conn) return null;

        $sql = "SELECT * FROM " . VIEW_RESULTADOS . " WHERE id_resultado = :id_resultado AND id_piloto = :id_piloto AND id_edicion = :id_edicion";

        try {
            $st = $conn->prepare($sql);
            $st->bindValue(":id_resultado", (int)$id_resultado, PDO::PARAM_INT);
            $st->bindValue(":id_piloto", (int)$id_piloto, PDO::PARAM_INT);
            $st->bindValue(":id_edicion", (int)$id_edicion, PDO::PARAM_INT);
            $st->execute();
            $row = $st->fetch(PDO::FETCH_ASSOC);
            parent::disconnect($conn);
            return ($row) ? new Resultado($row) : null;
            
        } catch (PDOException $e) {
            parent::disconnect($conn);
            error_log("Error en getResultado: " . $e->getMessage());
            return null;
            }
    }



    public static function getEstadisticasGanadores($id_circuito) {
        $conn = parent::connect();
        if (!$conn) return null;
        // Esta consulta cuenta cuántas veces ha ganado cada piloto en este circuito
        $sql = "SELECT id_piloto, nombre_piloto, apellido_piloto, foto_piloto, COUNT(DISTINCT id_carrera) as victorias
                FROM " . VIEW_RESULTADOS . "
                WHERE id_circuito = :id_circuito AND posicion = 1 AND nombre_carrera_tipo != 'Clasificacion'
                GROUP BY id_piloto
                ORDER BY victorias DESC
                LIMIT 3"; // Top 3 ganadores históricos

        try {
            $st = $conn->prepare($sql);
            $st->bindValue(":id_circuito", (int)$id_circuito, PDO::PARAM_INT);
            $st->execute();
            return $st->fetchAll();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public static function getRecordVuelta($id_circuito) {
        $conn = parent::connect();
        // Buscamos el tiempo más rápido, su piloto y el año en que ocurrió
        $sql = "SELECT p.nombre_piloto, p.apellido_piloto, r.mejor_vuelta, c.fecha_carrera
                FROM " . VIEW_RESULTADOS . " r
                JOIN " . VIEW_PILOTOS . " p ON r.id_piloto = p.id_piloto
                JOIN " . VIEW_CARRERAS . " c ON r.id_carrera = c.id_carrera
                WHERE c.id_circuito = :id_circuito 
                AND r.mejor_vuelta > 0 
                ORDER BY r.mejor_vuelta ASC
                LIMIT 1";

        try {
            $st = $conn->prepare($sql);
            $st->bindValue(":id_circuito", (int)$id_circuito, PDO::PARAM_INT);
            $st->execute();
            $record = $st->fetch();
            parent::disconnect($conn);
            return $record;
        } catch (PDOException $e) {
            parent::disconnect($conn);
            return null;
        }
    }

    public static function calcularVelocidadMedia($longitud_m, $tiempo_str) {
        if (empty($tiempo_str) || $longitud_m <= 0) return 0;

        // Limpiamos posibles espacios y separamos por ":"
        $partes = explode(':', trim($tiempo_str));

        // 2. Procesamos según lo que recibamos (Horas:Minutos:Segundos)
        if (count($partes) === 3) {
                $horas = (int)$partes[0];
                $minutos = (int)$partes[1];
                $segundos = (float)$partes[2]; // Esto captura los decimales (.500)

                $segundos_totales = ($horas * 3600) + ($minutos * 60) + $segundos;
        } else {
            return 0; // Formato inesperado
        }

        if ($segundos_totales <= 0) return 0;
        // Fórmula: (Distancia / Tiempo) * 3600 para pasar de km/s a km/h
        $longitud_km = $longitud_m / 1000;
        $velocidad = ($longitud_km / $segundos_totales) * 3600;
        return round($velocidad, 2);    
    
    }

    /**
    * Calcula la velocidad media en km/h
    * 
    * @param float $longitudCircuitoKm Longitud del circuito en kilómetros (ej: 1.4)
    * @param int $vueltas Número de vueltas completadas
    * @param string $tiempoTotal Tiempo en formato "HH:MM:SS" o "MM:SS.mmm"
    * @return float Velocidad media en km/h (redondeada a 2 decimales)
    */
    public static function calcularVelocidadMediaCarrera($longitudMetros, $vueltas, $tiempoTotal) {
        if ($vueltas <= 0 || $longitudMetros <= 0 || empty($tiempoTotal)) {
            return 0.0;
        }

        // 1. Distancia total en km
        $distanciaTotalKm = $vueltas * ($longitudMetros / 1000);

        // 2. Convertir HH:MM:SS.mmm a segundos
        $partes = explode(':', $tiempoTotal);
        if (count($partes) === 3) {
            // Formato HH:MM:SS.mmm
            $segundos = ($partes[0] * 3600) + ($partes[1] * 60) + (float)$partes[2];
        } elseif (count($partes) === 2) {
            // Formato MM:SS.mmm
            $segundos = ($partes[0] * 60) + (float)$partes[1];
        } else {
            return 0.0;
        }

            if ($segundos <= 0) return 0.0;

        // 3. Convertir segundos a horas y calcular km/h
        $horas = $segundos / 3600;
        $velocidadMedia = $distanciaTotalKm / $horas;

        return round($velocidadMedia, 2);
    }

    /**
    * Quita el prefijo "00:" de un tiempo en formato HH:MM:SS.mmm
    * Ejemplo: "00:13:09.234" -> "13:09.234"
    * Ejemplo: "01:13:09.234" -> "01:13:09.234" (Se mantiene si dura 1h o más)
    */
    public static function formatearTiempo($tiempo) {
        if (empty($tiempo)) return '---';

        // Si empieza por "00:", se los quitamos
        return preg_replace('/^00:/', '', trim($tiempo));
    }


    /**
     * Obtiene resultados filtrados dinámicamente con soporte para paginación y ordenación.
     * 
     * @param int $startRow Índice de inicio para la paginación (LIMIT)
     * @param int $numRows Número de filas a recuperar
     * @param string $order Campo y sentido de ordenación (ej: "nombre_piloto ASC")
     * @param array $filtros Array asociativo con las condiciones de búsqueda
     * @return array [Array de objetos Resultado, int Total de filas que coinciden]
    */
    public static function getResultadosFiltrados($startRow, $numRows, $order = "id_resultado DESC", array $filtros = []) {
        $conn = parent::connect();
        if (!$conn) return [[], 0];

        $tablaVista = defined('VIEW_RESULTADOS') ? VIEW_RESULTADOS : 'vista_resultados';
        $whereClauses = [];
        $params = [];

        // 1. Filtro por Edición / Campeonato
        if (isset($filtros['id_edicion']) && (int)$filtros['id_edicion'] > 0) {
            $whereClauses[] = "id_edicion = :id_edicion";
            $params[':id_edicion'] = (int)$filtros['id_edicion'];
        }

        // 2. Filtro por Carrera
        if (isset($filtros['id_carrera']) && (int)$filtros['id_carrera'] > 0) {
            $whereClauses[] = "id_carrera = :id_carrera";
            $params[':id_carrera'] = (int)$filtros['id_carrera'];
        }

        // 3. Filtro por Piloto
        if (!empty($filtros['piloto'])) {
            $whereClauses[] = "(nombre_piloto LIKE :piloto OR apellido_piloto LIKE :piloto)";
            $params[':piloto'] = '%' . trim($filtros['piloto']) . '%';
        }

        // 4. Filtro por Categoría
        if (!empty($filtros['id_categoria'])) {
            $whereClauses[] = "id_categoria = :id_categoria";
            $params[':id_categoria'] = (int)$filtros['id_categoria'];
        }

        // 5. Filtro por Posición
        if (!empty($filtros['posicion'])) {
            if ($filtros['posicion'] === 'top3') {
                $whereClauses[] = "posicion <= 3 AND posicion > 0";
            } elseif ($filtros['posicion'] === '1') {
                $whereClauses[] = "posicion = 1";
            }
        }

        // 6. Filtro por Chasis
        if (!empty($filtros['chasis'])) {
            $whereClauses[] = "marca_chasis LIKE :chasis";
            $params[':chasis'] = '%' . trim($filtros['chasis']) . '%';
        }

        // 7. Filtro por Motor
        if (!empty($filtros['motor'])) {
            $whereClauses[] = "marca_motor LIKE :motor";
            $params[':motor'] = '%' . trim($filtros['motor']) . '%';
        }


        $sqlWhere = (count($whereClauses) > 0) ? " WHERE " . implode(" AND ", $whereClauses) : "";

        // Limpieza de seguridad del ordenamiento
        $orderClean = preg_replace("/[^a-zA-Z0-9\s_]/", "", $order);
        // Si se pide ordenar por apellido, añadimos el nombre como criterio secundario
        if (strpos($orderClean, 'apellido_piloto') !== false) {
            // Si viene "apellido_piloto ASC", añadimos ", MIN(nombre_piloto) ASC"
            $sentido = (strpos($orderClean, 'DESC') !== false) ? 'DESC' : 'ASC';
            $orderClean = "apellido_piloto {$sentido}, nombre_piloto {$sentido}";
        } elseif (empty($orderClean)) {
            $orderClean = "id_resultado DESC";
        }
    try {
            // 1. CONTEO DE RESULTADOS ÚNICOS
            $sqlCount = "SELECT COUNT(*) FROM {$tablaVista} {$sqlWhere}";
            $stCount = $conn->prepare($sqlCount);
            foreach ($params as $param => $val) {
                $stCount->bindValue($param, $val);
            }
            
            $stCount->execute();
            $totalRows = (int)$stCount->fetchColumn();

            if ($totalRows === 0) {
                parent::disconnect($conn);
                return [[], 0];
            }

            // 2. CONSULTA DE DATOS COMPATIBLE CON ONLY_FULL_GROUP_BY
            // Se obtienen primero los IDs únicos filtrados y luego sus registros completos
            $sqlData = "SELECT 
                            id_resultado,
                            MIN(nombre_circuito) AS nombre_circuito,
                            MIN(nombre_piloto) AS nombre_piloto,
                            MIN(apellido_piloto) AS apellido_piloto,
                            MIN(nombre_categoria) AS nombre_categoria,
                            MIN(posicion) AS posicion,
                            MIN(marca_chasis) AS marca_chasis,
                            MIN(marca_motor) AS marca_motor,
                            MIN(id_piloto) AS id_piloto,
                            MIN(id_edicion) AS id_edicion
                        FROM {$tablaVista}
                        {$sqlWhere}
                        GROUP BY id_resultado
                        ORDER BY {$orderClean}
                        LIMIT :startRow, :numRows";

            $stData = $conn->prepare($sqlData);

            foreach ($params as $param => $val) {
                $stData->bindValue($param, $val);
            }

            $stData->bindValue(":startRow", (int)$startRow, PDO::PARAM_INT);
            $stData->bindValue(":numRows", (int)$numRows, PDO::PARAM_INT);

            $stData->execute();

            $resultados = [];
            foreach ($stData->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $resultados[] = new Resultado($row);
            }

            parent::disconnect($conn);
            return [$resultados, $totalRows];

        } catch (Throwable $e) {
            parent::disconnect($conn);
            echo "<div style='color:red; background:#fee; padding:10px; border:1px solid red;'>";
            echo "<strong>Error SQL/PHP:</strong> " . htmlspecialchars($e->getMessage());
            echo "</div>";
            return [[], 0];
        }
    }



    /**
    * Genera la URL para ordenar una columna manteniendo los filtros activos.
    * Método específico para alternar el sentido de la ordenación (ASC/DESC) sobre las columnas de la tabla.
    */
    public static function buildSortUrl($orderField, $currentOrder, $currentType) {
        // Copiamos todos los parámetros GET actuales (filtros incluidos)
        $params = $_GET;

        // Actualizamos los parámetros de ordenación
        $params['order'] = $orderField;
        
        // Si ya estamos ordenando por esa columna, alternamos ASC / DESC
        if ($currentOrder === $orderField) {
            $params['type'] = ($currentType === 'ASC') ? 'DESC' : 'ASC';
        } else {
            $params['type'] = 'ASC';
        }

        // Reiniciamos a la primera página para no quedar en una página inexistente
        $params['start'] = 0;

        return 'view_resultados.php?' . http_build_query($params);

    }

    /**
    * Método genérico que fusiona cualquier parámetro modificado (como la paginación con start) con los filtros de $_GET.
    */
    public static function buildUrl(array $newParams = []) {
        // Tomamos todos los parámetros GET actuales
        $params = $_GET;

        // Sobrescribimos o añadimos los parámetros deseados (como 'start')
        foreach ($newParams as $key => $value) {
            if ($value === null) {
                unset($params[$key]);
            } else {
                $params[$key] = $value;
            }
        }

        return 'view_resultados.php?' . http_build_query($params);
    }




}
?>
