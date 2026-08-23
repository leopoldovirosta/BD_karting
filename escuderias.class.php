<?php
require_once "DataObject.class.php";
require_once "config.php";

class Escuderia extends DataObject {
    protected $data = array(
        "id_escuderia"       => "",
        "nombre_escuderia"   => "",
        "logo_escuderia"     => "",
        "web_escuderia"      => "",
        "activa"             => "",
        "id_pais"            => "",
        "nombre_pais"        => "",
        "codigo_iso"         => ""
    );

    public static function getEscuderias($startRow = 0, $numRows = 20, $order = "id_escuderia ASC", $search = ""): array {
        $conn = parent::connect();
        if (!$conn) return [[], 0];

        // 1. Limpieza de seguridad para el ORDER BY
        $orderClean = preg_replace("/[^a-zA-Z0-9\s_]/", "", $order);
        if (empty($orderClean)) {
            $orderClean = "id_escuderia ASC";
        }

        // 2. Lógica del buscador (nombre de la escudería o nombre del país)
        $whereClause = " WHERE activa = 1";
        $hasSearch = !empty(trim($search));
        if ($hasSearch) {
            $whereClause = " WHERE (nombre_escuderia LIKE :search
                                OR nombre_pais LIKE :search)";
        }

        // 3. Consulta SQL con paginación optimizada
        $tablaVista = defined('VIEW_ESCUDERIAS') ? VIEW_ESCUDERIAS : 'vista_escuderias';
        $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM {$tablaVista} {$whereClause} 
                ORDER BY {$orderClean} 
                LIMIT :startRow, :numRows";

        try {
            $st = $conn->prepare($sql);

            // Vincular la búsqueda si existe
            if ($hasSearch) {
                $st->bindValue(":search", '%' . trim($search) . '%', PDO::PARAM_STR);
            }

            // Vincular parámetros de paginación
            $st->bindValue(":startRow", (int)$startRow, PDO::PARAM_INT);
            $st->bindValue(":numRows", (int)$numRows, PDO::PARAM_INT);
            $st->execute();

            $rows = $st->fetchAll(PDO::FETCH_ASSOC);

            // Obtener el total de filas encontradas
            $totalRows = $conn->query("SELECT FOUND_ROWS()")->fetchColumn();

            parent::disconnect($conn);

            // Convertimos cada fila en un objeto Escuderia
            $escuderias = array();
            foreach ($rows as $row) {
                $escuderias[] = new static($row);
            }

            return array($escuderias, (int)$totalRows);

        } catch (PDOException $e) {
            parent::disconnect($conn);
            error_log("Error en getEscuderias: " . $e->getMessage());
            return [[], 0];
        }
    }

}
?>
