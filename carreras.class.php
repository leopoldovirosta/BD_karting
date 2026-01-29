<?php
require_once "DataObject.class.php";
require_once "config.php";


class Carrera extends DataObject {
    protected $data = array(
        "id_carrera" => "",
        "id_cto" => "",
        "nombre_cto" => "",
        "fecha_carrera" => "",
        "dia" => "",
        "num_vueltas" => "",
        "temperatura" => "",
        "humedad" => "",
        "presion" => "",
        "viento" => "",
        "orientacion" => "",
        "tasfalto" => "",
        "pista" => "",
        "nombre_carrera_tipo" => "",
        "id_circuito" => "",
        "nombre_circuito" => ""
    );


    public static function getCarreras($startRow, $numRows, $order) {

        $conn = parent::connect();
        if (!$conn) return [[], 0]; // Retorno consistente
        
        // Limpieza de seguridad para el ORDER BY
        $order = preg_replace("/[^a-zA-Z0-9\s_]/", "", $order);
        if (empty($order)) $order = "id_carrera ASC";
        
        try {
            // --- PRIMERA CONSULTA: Obtener el total de filas (COUNT) ---
            $sqlCount = "SELECT COUNT(*) FROM " . VIEW_CARRERAS;
            $stCount = $conn->prepare($sqlCount);
            $stCount->execute();
            $totalRows = $stCount->fetchColumn();

            // Si el conteo es 0, podemos terminar aquí para ahorrar recursos
            if ($totalRows == 0) {
                parent::disconnect($conn);
                return [[], 0];
            }

            // --- SEGUNDA CONSULTA: Obtener los datos reales (SELECT *) ---
            $sqlData = "SELECT * FROM " . VIEW_CARRERAS . " ORDER BY $order LIMIT :startRow, :numRows";
            
            $stData = $conn->prepare($sqlData);
            
            $stData->bindValue(":startRow", (int)$startRow, PDO::PARAM_INT);
            $stData->bindValue(":numRows", (int)$numRows, PDO::PARAM_INT);
            $stData->execute();
            $carreras = array();
            foreach ($stData->fetchAll() as $row) {
                $carreras[] = new Carrera($row);
            }

            parent::disconnect($conn);
            return array($carreras, (int)$totalRows);

        } catch (PDOException $e) {
            parent::disconnect($conn);
            error_log("Error en getCarreras: " . $e->getMessage());
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
        $sql = "SELECT nombre_piloto, apellido_piloto, foto_piloto, COUNT(*) as victorias 
                FROM " . VIEW_RESULTADOS . " 
                WHERE id_circuito = :id_circuito AND posicion = 1 AND id_categoria=1
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

}




?>
