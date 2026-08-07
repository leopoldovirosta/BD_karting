<?php
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
        "id_pais" => "",
        "nombre_pais" => "",
        "codigo_iso" => "",
        "id_escuderia" => "",
        "nombre_escuderia" => "",
        "id_patrocinador" => "",
        "nombre_patrocinador" => ""
    );


    public static function getPilotos($startRow, $numRows, $order, $search = "") {

        $conn = parent::connect();
        if (!$conn) return [[], 0]; // Retorno consistente
        
        // Limpieza de seguridad para el ORDER BY
        $order = preg_replace("/[^a-zA-Z0-9\s_]/", "", $order);
        if (empty($order)) $order = "id_piloto ASC";
        
        // Lógica del buscador
        $whereClause = "";
        if (!empty($search)) {
            // Buscamos en nombre o apellido
            $whereClause = " WHERE nombre_piloto LIKE :search OR apellido_piloto LIKE :search ";
        }
        
        try {
            // --- PRIMERA CONSULTA: Obtener el total de filas (COUNT) ---
            $sqlCount = "SELECT COUNT(*) FROM " . VIEW_PILOTOS . " $whereClause";
            $stCount = $conn->prepare($sqlCount);

            // Si hay búsqueda, vinculamos el parámetro con comodines %
            if (!empty($search)) {
                $stCount->bindValue(":search", "%" . $search . "%", PDO::PARAM_STR);
            }

            $stCount->execute();
            $totalRows = $stCount->fetchColumn();

            // Si el conteo es 0, podemos terminar aquí para ahorrar recursos
            if ($totalRows == 0) {
                parent::disconnect($conn);
                return [[], 0];
            }

            // --- SEGUNDA CONSULTA: Obtener los datos reales (SELECT *) ---
            $sqlData = "SELECT * FROM " . VIEW_PILOTOS . " $whereClause ORDER BY $order LIMIT :startRow, :numRows";
            
            $stData = $conn->prepare($sqlData);
            
            if (!empty($search)) {
                $stData->bindValue(":search", "%" . $search . "%", PDO::PARAM_STR);
            }

            $stData->bindValue(":startRow", (int)$startRow, PDO::PARAM_INT);
            $stData->bindValue(":numRows", (int)$numRows, PDO::PARAM_INT);
            $stData->execute();
            $pilotos = array();
            foreach ($stData->fetchAll() as $row) {
                $pilotos[] = new Piloto($row);
            }

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

    // Funcion para buscar el siguiente piloto o anterior en vista piloto
    // ------------------------------------------------------------------
    public static function getNavegacionId($id_piloto_actual, $apellido_actual, $direccion_listado = 'ASC', $sentido = 'siguiente') {
        try {
            $conn = parent::connect();
            if (!$conn) return null;

            // 1. Normalizar dirección original del listado (ASC / DESC)
            $dirOriginal = (strtoupper($direccion_listado) === 'DESC') ? 'DESC' : 'ASC';
            $esSiguiente = ($sentido === 'siguiente');

            // 2. Definir operador y dirección SQL de forma limpia
            if ($esSiguiente) {
                $operador = ($dirOriginal === 'ASC') ? '>' : '<';
                $dirSQL   = $dirOriginal; // Mantiene ASC o DESC
            } else { // 'anterior'
                $operador = ($dirOriginal === 'ASC') ? '<' : '>';
                // Invertimos la ordenación para que LIMIT 1 capture el inmediatamente previo
                $dirSQL   = ($dirOriginal === 'ASC') ? 'DESC' : 'ASC';
            }

            // 3. Consulta SQL con Tupla (apellido_piloto, id_piloto)
            // MariaDB / MySQL evalúan el desempate por ID de forma nativa e impecable
            $sql = "SELECT id_piloto 
                    FROM " . VIEW_PILOTOS . "
                    WHERE (apellido_piloto, id_piloto) {$operador} (:apellido_actual, :id_actual)
                    ORDER BY apellido_piloto {$dirSQL}, id_piloto {$dirSQL} 
                    LIMIT 1";

            $st = $conn->prepare($sql);
            $st->bindValue(":apellido_actual", trim((string)$apellido_actual), PDO::PARAM_STR);
            $st->bindValue(":id_actual", (int)$id_piloto_actual, PDO::PARAM_INT);
            $st->execute();

            $row = $st->fetch(PDO::FETCH_ASSOC);
            parent::disconnect($conn);

            return $row ? (int)$row['id_piloto'] : null;

        } catch (Throwable $e) {
            error_log("Error en getNavegacionId: " . $e->getMessage());
            return null;
        }
    }




}
?>
