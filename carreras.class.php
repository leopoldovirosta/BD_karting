<?php
require_once "DataObject.class.php";
require_once "config.php";


class Carrera extends DataObject {
    protected $data = array(
        "id_carrera" => "",
        "id_cto" => "",
        "nombre_cto" => "",
        "fecha_carrera" => "",
        "dia" => "",
        "num_vueltas" => "",
        "temperatura" => "",
        "humedad" => "",
        "presion" => "",
        "viento" => "",
        "orientacion" => "",
        "tasfalto" => "",
        "pista" => "",
        "nombre_carrera_tipo" => "",
        "id_circuito" => "",
        "nombre_circuito" => ""
    );


    public static function getCarreras($startRow, $numRows, $order) {

        $conn = parent::connect();
        if (!$conn) return [[], 0]; // Retorno consistente
        
        // Limpieza de seguridad para el ORDER BY
        $order = preg_replace("/[^a-zA-Z0-9\s_]/", "", $order);
        if (empty($order)) $order = "id_carrera ASC";
        
        try {
            // --- PRIMERA CONSULTA: Obtener el total de filas (COUNT) ---
            $sqlCount = "SELECT COUNT(*) FROM " . VIEW_CARRERAS;
            $stCount = $conn->prepare($sqlCount);
            $stCount->execute();
            $totalRows = $stCount->fetchColumn();

            // Si el conteo es 0, podemos terminar aquí para ahorrar recursos
            if ($totalRows == 0) {
                parent::disconnect($conn);
                return [[], 0];
            }

            // --- SEGUNDA CONSULTA: Obtener los datos reales (SELECT *) ---
            $sqlData = "SELECT * FROM " . VIEW_CARRERAS . " ORDER BY $order LIMIT :startRow, :numRows";
            
            $stData = $conn->prepare($sqlData);
            
            $stData->bindValue(":startRow", (int)$startRow, PDO::PARAM_INT);
            $stData->bindValue(":numRows", (int)$numRows, PDO::PARAM_INT);
            $stData->execute();
            $carreras = array();
            foreach ($stData->fetchAll() as $row) {
                $carreras[] = new Carrera($row);
            }

            parent::disconnect($conn);
            return array($carreras, (int)$totalRows);

        } catch (PDOException $e) {
            parent::disconnect($conn);
            error_log("Error en getCarreras: " . $e->getMessage());
            return [[], 0]; // Devolvemos array vacío en caso de error
        }
    }

    public static function getCarrera($id_carrera, $id_cto) {
        $conn = parent::connect();
        if (!$conn) return null;

        $sql = "SELECT * FROM " . VIEW_CARRERAS . " WHERE id_carrera = :id_carrera AND id_cto = :id_cto";

        try {
            $st = $conn->prepare($sql);
            $st->bindValue(":id_carrera", (int)$id_carrera, PDO::PARAM_INT);
            $st->bindValue(":id_cto", (int)$id_cto, PDO::PARAM_INT);
            $st->execute();
            $row = $st->fetch();
            parent::disconnect($conn);
            return ($row) ? new Carrera($row) : null;
            
        } catch (PDOException $e) {
            parent::disconnect($conn);
            error_log("Error en getCarrera: " . $e->getMessage());
            return null;
            }
    }

/*
     * Obtiene los 3 primeros clasificados de cada categoría para una carrera dada.
     * 
     * @param int $id_carrera ID de la carrera a consultar
     * @return array Estructura: ['Nombre Categoria' => [ObjetoResultado, ObjetoResultado, ...]]
     */
    public static function getPodiosPorCategoria($id_carrera) {
        $conn = parent::connect();
        if (!$conn) return [];

        // Consulta optimizada con ROW_NUMBER() para filtrar el TOP 3 por categoría en SQL
        $sql = "SELECT * FROM (
                    SELECT 
                        sub.*,
                        ROW_NUMBER() OVER (
                            PARTITION BY sub.id_categoria 
                            ORDER BY sub.posicion ASC
                        ) AS ranking_cat
                    FROM (
                        -- Subconsulta interna para eliminar duplicados del JOIN entre campeonatos
                        SELECT DISTINCT
                            id_carrera,
                            id_piloto,
                            nombre_piloto,
                            apellido_piloto,
                            foto_piloto,
                            id_categoria,
                            nombre_categoria,
                            posicion,
                            mejor_vuelta,
                            comentario_posicion,
                            marca_chasis,
                            marca_motor,
                            marca_rueda
                        FROM " . VIEW_RESULTADOS . "
                        WHERE id_carrera = :id_carrera
                            AND (comentario_posicion IS NULL OR comentario_posicion NOT IN ('DNS', 'DSQ'))
                    ) AS sub
                ) AS podios
                WHERE ranking_cat <= 3
                ORDER BY id_categoria ASC, ranking_cat ASC";

        try {
            $st = $conn->prepare($sql);
            $st->bindValue(":id_carrera", (int)$id_carrera, PDO::PARAM_INT);
            $st->execute();

            $podios = [];
            
            // Si tienes una clase 'Resultado', instanciamos sus objetos;
            // si no, guardamos el array asociativo directamente.
            foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $cat = $row['nombre_categoria'];
                if (!isset($podios[$cat])) {
                    $podios[$cat] = [];
                }
                
                // Si usas la clase Resultado:
                $podios[$cat][] = class_exists('Resultado') ? new Resultado($row) : $row;
            }

            parent::disconnect($conn);
            return $podios;

        } catch (PDOException $e) {
            parent::disconnect($conn);
            error_log("Error en getPodiosPorCategoria: " . $e->getMessage());
            return [];
        }
    }

/**
     * Obtiene el piloto y datos de la vuelta más rápida de una carrera.
     * 
     * @param int $id_carrera ID de la carrera a consultar
     * @return Resultado|array|null Devuelve el objeto Resultado (o array), o null si no hay registros.
     */
    public static function getVueltaRapida($id_carrera) {
        $conn = parent::connect();
        if (!$conn) return null;

        // Ordenamos por mejor_vuelta ASC para obtener el tiempo más bajo
        $sql = "SELECT * 
                FROM " . VIEW_RESULTADOS . "
                WHERE id_carrera = :id_carrera
                  AND mejor_vuelta IS NOT NULL
                  AND mejor_vuelta != ''
                  AND mejor_vuelta != '00:00:00.000'
                  AND (comentario_posicion IS NULL OR comentario_posicion NOT IN ('DNS', 'DSQ'))
                ORDER BY mejor_vuelta ASC
                LIMIT 1";

        try {
            $st = $conn->prepare($sql);
            $st->bindValue(":id_carrera", (int)$id_carrera, PDO::PARAM_INT);
            $st->execute();
            
            $row = $st->fetch(PDO::FETCH_ASSOC);
            parent::disconnect($conn);

            if (!$row) {
                return null;
            }

            // Si existe la clase Resultado instanciamos el objeto, de lo contrario devolvemos el array
            return class_exists('Resultado') ? new Resultado($row) : $row;

        } catch (PDOException $e) {
            parent::disconnect($conn);
            error_log("Error en getVueltaRapida: " . $e->getMessage());
            return null;
        }
    }



}
?>
