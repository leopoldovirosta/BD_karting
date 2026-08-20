<?php
require_once "DataObject.class.php";
require_once "config.php";

class Categoria extends DataObject {

    protected $data = array(
        "id_categoria" => "",
        "nombre_categoria" => "",
        "descripcion_categoria" => "",
        "orden" => "",
        "activa" => ""
    );


    public static function getCategorias(): array {
        $conn = parent::connect();
        
        $sql = "SELECT * FROM " . TABLE_CATEGORIAS . " WHERE activa = 1 ORDER BY orden ASC";

        try {
            $st = $conn->prepare($sql);
            $st->execute();
            $rows = $st->fetchAll(PDO::FETCH_ASSOC);
            parent::disconnect($conn);

            // Convertimos cada fila asociativa en un objeto Categoria
            $list = array();
            foreach ($rows as $row) {
                $list[] = new Categoria($row);
            }

            return $list;
        } catch (PDOException $e) {
            parent::disconnect($conn);
            return array();
        }
    }



}
?>
