<?php
require_once "pdo.php";
require_once "DataObject.class.php";
require_once "config.php";


class Piloto extends DataObject {
    protected $data = array(
        "id_piloto" => "",
        "nombre_piloto" => "",
        "apellido_piloto" => "",
        "fecha_nacimiento" => "",
        "web_piloto" => "",
        "email_piloto" => "",
        "foto_piloto" => "",
        "nombre_federacion" => "",
        "nombre_escuderia" => "",
        "nombre_sponsor" => ""
    );


    public static function getPilotos($startRow, $numRows, $order, $search = "") {

        $conn = parent::connect();
        if (!$conn) return[[], 0]; // Retorno consistente
        
        // Limpieza de seguridad para el ORDER BY
        $order = preg_replace("/[^a-zA-Z0-9\s_]/", "", $order);
        if (empty($order)) $order = "id_piloto ASC";
        
        // Lógica del buscador
        $whereClause = "";
        if (!empty($search)) {
            // Buscamos en nombre o apellido
            $whereClause = " WHERE nombre_piloto LIKE :search OR apellido_piloto LIKE :search ";
        }
        
        $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM " . VIEW_PILOTOS . " $whereClause ORDER BY $order LIMIT :startRow, :numRows";
    
        try {
            $st = $conn->prepare($sql);

        // Si hay búsqueda, vinculamos el parámetro con comodines %
            if (!empty($search)) {
                $st->bindValue(":search", "%" . $search . "%", PDO::PARAM_STR);
            }

            $st->bindValue(":startRow", (int)$startRow, PDO::PARAM_INT);
            $st->bindValue(":numRows", (int)$numRows, PDO::PARAM_INT);
            $st->execute();
            $pilotos = array();
            foreach ($st->fetchAll() as $row) {
                $pilotos[] = new Piloto($row);
            }
        // Obtener el total de filas para la paginación
        $res = $conn->query("SELECT FOUND_ROWS() AS totalRows");
        $totalRows = $res->fetchColumn();

        parent::disconnect($conn);
        return array($pilotos, $totalRows);

        } catch (PDOException $e) {
            parent::disconnect($conn);
            error_log("Error en getPilotos: " . $e->getMessage());
            return [[], 0]; // Devolvemos array vacío en caso de error
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
            $row = $st->fetch();
            parent::disconnect($conn);
            if ($row) {
                return new Piloto($row);
            } else {
                return null;
            }
        } catch (PDOException $e) {
            parent::disconnect($conn);
            error_log("Error en getPiloto: " . $e->getMessage());
            return null;
            }
        }

}
?>
