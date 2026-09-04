<?php

require_once "common.inc.php";
require_once "config.php";
require_once "circuitos.class.php";
 
// 1. Detectamos el sentido (por defecto ASC)
$type = isset($_GET["type"]) && $_GET["type"] == "DESC" ? "DESC" : "ASC";

// 2. Limpiamos las variables
$search = isset($_GET["search"]) ? $_GET["search"] : "";
$start = isset($_GET["start"]) ? (int)$_GET["start"] : 0;
$order = isset($_GET["order"]) ? preg_replace("/[^a-zA-Z_]/", "", $_GET["order"]) : "id_circuito";
$pageSize = isset($_GET["pageSize"]) ? (int)$_GET["pageSize"] : PAGE_SIZE;

// 3. Llamamos al método (asegúrate de que tu SQL en Piloto ahora use $order y $type)
list($circuitos, $totalRows) = Circuito::getCircuitos($start, $pageSize, $order . " " . $type, $search);

displayPageHeader("Lista de circuitos");

?>
    <form action="view_circuitos.php" method="get" class="search-form">
        <input type="hidden" name="order" value="<?php echo htmlspecialchars($order); ?>" />
        <input type="hidden" name="type" value="<?php echo htmlspecialchars($type); ?>" />

    <div class="tabla-controles">
      <div class="resultados-por-pagina">
        <label for="pageSize">Circuitos por página:</label>
        <select name="pageSize" id="pageSize" class="form-control" onchange="this.form.submit()">
            <?php foreach (array(5, 10, 20, 50) as $value): ?>
               <option value="<?php echo $value ?>" <?php if ($pageSize == $value) echo 'selected="selected"' ?>>
                    <?php echo $value ?>
                </option>
            <?php endforeach; ?>
        </select>
      </div>
      <div class="buscador-box">
        <input type="text" name="search" value="<?php echo htmlspecialchars($search) ?>" placeholder="Buscar circuito..." />
        <button type="submit" class="btn-nav">Buscar</button>
            <?php if (!empty($search)): ?>
                <a href="view_circuitos.php">Limpiar filtro</a>
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
            "id_circuito"           => "ID",
            "codigo_iso"            => "Pais",
            "nombre_circuito"       => "Circuito",
            "web_circuito"          => "Web",
            "direccion_circuito"    => "Dirección",
            "localidad_circuito"    => "Localidad",
            "telefono_circuito"     => "Teléfono",
            "altitud"               => "Altitud",
            "longitud"              => "Longitud",
            "curvasizd"             => "Curvas IZD",
            "curvasdcha"            => "Curvas DCHA",
            "velocidadmax"          => "Velocidad Max",
            "silueta"               => "Trazado"
        );
        // Columnas no ordenables
        $noSortable = array("web_circuito","telefono_circuito","direccion_circuito","silueta");
        
        foreach ($columns as $colKey => $colName): 
                // Si la columna es la actual, el siguiente clic debe ser el opuesto
                $nextType = ($order == $colKey && $type == "ASC") ? "DESC" : "ASC";
                $icon = ($type == "ASC") ? "▲" : "▼";
            ?>
                <th>
                    <?php if (in_array($colKey, $noSortable)): ?>
                        <?php echo htmlspecialchars($colName); ?>
                    <?php else: ?>
                    <a href="view_circuitos.php?order=<?php echo $colKey ?>&amp;type=<?php echo $nextType ?>&amp;pageSize=<?php echo $pageSize ?>&amp;search=<?php echo urlencode($search) ?>">
                        <?php echo htmlspecialchars($colName) ?>
                        <?php if ($order == $colKey): ?>
                            <span class="sort-icon"><?php echo $icon ?></span>
                        <?php endif; ?>
                    </a>
                    <?php endif; ?>
                </th>
            <?php endforeach; ?>
        </tr>
       </thead>
       </tbody>
    <?php
        $rowCount = 0;
        
        foreach($circuitos as $circuito):
            $rowCount++;
    ?>
            <tr<?php if ($rowCount % 2 == 0) echo " class='alt'" ?>>
                <td><a href="view_circuito.php?id_circuito=<?php echo $circuito->getValue('id_circuito') ?>"><span class="material-symbols-outlined">assignment</span></a></td>
                <td>
                    <?php
                        $bandera = $circuito->getValue("codigo_iso");
                        if ($bandera != 'xx'):
                    ?>
                        <span class="fi fi-<?php echo $circuito->getValue("codigo_iso"); ?>"></span>
                    <?php endif; ?>
                </td>

                <td><?php echo $circuito->getValue("nombre_circuito") ?></td>
                <td><a href="<?php echo $circuito->getValueEncoded('web_circuito') ?>" target="_blank" alt="Página web"><span class="material-symbols-outlined">web</span></a></td>
                <td><?php echo $circuito->getValueEncoded("direccion_circuito") ?></td>
                <td><?php echo $circuito->getValue("localidad_circuito") ?></td>
                <td><?php echo $circuito->getValueEncoded("telefono_circuito") ?></td>
                <td><?php echo $circuito->getValue("altitud") ?></td>
                <td><?php echo $circuito->getValue("longitud") ?></td>
                <td><?php echo $circuito->getValue("curvasizd") ?></td>
                <td><?php echo $circuito->getValue("curvasdcha") ?></td>
                <td><?php echo $circuito->getValue("velocidadmax") ?></td>
                <td>
                <img src="<?php echo IMAGE_CIRCUITO_DIRECTORY . ($circuito->getValueEncoded('silueta') ?: 'default.webp') ?>" class="foto foto-click" onclick="openModal(this.src, this.alt)" />
                </td>
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
            <a href="view_circuitos.php?start=<?php echo max($start - $pageSize, 0) ?>&amp;order=<?php echo $order ?>&amp;type=<?php echo $type ?>&amp;pageSize=<?php echo $pageSize ?>" class="btn-nav">&laquo; Página anterior</a>
        <?php endif; ?>
        
        &nbsp;
        
        <?php if ($start + $pageSize < $totalRows): ?>
            <a href="view_circuitos.php?start=<?php echo ($start + $pageSize) ?>&amp;order=<?php echo $order ?>&amp;type=<?php echo $type ?>&amp;pageSize=<?php echo $pageSize ?>" class="btn-nav">Página siguiente &raquo;</a>
        <?php endif; ?>
    </div> 
<?php

    displayPageFooter();
?>
