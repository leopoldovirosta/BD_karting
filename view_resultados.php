<?php

require_once "common.inc.php";
require_once "config.php";
require_once "resultados.class.php";
require_once "pilotos.class.php";
 
// 1. Detectamos el sentido (por defecto ASC)
$type = isset($_GET["type"]) && $_GET["type"] == "DESC" ? "DESC" : "ASC";

// 2. Limpiamos las variables
$start = isset($_GET["start"]) ? (int)$_GET["start"] : 0;
$order = isset($_GET["order"]) ? preg_replace("/[^a-zA-Z_]/", "", $_GET["order"]) : "id_resultado";
$pageSize = isset($_GET["pageSize"]) ? (int)$_GET["pageSize"] : PAGE_SIZE;

// 3. Llamamos al método (asegúrate de que tu SQL en Piloto ahora use $order y $type)
list($resultados, $totalRows) = Resultado::getResultados($start, $pageSize, $order . " " . $type);

displayPageHeader("Lista de Resultados");

?>
    <form action="view_resultados.php" method="get" class="search-form">
        <input type="hidden" name="order" value="<?php echo $order ?>" />
        <label for="pageSize">Resultados por página:</label>
        <select name="pageSize" id="pageSize" onchange="this.form.submit()">
            <?php foreach (array(5, 10, 20, 50) as $value): ?>
                <option value="<?php echo $value ?>" <?php if ($pageSize == $value) echo 'selected="selected"' ?>>
                    <?php echo $value ?>
                </option>
            <?php endforeach; ?>
        </select>
        <br><br>
    </form>
    <br>
    <table>
    <tr>
        <?php
        // Definimos las columnas que queremos mostrar
    $columns = array(
            "id_resultado" => "ID",
            "fecha_carrera" => "Fecha",
            "nombre_cto" => "Campeonato",
            "nombre_circuito" => "Circuito",
            "nombre_carrera_tipo" => "Carrera",
            "nombre_categoria" => "Categoría",
            "nombre_piloto" => "Nombre",
            "apellido_piloto" => "Apellido",
            "foto_piloto" => "Foto",
            "dorsal" => "Dorsal",
            "tiempo_total" => "Tiempo total",
            "mejor_vuelta" => "Mejor vuelta",
            "num_vueltas" => "Vueltas",
            "num_vueltas_completadas" => "Completadas",
            "posicion" => "Posición",
            "comentario_posicion" => "Observaciones",
            "puntos" => "Puntos",
            "marca_chasis" => "Chasis",
            "modelo_chasis" => "Modelo",
            "marca_motor" => "Motor",
            "modelo_motor" => "Modelo"
        );

        foreach ($columns as $colKey => $colName): 
                // Si la columna es la actual, el siguiente clic debe ser el opuesto
                $nextType = ($order == $colKey && $type == "ASC") ? "DESC" : "ASC";
                $icon = ($type == "ASC") ? "▲" : "▼";
            ?>
                <th>
                    <a href="view_resultados.php?order=<?php echo $colKey ?>&amp;type=<?php echo $nextType ?>&amp;pageSize=<?php echo $pageSize ?>">
                        <?php echo $colName ?>
                        <?php if ($order == $colKey): ?>
                            <span class="sort-icon"><?php echo $icon ?></span>
                        <?php endif; ?>
                    </a>
                </th>
            <?php endforeach; ?>
        </tr>

<?php
    $rowCount = 0;
    
    foreach($resultados as $resultado):
        $rowCount++;
?>
        <tr<?php if ($rowCount % 2 == 0) echo " class='alt'" ?>>
            <td><a href="view_resultado.php?id_resultado=<?php echo $resultado->getValue('id_resultado') ?>&id_piloto=<?php echo $resultado->getValue("id_piloto")?>&id_cto=<?php echo $resultado->getValue("id_cto")?>"><?php echo $resultado->getValue('id_resultado') ?></a></td>
            <td><?php echo $resultado->getValueEncoded("fecha_carrera") ?></td>
            <td><?php echo $resultado->getValueEncoded("nombre_cto") ?></td>
            <td><?php echo $resultado->getValueEncoded("nombre_circuito") ?></td>
            <td><?php echo $resultado->getValueEncoded("nombre_carrera_tipo") ?></td>
            <td><?php echo $resultado->getValueEncoded("nombre_categoria") ?></td>
            <td><?php echo $resultado->getValueEncoded("nombre_piloto") ?></td>
            <td><?php echo $resultado->getValueEncoded("apellido_piloto") ?></td>
            <td><img src="<?php echo IMAGE_PILOT_DIRECTORY . ($resultado->getValueEncoded('foto_piloto') ?: 'default.jpg') ?>" class="foto foto-click" onclick="openModal(this.src, this.alt)" /></td>
            <td class="text-center"><?php echo $resultado->getValue("dorsal") ?></td>
            <td><?php echo $resultado->getValueEncoded("tiempo_total") ?></td>
            <td><?php echo $resultado->getValueEncoded("mejor_vuelta") ?></td>
            <td><?php echo $resultado->getValue("num_vueltas") ?></td>
            <td><?php echo $resultado->getValue("num_vueltas_completadas") ?></td>
            <td><?php echo $resultado->getValue("posicion") ?></td>
            <td><?php echo $resultado->getValueEncoded("comentario_posicion") ?></td>
            <td class="text-center"><?php echo $resultado->getValue("puntos") ?></td>
            <td><?php echo $resultado->getValueEncoded("marca_chasis") ?></td>
            <td><?php echo $resultado->getValueEncoded("modelo_chasis") ?></td>
            <td><?php echo $resultado->getValueEncoded("marca_motor") ?></td>
            <td><?php echo $resultado->getValueEncoded("modelo_motor") ?></td>
        </tr>
<?php
    endforeach;
?>
    </table>
    <div class="pagination-container">
    <p class="info-text">
        Mostrando <?php echo $start + 1 ?>-<?php echo min($start + $pageSize, $totalRows) ?> de <?php echo $totalRows ?>
    </p>
        <?php if ($start > 0 ): ?>
            <a href="view_resultados.php?start=<?php echo max($start - $pageSize, 0) ?>&amp;order=<?php echo $order ?>&amp;type=<?php echo $type ?>&amp;pageSize=<?php echo $pageSize ?>" class="btn-nav">&laquo; Página anterior</a>
        <?php endif; ?>
        
        &nbsp;
        
        <?php if ($start + $pageSize < $totalRows): ?>
            <a href="view_resultados.php?start=<?php echo ($start + $pageSize) ?>&amp;order=<?php echo $order ?>&amp;type=<?php echo $type ?>&amp;pageSize=<?php echo $pageSize ?>" class="btn-nav">Página siguiente &raquo;</a>
        <?php endif; ?>
    </div> 
<?php

    displayPageFooter();
?>
