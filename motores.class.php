<?php
require_once "DataObject.class.php";
require_once "config.php";

class Motor extends DataObject {

    protected $data = array(
        "id_motor"          => "",
        "id_marca"          => "",
        "nombre_marca"      => "",
        "logo_marca"        => "",
        "pagina_web"        => "",
        "id_pais"           => "",
        "nombre_pais"       => "",
        "codigo_iso"        => "",
        "modelo"            => "",
        "descripcion"       => "",
        "clase"             => "",
        "diametro"          => "",
        "max_diametro"      => "",
        "carrera"           => "",
        "cilindrada"        => "",
        "admision"          => "",
        "lubricacion"       => "",
        "lumbreras"         => "",
        "encendido"         => "",
        "regimen_max_rpm"   => "",
        "transmision"       => "",
        "refrigeracion"     => "",
        "starter"           => "",
        "homologacion"      => "",
        "url_homologacion"  => "",
        "peso"              => "",
        "carburador"        => "",
        "potencia_max"      => "",
        "par_motor_max"     => "",
        "foto_motor"        => ""
    );

    public static function getMotores($start = 0, $pageSize = 20, $order = "id_motor ASC", $search = ""): array {
        $conn = parent::connect();
        if (!$conn) return [[], 0];

        // 1. Limpieza de seguridad para el ORDER BY
        $orderClean = preg_replace("/[^a-zA-Z0-9\s_]/", "", $order);
        if (empty($orderClean)) {
            $orderClean = "id_motor ASC";
        }
    
        // 2. Lógica del buscador (modelo o marca)
        $whereClause = "";
        $hasSearch = !empty(trim($search));
        if ($hasSearch) {
            $whereClause = " WHERE (modelo LIKE :search
                                OR nombre_marca LIKE :search
                                OR clase LIKE :search
                                OR admision LIKE :search
                                OR encendido LIKE :search
                                OR transmision LIKE :search
                                OR refrigeracion LIKE :search
                                OR starter LIKE :search
                                OR carburador LIKE :search
                                )";
        }

        // 3. Consulta SQL con paginación
        $tablaVista = defined('VIEW_MOTORES') ? VIEW_MOTORES : 'vista_motores';
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

            // Convertimos cada fila en un objeto Motor
            $list = array();
            foreach ($rows as $row) {
                $list[] = new static($row);
            }

            return array($list, (int)$totalRows);

            } catch (PDOException $e) {
                parent::disconnect($conn);
                error_log("Error en getMotores: " . $e->getMessage());
                return array([], 0);
            }
        }



    public static function getMotorById($id_motor) {
        $conn = parent::connect();
        if (!$conn) return null;

        $tablaVista = defined('VIEW_MOTORES') ? VIEW_MOTORES : 'vista_motores';
        $sql = "SELECT * FROM {$tablaVista}  WHERE id_motor = :id_motor LIMIT 1";

        try {
            $st = $conn->prepare($sql);
            $st->bindValue(":id_motor", (int)$id_motor, PDO::PARAM_INT);
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
            error_log("Error en getMotorById: " . $e->getMessage());
            return null;
            }
    }

}
?>
