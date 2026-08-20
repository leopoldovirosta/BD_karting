<?php
require_once "DataObject.class.php";
require_once "config.php";

class Marcas extends DataObject {

    protected $data = array(
        "id_marca" => "",
        "nombre_marca" => "",
        "id_pais" => "",
        "logo_marca" => "",
        "pagina_web" => "",
        "es_marca_chasis" => "",
        "es_marca_motor" => "",
        "es_marca_ruedas" => ""
    );


    public static function getMarcaChasis(): array {
        $conn = parent::connect();
        if (!$conn) return array();

        $sql = "SELECT * FROM " . TABLE_MARCAS . " WHERE es_marca_chasis = 1 ORDER BY nombre_marca ASC";

        try {
            $st = $conn->prepare($sql);
            $st->execute();
            $rows = $st->fetchAll(PDO::FETCH_ASSOC);
            parent::disconnect($conn);

            // Convertimos cada fila asociativa en un objeto Marcas
            $list = array();
            foreach ($rows as $row) {
                $list[] = new static($row);
            }

            return $list;
        } catch (PDOException $e) {
            parent::disconnect($conn);
            error_log("Error en getMarcaChasis: " . $e->getMessage());
            return array();
        }
    }

    
    public static function getMarcaMotor(): array {
    $conn = parent::connect();
    if (!$conn) return array();

    $sql = "SELECT * FROM " . TABLE_MARCAS . " WHERE es_marca_motor = 1 ORDER BY nombre_marca ASC";

    try {
        $st = $conn->prepare($sql);
        $st->execute();
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        parent::disconnect($conn);

        $list = array();
        foreach ($rows as $row) {
            $list[] = new static($row);
        }
        return $list;
    } catch (PDOException $e) {
        parent::disconnect($conn);
        error_log("Error en getMarcaMotor: " . $e->getMessage());
        return array();
    }
}

}
?>
