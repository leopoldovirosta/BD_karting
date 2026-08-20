<?php
require_once "DataObject.class.php";
require_once "config.php";

class Chasis extends DataObject {

    protected $data = array(
        "id_chasis"         => "",
        "id_marca"          => "",
        "nombre_marca"      => "",
        "logo_marca"        => "",
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


    public static function getChasis($start, $pageSize, $order="id_chasis ASC"): array {
        $conn = parent::connect();
        if (!$conn) return [[], 0]; // Retorno consistente

        // Limpieza de seguridad para el ORDER BY
        $orderClean = preg_replace("/[^a-zA-Z0-9\s_]/", "", $order);
        if (empty($orderClean)) {
            $orderClean = "id_chasis ASC";
        }
        
        // 2. Consulta con SQL_CALC_FOUND_ROWS para soporte de paginación
        $tablaVista = defined('VIEW_CHASIS') ? VIEW_CHASIS : 'vista_chasis';
        $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM {$tablaVista} 
                ORDER BY {$orderClean} 
                LIMIT :start, :pageSize";

        try {
            $st = $conn->prepare($sql);
            $st->bindValue(":start", (int)$start, PDO::PARAM_INT);
            $st->bindValue(":pageSize", (int)$pageSize, PDO::PARAM_INT);
            $st->execute();

            $rows = $st->fetchAll(PDO::FETCH_ASSOC);
            // Obtener el total de filas encontradas para la paginación
            $totalRows = $conn->query("SELECT FOUND_ROWS()")->fetchColumn();

            parent::disconnect($conn);

            // Convertimos cada fila asociativa en un objeto Chasis
            $list = array();
            foreach ($rows as $row) {
                $list[] = new Chasis($row);
            }

            return array($list, (int)$totalRows);

        } catch (PDOException $e) {
            parent::disconnect($conn);
            return array([], 0);
        }
    }



}
?>
