<?php
require_once "DataObject.class.php";
require_once "config.php";


class Carrera extends DataObject {
    protected $data = array(
        "id_carrera" => "",
        "fecha_carrera" => "",
        "dia" => "",
        "num_vueltas" => "",
        "pista" => "",
        "temperatura" => "",
        "humedad" => "",
        "presion" => "",
        "viento" => "",
        "orientacion" => "",
        "tasfalto" => "",
        "id_carrera_tipo" => "",
        "nombre_carrera_tipo" => "",
        "id_circuito" => "",
        "nombre_circuito" => "",
        "longitud" => "",
        "id_cto" => "",
        "nombre_cto" => "",
        "id_edicion" => "",
        "anio_edicion" => ""
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
            foreach ($stData->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $carreras[] = new static($row);
            }

            parent::disconnect($conn);
            return array($carreras, (int)$totalRows);

        } catch (PDOException $e) {
            parent::disconnect($conn);
            error_log("Error en getCarreras: " . $e->getMessage());
            return [[], 0]; // Devolvemos array vacío en caso de error
        }
    }

    public static function getCarrera($id_carrera, $id_edicion) {
        $conn = parent::connect();
        if (!$conn) return null;

        $sql = "SELECT * FROM " . VIEW_CARRERAS . " WHERE id_carrera = :id_carrera AND id_edicion = :id_edicion";

        try {
            $st = $conn->prepare($sql);
            $st->bindValue(":id_carrera", (int)$id_carrera, PDO::PARAM_INT);
            $st->bindValue(":id_edicion", (int)$id_edicion, PDO::PARAM_INT);
            $st->execute();
            $row = $st->fetch();
            parent::disconnect($conn);
            return ($row) ? new static($row) : null;
            
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
    public static function getPodiosPorCategoria($id_carrera,$id_edicion) {
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
                            vr.id_carrera,
                            vr.id_piloto,
                            vr.nombre_piloto,
                            vr.apellido_piloto,
                            vr.foto_piloto,
                            vr.id_categoria,
                            vr.nombre_categoria,
                            vr.posicion,
                            vr.mejor_vuelta,
                            vr.comentario_posicion,
                            vr.marca_chasis,
                            vr.marca_motor,
                            vr.marca_rueda
                        FROM " . VIEW_RESULTADOS . " vr
                        INNER JOIN ediciones_categorias ecat
                            ON vr.id_categoria = ecat.id_categoria
                        WHERE vr.id_carrera = :id_carrera
                            AND ecat.id_edicion = :id_edicion -- 🎯 Solo categorías asociadas al campeonato
                            AND (vr.comentario_posicion IS NULL OR vr.comentario_posicion NOT IN ('DNS', 'DSQ'))
                    ) AS sub
                ) AS podios
                WHERE ranking_cat <= 3
                ORDER BY id_categoria ASC, ranking_cat ASC";

        try {
            $st = $conn->prepare($sql);
            $st->bindValue(":id_carrera", (int)$id_carrera, PDO::PARAM_INT);
            $st->bindValue(":id_edicion", (int)$id_edicion, PDO::PARAM_INT);
            $st->execute();

            $podios = [];
            
            // Si tienes una clase 'Resultado', instanciamos sus objetos;
            // si no, guardamos el array asociativo directamente.
            foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $cat = $row['nombre_categoria'];
                if (!isset($podios[$cat])) {
                    $podios[$cat] = [];
                }
                
                $podios[$cat][] = new Resultado($row);
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
            
            return new Resultado($row);

        } catch (PDOException $e) {
            parent::disconnect($conn);
            error_log("Error en getVueltaRapida: " . $e->getMessage());
            return null;
        }
    }

        
    public static function getSiguienteId($id_edicion, $fecha_carrera, $id_carrera_tipo) {
        $conn = parent::connect();
        if (!$conn) return null;

        $sql = "SELECT id_carrera 
                FROM " . VIEW_CARRERAS . "
                WHERE id_edicion = :id_edicion
                  AND ((fecha_carrera = :fecha_carrera AND id_carrera_tipo > :id_carrera_tipo)
                  OR (fecha_carrera > :fecha_carrera))
                ORDER BY fecha_carrera ASC, id_Carrera_tipo ASC
                LIMIT 1";

        try {
            $st = $conn->prepare($sql);
            $st->bindValue(":id_edicion", (int)$id_edicion, PDO::PARAM_INT);
            $st->bindValue(":fecha_carrera", $fecha_carrera, PDO::PARAM_STR);
            $st->bindValue(":id_carrera_tipo", (int)$id_carrera_tipo, PDO::PARAM_INT);
            $st->execute();
            
            $row = $st->fetch(PDO::FETCH_ASSOC);
            parent::disconnect($conn);

            // Retornamos directamente el ID entero si existe, o null
            return $row ? (int)$row['id_carrera'] : null;

        } catch (PDOException $e) {
            parent::disconnect($conn);
            error_log("Error en getSiguienteId: " . $e->getMessage());
            return null;
        }
    }


    public static function getAnteriorId($id_edicion, $fecha_carrera, $id_carrera_tipo) {
        $conn = parent::connect();
        if (!$conn) return null;

        $sql = "SELECT id_carrera 
                FROM " . VIEW_CARRERAS . "
                WHERE id_edicion = :id_edicion
                  AND ((fecha_carrera = :fecha_carrera AND id_carrera_tipo < :id_carrera_tipo)
                  OR (fecha_carrera < :fecha_carrera))
                ORDER BY fecha_carrera DESC, id_Carrera_tipo DESC
                LIMIT 1";

        try {
            $st = $conn->prepare($sql);
            $st->bindValue(":id_edicion", (int)$id_edicion, PDO::PARAM_INT);
            $st->bindValue(":fecha_carrera", $fecha_carrera, PDO::PARAM_STR);
            $st->bindValue(":id_carrera_tipo", (int)$id_carrera_tipo, PDO::PARAM_INT);
            $st->execute();
            
            $row = $st->fetch(PDO::FETCH_ASSOC);
            parent::disconnect($conn);

            // Retornamos directamente el ID entero si existe, o null
            return $row ? (int)$row['id_carrera'] : null;

        } catch (PDOException $e) {
            parent::disconnect($conn);
            error_log("Error en getAnteriorId: " . $e->getMessage());
            return null;
        }
    }

}
?>
