<?php
require_once "DataObject.class.php";
require_once "config.php";


class Piloto extends DataObject {
    protected $data = array(
        "id_piloto"              => "",
        "nombre_piloto"          => "",
        "apellido_piloto"        => "",
        "fecha_nacimiento"       => "",
        "web_piloto"             => "",
        "email_piloto"           => "",
        "foto_piloto"            => "",
        "id_pais"                => "",
        "nombre_pais"            => "",
        "codigo_iso"             => "",
        "id_escuderia"           => "",
        "nombre_escuderia"       => "",
        "id_patrocinador"        => "",
        "nombre_patrocinador"    => ""
    );


    public static function getPilotos($startRow, $numRows, $order, $search = "") {

        $conn = parent::connect();
        if (!$conn) return [[], 0]; // Retorno consistente
        
        // Limpieza de seguridad para el ORDER BY
        $order = preg_replace("/[^a-zA-Z0-9\s_]/", "", $order);
        if (empty($order)) $order = "id_piloto ASC";
        
        // Lógica del buscador
        $whereClause = "";
        if (!empty($search)) {
            // Buscamos en nombre o apellido
            $whereClause = " WHERE (nombre_piloto LIKE :search
                                OR apellido_piloto LIKE :search
                                OR nombre_escuderia LIKE :search
                                OR nombre_pais LIKE :search)";
        }
        
        try {
            // --- PRIMERA CONSULTA: Obtener el total de filas (COUNT) ---
            $sqlCount = "SELECT COUNT(*) FROM " . VIEW_PILOTOS . " $whereClause";
            $stCount = $conn->prepare($sqlCount);

            // Si hay búsqueda, vinculamos el parámetro con comodines %
            if (!empty($search)) {
                $stCount->bindValue(":search", "%" . $search . "%", PDO::PARAM_STR);
            }

            $stCount->execute();
            $totalRows = $stCount->fetchColumn();

            // Si el conteo es 0, podemos terminar aquí para ahorrar recursos
            if ($totalRows == 0) {
                parent::disconnect($conn);
                return [[], 0];
            }

            // --- SEGUNDA CONSULTA: Obtener los datos reales (SELECT *) ---
            $sqlData = "SELECT * FROM " . VIEW_PILOTOS . " $whereClause ORDER BY $order LIMIT :startRow, :numRows";
            
            $stData = $conn->prepare($sqlData);
            
            if (!empty($search)) {
                $stData->bindValue(":search", "%" . $search . "%", PDO::PARAM_STR);
            }

            $stData->bindValue(":startRow", (int)$startRow, PDO::PARAM_INT);
            $stData->bindValue(":numRows", (int)$numRows, PDO::PARAM_INT);
            $stData->execute();
            $pilotos = array();
            foreach ($stData->fetchAll() as $row) {
                $pilotos[] = new static($row);
            }

            parent::disconnect($conn);
            return array($pilotos, $totalRows);

        } catch (PDOException $e) {
            parent::disconnect($conn);
            error_log("Error en getPilotos: " . $e->getMessage());
            return [[], 0];
        }
    }

    public static function getPiloto($id_piloto) {
        $conn = parent::connect();
        if (!$conn) return null;

        $sql = "SELECT * FROM " . VIEW_PILOTOS . " WHERE id_piloto = :id_piloto";

        try {
            $st = $conn->prepare($sql);
            $st->bindValue(":id_piloto", (int)$id_piloto, PDO::PARAM_INT);
            $st->execute();
            $row = $st->fetch(PDO::FETCH_ASSOC);
            parent::disconnect($conn);

            if ($row) {
                return new static($row);
            } else {
                return null;
            }
        } catch (PDOException $e) {
            parent::disconnect($conn);
            error_log("Error en getPiloto: " . $e->getMessage());
            return null;
            }
    }

    // Funcion para buscar el siguiente piloto o anterior en vista piloto
    // ------------------------------------------------------------------
    public static function getNavegacionId($id_piloto_actual, $apellido_actual, $direccion_listado = 'ASC', $sentido = 'siguiente') {
        try {
            $conn = parent::connect();
            if (!$conn) return null;

            // 1. Normalizar dirección original del listado (ASC / DESC)
            $dirOriginal = (strtoupper($direccion_listado) === 'DESC') ? 'DESC' : 'ASC';
            $esSiguiente = ($sentido === 'siguiente');

            // 2. Definir operador y dirección SQL de forma limpia
            if ($esSiguiente) {
                $operador = ($dirOriginal === 'ASC') ? '>' : '<';
                $dirSQL   = $dirOriginal; // Mantiene ASC o DESC
            } else { // 'anterior'
                $operador = ($dirOriginal === 'ASC') ? '<' : '>';
                // Invertimos la ordenación para que LIMIT 1 capture el inmediatamente previo
                $dirSQL   = ($dirOriginal === 'ASC') ? 'DESC' : 'ASC';
            }

            // 3. Consulta SQL con Tupla (apellido_piloto, id_piloto)
            // MariaDB / MySQL evalúan el desempate por ID de forma nativa e impecable
            $sql = "SELECT id_piloto 
                    FROM " . VIEW_PILOTOS . "
                    WHERE (apellido_piloto, id_piloto) {$operador} (:apellido_actual, :id_actual)
                    ORDER BY apellido_piloto {$dirSQL}, id_piloto {$dirSQL} 
                    LIMIT 1";

            $st = $conn->prepare($sql);
            $st->bindValue(":apellido_actual", trim((string)$apellido_actual), PDO::PARAM_STR);
            $st->bindValue(":id_actual", (int)$id_piloto_actual, PDO::PARAM_INT);
            $st->execute();

            $row = $st->fetch(PDO::FETCH_ASSOC);
            parent::disconnect($conn);

            return $row ? (int)$row['id_piloto'] : null;

        } catch (Throwable $e) {
            parent::disconnect($conn);
            error_log("Error en getNavegacionId: " . $e->getMessage());
            return null;
        }
    }

    public static function getEstadisticasPiloto($idPiloto) {
        $conn = parent::connect();
        if (!$conn) return null;

        // Subconsulta para eliminar duplicados por id_carrera
        $sql = "SELECT 
                    /* Total de carreras (mangas) únicas disputadas */
                    COUNT(CASE WHEN id_carrera_tipo != 1 THEN 1 END) AS total_carreras,
                    
                    /* Victorias únicas (1 por evento/manga ganada) */
                    SUM(CASE WHEN posicion = 1 AND id_carrera_tipo != 1 THEN 1 ELSE 0 END) AS victorias,
                    
                    /* Podios únicos */
                    SUM(CASE WHEN posicion BETWEEN 1 AND 3 AND id_carrera_tipo != 1 THEN 1 ELSE 0 END) AS podios,
                    
                    /* Poles únicas */
                    SUM(CASE WHEN posicion = 1 AND id_carrera_tipo = 1 THEN 1 ELSE 0 END) AS poles,
                    
                    /* Mejor posición obtenida */
                    MIN(CASE WHEN id_carrera_tipo != 1 AND posicion > 0 THEN posicion END) AS mejor_resultado
                FROM (
                    -- Subconsulta que colapsa filas duplicadas de la misma carrera
                    SELECT DISTINCT 
                        id_carrera, 
                        id_carrera_tipo, 
                        posicion
                    FROM " . VIEW_RESULTADOS . "
                    WHERE id_piloto = :id_piloto
                ) AS resultados_unicos";

        try {
            $st = $conn->prepare($sql);
            $st->bindValue(":id_piloto", (int)$idPiloto, PDO::PARAM_INT);
            $st->execute();
            $stats = $st->fetch(PDO::FETCH_ASSOC);
            parent::disconnect($conn);
        return $stats;
        } catch (PDOException $e) {
            parent::disconnect($conn);
            error_log("Error en getEstadisticasPiloto: " . $e->getMessage());
            return null;
        }
    }

    public static function getVictoriasPiloto($idPiloto) {
        $conn = parent::connect();
        if (!$conn) return [];

        // Seleccionamos las carreras donde finalizó 1º (excluyendo la clasificación id_carrera_tipo = 1)
        $sql = "SELECT DISTINCT 
                    id_carrera,
                    nombre_cto,
                    nombre_circuito,
                    fecha_carrera,
                    nombre_carrera_tipo
                FROM " . VIEW_RESULTADOS . "
                WHERE id_piloto = :id_piloto 
                AND posicion = 1 
                AND id_carrera_tipo != 1
                ORDER BY fecha_carrera DESC";

        try {
            $st = $conn->prepare($sql);
            $st->bindValue(":id_piloto", (int)$idPiloto, PDO::PARAM_INT);
            $st->execute();
            $victorias = $st->fetchAll(PDO::FETCH_ASSOC);
            parent::disconnect($conn);
            return $victorias;
        } catch (PDOException $e) {
            parent::disconnect($conn);
            error_log("Error en getVictoriasPiloto: " . $e->getMessage());
            return [];
        }
    }

public static function getPolesPiloto($idPiloto) {
    $conn = parent::connect();
    if (!$conn) return [];

    // Seleccionamos las carreras donde fue 1º únicamente en la sesión de clasificación (id_carrera_tipo = 1)
    $sql = "SELECT DISTINCT 
                id_carrera,
                nombre_cto,
                nombre_circuito,
                fecha_carrera
            FROM " . VIEW_RESULTADOS . "
            WHERE id_piloto = :id_piloto 
              AND posicion = 1 
              AND id_carrera_tipo = 1
            ORDER BY fecha_carrera DESC";

    try {
        $st = $conn->prepare($sql);
        $st->bindValue(":id_piloto", (int)$idPiloto, PDO::PARAM_INT);
        $st->execute();
        $poles = $st->fetchAll(PDO::FETCH_ASSOC);
        parent::disconnect($conn);
        return $poles;
    } catch (PDOException $e) {
        parent::disconnect($conn);
        error_log("Error en getPolesPiloto: " . $e->getMessage());
        return [];
    }
}

    public static function getPodiosPiloto($idPiloto) {
        $conn = parent::connect();
        if (!$conn) return [];

        // Seleccionamos las carreras únicas donde quedó 2º o 3º
        $sql = "SELECT DISTINCT 
                    id_carrera,
                    nombre_cto,
                    nombre_circuito,
                    fecha_carrera,
                    nombre_carrera_tipo,
                    posicion
                FROM " . VIEW_RESULTADOS . "
                WHERE id_piloto = :id_piloto 
                AND posicion IN (2, 3)
                AND id_carrera_tipo != 1
                ORDER BY fecha_carrera DESC";

        try {
            $st = $conn->prepare($sql);
            $st->bindValue(":id_piloto", (int)$idPiloto, PDO::PARAM_INT);
            $st->execute();
            $podios = $st->fetchAll(PDO::FETCH_ASSOC);
            parent::disconnect($conn);
            return $podios;
        } catch (PDOException $e) {
            parent::disconnect($conn);
            error_log("Error en getPodiosPiloto: " . $e->getMessage());
            return [];
        }
    }

public static function getEstadisticasPorTemporada($idPiloto) {
    $conn = parent::connect();
    if (!$conn) return [];

    $sql = "SELECT 
                YEAR(fecha_carrera) AS temporada,
                COUNT(CASE WHEN id_carrera_tipo != 1 THEN 1 END) AS carreras,
                SUM(CASE WHEN posicion = 1 AND id_carrera_tipo != 1 THEN 1 ELSE 0 END) AS victorias,
                SUM(CASE WHEN posicion BETWEEN 1 AND 3 AND id_carrera_tipo != 1 THEN 1 ELSE 0 END) AS podios,
                SUM(CASE WHEN posicion = 1 AND id_carrera_tipo = 1 THEN 1 ELSE 0 END) AS poles
            FROM (
                SELECT DISTINCT 
                    id_carrera, 
                    id_carrera_tipo, 
                    posicion,
                    fecha_carrera
                FROM " . VIEW_RESULTADOS . "
                WHERE id_piloto = :id_piloto
            ) AS resultados_unicos
            GROUP BY YEAR(fecha_carrera)
            ORDER BY temporada DESC";

    try {
        $st = $conn->prepare($sql);
        $st->bindValue(":id_piloto", (int)$idPiloto, PDO::PARAM_INT);
        $st->execute();
        $progresion = $st->fetchAll(PDO::FETCH_ASSOC);
        parent::disconnect($conn);
        return $progresion;
    } catch (PDOException $e) {
        parent::disconnect($conn);
        error_log("Error en getEstadisticasPorTemporada: " . $e->getMessage());
        return [];
    }
}





}
?>
