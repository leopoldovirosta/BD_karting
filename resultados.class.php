<?php
require_once "DataObject.class.php";
require_once "config.php";


class Resultado extends DataObject {
    protected $data = array(
        "id_resultado" =>  "",
        "id_carrera" => "",
        "id_categoria" => "",
        "fecha_carrera" => "",
        "nombre_carrera_tipo" => "",
        "id_categoria" => "",
        "nombre_categoria" => "",
        "id_circuito" => "",
        "nombre_circuito" => "",
        "id_piloto" => "",
        "nombre_piloto" => "",
        "apellido_piloto" => "",
        "foto_piloto" => "",
        "dorsal" => "",
        "id_cto" => "",
        "nombre_cto" => "",
        "tiempo_total" => "",
        "mejor_vuelta" => "",
        "num_vueltas" => "",
        "num_vueltas_completadas" => "",
        "posicion" => "",
        "comentario_posicion" => "",
        "puntos" => "",
        "marca_chasis" => "",
        "modelo_chasis" => "",
        "marca_motor" => "",
        "modelo_motor" => "",
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

    public static function getCarrera($id_carrera, $id_cto) {
        $conn = parent::connect();
        if (!$conn) return null;

        $sql = "SELECT * FROM " . VIEW_CARRERAS . " WHERE id_carrera = :id_carrera AND id_cto = :id_cto";

        try {
            $st = $conn->prepare($sql);
            $st->bindValue(":id_carrera", (int)$id_carrera, PDO::PARAM_INT);
            $st->bindValue(":id_cto", (int)$id_cto, PDO::PARAM_INT);
            $st->execute();
            $row = $st->fetch();
            parent::disconnect($conn);
            return ($row) ? new Carrera($row) : null;
            
        } catch (PDOException $e) {
            parent::disconnect($conn);
            error_log("Error en getCarrera: " . $e->getMessage());
            return null;
            }
    }

    public static function getEstadisticasGanadores($id_circuito) {
        $conn = parent::connect();
        if (!$conn) return null;
        // Esta consulta cuenta cuántas veces ha ganado cada piloto en este circuito
        $sql = "SELECT nombre_piloto, apellido_piloto, foto_piloto, COUNT(DISTINCT id_carrera) as victorias
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

}

?>
