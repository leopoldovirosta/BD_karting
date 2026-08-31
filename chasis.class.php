<?php
require_once "DataObject.class.php";
require_once "config.php";

class Chasis extends DataObject {

    protected $data = array(
        "id_chasis"         => "",
        "id_marca"          => "",
        "nombre_marca"      => "",
        "logo_marca"        => "",
        "pagina_web"        => "",
        "id_pais"           => "",
        "nombre_pais"       => "",
        "codigo_iso"        => "",
        "modelo_chasis"     => "",
        "material"          => "",
        "tubo_diametro"     => "",
        "distancia_ejes"    => "",
        "eje_trasero"       => "",
        "sistema_frenado"   => "",
        "categoria_objetivo"=> "",
        "ano"               => "",
        "homologacion"      => "",
        "url_homologacion"  => "",
        "foto_chasis"       => ""
    );

    public static function getChasis($start = 0, $pageSize = 20, $order = "id_chasis ASC", $search = ""): array {
        $conn = parent::connect();
        if (!$conn) return [[], 0];

        // 1. Limpieza de seguridad para el ORDER BY
        $orderClean = preg_replace("/[^a-zA-Z0-9\s_]/", "", $order);
        if (empty($orderClean)) {
            $orderClean = "id_chasis ASC";
        }
    
        // 2. Lógica del buscador (modelo o marca)
        $whereClause = "WHERE modelo_chasis != 'N/D' ";
        $hasSearch = !empty(trim($search));
        if ($hasSearch) {
            $whereClause .= " AND (modelo_chasis LIKE :search
                                OR nombre_marca LIKE :search
                                OR categoria_objetivo LIKE :search
                                OR ano LIKE :search
                                )";
        }

        // 3. Consulta SQL con paginación
        $tablaVista = defined('VIEW_CHASIS') ? VIEW_CHASIS : 'vista_chasis';
        $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM {$tablaVista} {$whereClause} 
                ORDER BY {$orderClean} 
                LIMIT :start, :pageSize";

        try {
            $st = $conn->prepare($sql);

            // Vincular la búsqueda si existe
            if ($hasSearch) {
                $st->bindValue(":search", '%' . trim($search) . '%', PDO::PARAM_STR);
            }

            // Vincular obligatoriamente los parámetros de la paginación como INT
            $st->bindValue(":start", (int)$start, PDO::PARAM_INT);
            $st->bindValue(":pageSize", (int)$pageSize, PDO::PARAM_INT);
            $st->execute();

            $rows = $st->fetchAll(PDO::FETCH_ASSOC);
            
            // Obtener el total de filas encontradas para la paginación
            $totalRows = $conn->query("SELECT FOUND_ROWS()")->fetchColumn();

            parent::disconnect($conn);

            // Convertimos cada fila en un objeto Chasis
            $list = array();
            foreach ($rows as $row) {
                $list[] = new static($row);
            }

            return array($list, (int)$totalRows);

            } catch (PDOException $e) {
                parent::disconnect($conn);
                error_log("Error en getChasis: " . $e->getMessage());
                return array([], 0);
            }
        }



    public static function getChasisById($id_chasis) {
        $conn = parent::connect();
        if (!$conn) return null;

        $tablaVista = defined('VIEW_CHASIS') ? VIEW_CHASIS : 'vista_chasis';
        $sql = "SELECT * FROM {$tablaVista}  WHERE id_chasis = :id_chasis LIMIT 1";

        try {
            $st = $conn->prepare($sql);
            $st->bindValue(":id_chasis", (int)$id_chasis, PDO::PARAM_INT);
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
            error_log("Error en getChasisById: " . $e->getMessage());
            return null;
            }
    }

}
?>
