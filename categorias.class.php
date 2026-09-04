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


    public static function getCategorias($idEdicion = 0, $idCampeonato = 0): array {
        $conn = parent::connect();
        if (!$conn) return array(); // Control de fallo de conexión

        // Si se pasan ambos IDs, filtramos por la relación de edición y campeonato
        if ($idEdicion > 0 && $idCampeonato > 0) {
            $sql = "SELECT DISTINCT c.* 
                    FROM " . TABLE_CATEGORIAS . " c
                    INNER JOIN ediciones_categorias ec ON c.id_categoria = ec.id_categoria
                    INNER JOIN ediciones_campeonatos ecm ON ec.id_edicion = ecm.id_edicion
                    WHERE c.activa = 1 
                    AND ec.id_edicion = :id_edicion 
                    AND ecm.id_cto = :id_campeonato 
                    ORDER BY c.orden ASC";
        } else {
            // Consulta por defecto (todas las activas)
            $sql = "SELECT * FROM " . TABLE_CATEGORIAS . " WHERE activa = 1 ORDER BY orden ASC";
        }

        try {
            $st = $conn->prepare($sql);

            // Bind de los parámetros solo si se aplican los filtros
            if ($idEdicion > 0 && $idCampeonato > 0) {
                $st->bindValue(":id_edicion", (int)$idEdicion, PDO::PARAM_INT);
                $st->bindValue(":id_campeonato", (int)$idCampeonato, PDO::PARAM_INT);
            }

            $st->execute();
            $rows = $st->fetchAll(PDO::FETCH_ASSOC);
            parent::disconnect($conn);

            // Convertimos cada fila asociativa en un objeto Categoria
            $list = array();
            foreach ($rows as $row) {
                $list[] = new static($row);
            }

        return $list;
        } catch (PDOException $e) {
            parent::disconnect($conn);
            error_log("Error en getCategorias: " . $e->getMessage());
            return array();
        }
    }


}
?>
