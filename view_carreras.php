<?php

require_once "common.inc.php";
require_once "config.php";
require_once "carreras.class.php";
 
// 1. Detectamos el sentido (por defecto ASC)
$type = isset($_GET["type"]) && $_GET["type"] == "DESC" ? "DESC" : "ASC";

// 2. Limpiamos las variables
$search = isset($_GET["search"]) ? trim($_GET["search"]) : "";
$start = isset($_GET["start"]) ? (int)$_GET["start"] : 0;
$order = isset($_GET["order"]) ? preg_replace("/[^a-zA-Z_]/", "", $_GET["order"]) : "id_carrera";
$pageSize = isset($_GET["pageSize"]) ? (int)$_GET["pageSize"] : PAGE_SIZE;

// 3. Llamamos al método (asegúrate de que tu SQL en Piloto ahora use $order y $type)
list($carreras, $totalRows) = Carrera::getCarreras($start, $pageSize, $order . " " . $type, $search);

displayPageHeader("Lista de carreras");

?>
    <form action="view_carreras.php" method="get" class="search-form">
        <input type="hidden" name="order" value="<?php echo $order ?>" />
        <input type="hidden" name="type" value="<?php echo htmlspecialchars($type); ?>" />

    <div class="tabla-controles">
      <div class="resultados-por-pagina">
        <label for="pageSize">Carreras por página:</label>
        <select name="pageSize" id="pageSize" class="form-control" onchange="this.form.submit()">
            <?php foreach (array(5, 10, 20, 50) as $value): ?>
                <option value="<?php echo $value ?>" <?php if ($pageSize == $value) echo 'selected="selected"' ?>>
                    <?php echo $value ?>
                </option>
            <?php endforeach; ?>
        </select>
      </div>
      <div class="buscador-box">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search) ?>" placeholder="Buscar carrera..." class="form-control" />
            <button type="submit" class="btn btn-sm btn-primary">Buscar</button>
            <?php if (!empty($search)): ?>
                <a href="view_carreras.php" class="btn btn-sm btn-secondary">Limpiar</a>
            <?php endif; ?>
      </div>
    </div>
    </form>
    <div class="table-responsive">
     <table>
      <thead>
       <tr>
        <?php
        // Definimos las columnas que queremos mostrar
        $columns = array(
            "id_carrera" => "ID",
            "nombre_cto" => "Campeonato",
            "anio_edicion" => "Temporada",
            "fecha_carrera" => "Fecha",
            "num_vueltas" => "Vueltas",
            "dia" => "Día",
            "pista" => "Pista",
            "nombre_carrera_tipo" => "Carrera",
            "nombre_circuito" => "Circuito",
            "temperatura" => "Tª",
            "humedad" => "Humedad",
            "presion" => "Presión",
            "viento" => "Viento",
            "orientacion" => "Orientacion",
            "tasfalto" => "Tª asfalto"
        );

        foreach ($columns as $colKey => $colName): 
                // Si la columna es la actual, el siguiente clic debe ser el opuesto
                $nextType = ($order == $colKey && $type == "ASC") ? "DESC" : "ASC";
                $icon = ($type == "ASC") ? "▲" : "▼";
            ?>
                <th>
                    <a href="view_carreras.php?order=<?php echo $colKey ?>&amp;type=<?php echo $nextType ?>&amp;pageSize=<?php echo $pageSize ?>">
                        <?php echo $colName ?>
                        <?php if ($order == $colKey): ?>
                            <span class="sort-icon"><?php echo $icon ?></span>
                        <?php endif; ?>
                    </a>
                </th>
            <?php endforeach; ?>
        </tr>
       </thead>
     <tbody>
<?php
    $rowCount = 0;
    
    foreach($carreras as $carrera):
        $rowCount++;
?>
        <tr<?php if ($rowCount % 2 == 0) echo " class='alt'" ?>>
            <td><a href="view_carrera.php?id_carrera=<?php echo $carrera->getValue('id_carrera') ?>&id_edicion=<?php echo $carrera->getValue("id_edicion")?>" title="Ver ficha de carrera">
                <span class="material-symbols-outlined">assignment</span></a></td></a></td>
            <td><?php echo $carrera->getValueEncoded("nombre_cto") ?></td>
            <td><?php echo $carrera->getValueEncoded("anio_edicion") ?></td>
            <td><?php echo formatearFecha($carrera->getValueEncoded("fecha_carrera")); ?></td>
            <td class="text-center"><?php echo mostrarValor($carrera->getValue("num_vueltas")); ?></td>
            <td><?php echo mostrarValor($carrera->getValueEncoded("dia")); ?></td>
            <td><?php echo mostrarValor($carrera->getValueEncoded("pista")); ?></td>
            <td><?php echo $carrera->getValueEncoded("nombre_carrera_tipo") ?></td>
            <td><?php echo $carrera->getValueEncoded("nombre_circuito") ?></td>
            <td><?php echo mostrarValor($carrera->getValue("temperatura")); ?> ºC</td>
            <td class="text-center"><?php echo mostrarValor($carrera->getValue("humedad")); ?> %</td>
            <td><?php echo mostrarValor($carrera->getValue("presion")); ?> hPa</td>
            <td><?php echo mostrarValor($carrera->getValue("viento")); ?> Km/h</td>
            <td><?php echo mostrarValor($carrera->getValueEncoded("orientacion")); ?></td>
            <?php
                $tasfalto = $carrera->getValue("tasfalto");
                if (!empty($tasfalto)): // Si la URL no está vacía...
            ?>
                <td class="text-center"><?php echo $tasfalto ?> ºC</td>
            <?php else: ?><td class="text-center">---</td>
            <?php endif; ?>
        </tr>
        <?php
            endforeach;
        ?>
       </tbody>
     </table>
    </div>

    <div class="pagination-container">
    <p class="info-text">
        Mostrando <?php echo $start + 1 ?>-<?php echo min($start + $pageSize, $totalRows) ?> de <?php echo $totalRows ?>
    </p>
        <?php if ($start > 0 ): ?>
            <a href="view_carreras.php?start=<?php echo max($start - $pageSize, 0) ?>&amp;order=<?php echo $order ?>&amp;type=<?php echo $type ?>&amp;pageSize=<?php echo $pageSize ?>" class="btn-nav">&laquo; Página anterior</a>
        <?php endif; ?>
        
        &nbsp;
        
        <?php if ($start + $pageSize < $totalRows): ?>
            <a href="view_carreras.php?start=<?php echo ($start + $pageSize) ?>&amp;order=<?php echo $order ?>&amp;type=<?php echo $type ?>&amp;pageSize=<?php echo $pageSize ?>" class="btn-nav">Página siguiente &raquo;</a>
        <?php endif; ?>
    </div> 
<?php

    displayPageFooter();
?>
