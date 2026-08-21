<?php
require_once "DataObject.class.php";
require_once "config.php";

class Patrocinador extends DataObject {
    protected $data = array(
        "id_patrocinador"       => "",
        "nombre_patrocinador"   => "",
        "logo_patrocinador"     => "",
        "web_patrocinador"      => "",
        "id_pais"               => "",
        "nombre_pais"           => "",
        "codigo_iso"            => ""
    );

    public static function getPatrocinadores($startRow = 0, $numRows = 20, $order = "id_patrocinador ASC", $search = ""): array {
        $conn = parent::connect();
        if (!$conn) return [[], 0];

        // 1. Limpieza de seguridad para el ORDER BY
        $orderClean = preg_replace("/[^a-zA-Z0-9\s_]/", "", $order);
        if (empty($orderClean)) {
            $orderClean = "id_patrocinador ASC";
        }

        // 2. Lógica del buscador (nombre de la escudería o nombre del país)
        $whereClause = "";
        $hasSearch = !empty(trim($search));
        if ($hasSearch) {
            $whereClause = " WHERE (nombre_patrocinador LIKE :search
                                OR nombre_pais LIKE :search)";
        }

        // 3. Consulta SQL con paginación optimizada
        $tablaVista = defined('VIEW_PATROCINADORES') ? VIEW_PATROCINADORES : 'vista_patrocinadores';
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

            // Convertimos cada fila en un objeto Patrocinador
            $patrocinadores = array();
            foreach ($rows as $row) {
                $patrocinadores[] = new static($row);
            }

            return array($patrocinadores, (int)$totalRows);

        } catch (PDOException $e) {
            parent::disconnect($conn);
            error_log("Error en getPatrocinadores: " . $e->getMessage());
            return [[], 0];
        }
    }

}
?>
