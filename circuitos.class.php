<?php
require_once "DataObject.class.php";
require_once "config.php";


class Circuito extends DataObject {
    protected $data = array(
        "id_circuito" => "",
        "nombre_circuito" => "",
        "web_circuito" => "",
        "direccion_circuito" => "",
        "localidad_circuito" => "",
        "telefono_circuito" => "",
        "area_id" => "",
        "nombre_area" => "",
        "altitud" => "",
        "longitud" => "",
        "curvasizd" => "",
        "curvasdcha" => "",
        "velocidadmax" => "",
        "silueta" => ""
    );


    public static function getCircuitos($startRow, $numRows, $order, $search = "") {

        $conn = parent::connect();
        if (!$conn) return [[], 0]; // Retorno consistente
        
        // Limpieza de seguridad para el ORDER BY
        $order = preg_replace("/[^a-zA-Z0-9\s_]/", "", $order);
        if (empty($order)) $order = "id_circuito ASC";
        
        // Lógica del buscador
        $whereClause = "";
        if (!empty($search)) {
            // Buscamos en nombre o apellido
            $whereClause = " WHERE nombre_circuito LIKE :search";
        }
        
        try {
            // --- PRIMERA CONSULTA: Obtener el total de filas (COUNT) ---
            $sqlCount = "SELECT COUNT(*) FROM " . VIEW_CIRCUITOS . " $whereClause";
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
            $sqlData = "SELECT * FROM " . VIEW_CIRCUITOS . " $whereClause ORDER BY $order LIMIT :startRow, :numRows";
            
            $stData = $conn->prepare($sqlData);
            
            if (!empty($search)) {
                $stData->bindValue(":search", "%" . $search . "%", PDO::PARAM_STR);
            }

            $stData->bindValue(":startRow", (int)$startRow, PDO::PARAM_INT);
            $stData->bindValue(":numRows", (int)$numRows, PDO::PARAM_INT);
            $stData->execute();
            $circuitos = array();
            foreach ($stData->fetchAll() as $row) {
                $circuitos[] = new Circuito($row);
            }

            parent::disconnect($conn);
            return array($circuitos, $totalRows);

        } catch (PDOException $e) {
            parent::disconnect($conn);
            error_log("Error en getCircuitos: " . $e->getMessage());
            return [[], 0]; // Devolvemos array vacío en caso de error
        }
    }

    public static function getCircuito($id_circuito) {
        $conn = parent::connect();
        if (!$conn) return null;

        $sql = "SELECT * FROM " . VIEW_CIRCUITOS . " WHERE id_circuito = :id_circuito";

        try {
            $st = $conn->prepare($sql);
            $st->bindValue(":id_circuito", (int)$id_circuito, PDO::PARAM_INT);
            $st->execute();
            $row = $st->fetch();
            parent::disconnect($conn);
            if ($row) {
                return new Circuito($row);
            } else {
                return null;
            }
        } catch (PDOException $e) {
            parent::disconnect($conn);
            error_log("Error en getCircuito: " . $e->getMessage());
            return null;
            }
    }



}
?>
