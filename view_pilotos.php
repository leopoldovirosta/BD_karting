<?php

require_once "common.inc.php";
require_once "config.php";
require_once "pilotos.class.php";
 
// 1. Detectamos el sentido (por defecto ASC)
$type = isset($_GET["type"]) && $_GET["type"] == "DESC" ? "DESC" : "ASC";

// 2. Limpiamos las variables
$search = isset($_GET["search"]) ? $_GET["search"] : "";
$start = isset($_GET["start"]) ? (int)$_GET["start"] : 0;
$order = isset($_GET["order"]) ? preg_replace("/[^a-zA-Z_]/", "", $_GET["order"]) : "id_piloto";
$pageSize = isset($_GET["pageSize"]) ? (int)$_GET["pageSize"] : PAGE_SIZE;

// 3. Llamamos al método (asegúrate de que tu SQL en Piloto ahora use $order y $type)
list($pilotos, $totalRows) = Piloto::getPilotos($start, $pageSize, $order . " " . $type, $search);

displayPageHeader("Lista de pilotos");

?>
    <form action="view_pilotos.php" method="get" class="search-form">
        <input type="hidden" name="order" value="<?php echo $order ?>" />
        <label for="pageSize">Pilotos por página:</label>
        <select name="pageSize" id="pageSize" onchange="this.form.submit()">
            <?php foreach (array(5, 10, 20, 50) as $value): ?>
               <option value="<?php echo $value ?>" <?php if ($pageSize == $value) echo 'selected="selected"' ?>>
                    <?php echo $value ?>
                </option>
            <?php endforeach; ?>
        </select>
        <br><br>
        <input type="text" name="search" value="<?php echo htmlspecialchars($search) ?>" placeholder="Buscar piloto..." />
        <button type="submit" class="btn-nav">Buscar</button>
            <?php if (!empty($search)): ?>
                <a href="view_pilotos.php">Limpiar filtro</a>
            <?php endif; ?>
    </form>
    <br>
    <table>
    <tr>
        <?php
        // Definimos las columnas que queremos mostrar
        $columns = array(
            "id_piloto" => "ID Piloto",
            "nombre_piloto" => "Nombre",
            "apellido_piloto" => "Apellido",
            "fecha_nacimiento" => "Fecha nacimiento",
            "web_piloto" => "Página web",
            "email_piloto" => "Email",
            "foto_piloto" => "Foto",
            "nombre_region" => "Region",
            "nombre_escuderia" => "Escudería",
            "nombre_patrocinador" => "Patrocinador"
        );

        foreach ($columns as $colKey => $colName): 
                // Si la columna es la actual, el siguiente clic debe ser el opuesto
                $nextType = ($order == $colKey && $type == "ASC") ? "DESC" : "ASC";
                $icon = ($type == "ASC") ? "▲" : "▼";
            ?>
                <th>
                    <a href="view_pilotos.php?order=<?php echo $colKey ?>&amp;type=<?php echo $nextType ?>&amp;pageSize=<?php echo $pageSize ?>">
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
    
    foreach($pilotos as $piloto):
        $rowCount++;
?>
        <tr<?php if ($rowCount % 2 == 0) echo " class='alt'" ?>>
            <td><a href="view_piloto.php?id_piloto=<?php echo $piloto->getValue('id_piloto') ?>"><?php echo $piloto->getValue("id_piloto")?></a></td>
            <td><?php echo $piloto->getValueEncoded("nombre_piloto") ?></td>
            <td><?php echo $piloto->getValueEncoded("apellido_piloto") ?></td>
            <td><?php echo $piloto->getValueEncoded("fecha_nacimiento") ?></td>
            <td><a href="<?php echo $piloto->getValueEncoded('web_piloto') ?>" target="_blank" alt="Página web"><?php echo $piloto->getValueEncoded("web_piloto") ?></a></td>
            <td><a href="mailto:<?php echo $piloto->getValueEncoded('email_piloto') ?>"><?php echo $piloto->getValueEncoded("email_piloto") ?></a></td>
            <td>
            <img src="<?php echo IMAGE_PILOT_DIRECTORY . ($piloto->getValueEncoded('foto_piloto') ?: 'default.jpg') ?>" class="foto foto-click" onclick="openModal(this.src, this.alt)" />
            </td>
            <td><?php echo $piloto->getValueEncoded("nombre_region") ?></td>
            <td><?php echo $piloto->getValueEncoded("nombre_escuderia") ?></td>
            <td><?php echo $piloto->getValueEncoded("nombre_patrocinador") ?></td>
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
            <a href="view_pilotos.php?start=<?php echo max($start - $pageSize, 0) ?>&amp;order=<?php echo $order ?>&amp;type=<?php echo $type ?>&amp;pageSize=<?php echo $pageSize ?>" class="btn-nav">&laquo; Página anterior</a>
        <?php endif; ?>
        
        &nbsp;
        
        <?php if ($start + $pageSize < $totalRows): ?>
            <a href="view_pilotos.php?start=<?php echo ($start + $pageSize) ?>&amp;order=<?php echo $order ?>&amp;type=<?php echo $type ?>&amp;pageSize=<?php echo $pageSize ?>" class="btn-nav">Página siguiente &raquo;</a>
        <?php endif; ?>
    </div> 
<?php

    displayPageFooter();
?>
