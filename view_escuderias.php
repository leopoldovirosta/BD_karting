<?php
require_once "common.inc.php";
require_once "config.php";
require_once "escuderias.class.php";
 
// 1. Detectamos el sentido (por defecto ASC)
$type = isset($_GET["type"]) && $_GET["type"] == "DESC" ? "DESC" : "ASC";

// 2. Limpiamos las variables
$search = isset($_GET["search"]) ? trim($_GET["search"]) : "";
$start = isset($_GET["start"]) ? (int)$_GET["start"] : 0;
$order = isset($_GET["order"]) ? preg_replace("/[^a-zA-Z_]/", "", $_GET["order"]) : "id_escuderia";
$pageSize = isset($_GET["pageSize"]) ? (int)$_GET["pageSize"] : PAGE_SIZE;

// 3. Llamamos al método
list($lista_escuderias, $totalRows) = Escuderia::getEscuderias($start, $pageSize, $order . " " . $type, $search);

displayPageHeader("Lista de escuderías");

?>
    <form action="view_escuderias.php" method="get" class="search-form">
        <!-- Ocultos para mantener ordenación y sentido -->
        <input type="hidden" name="order" value="<?php echo htmlspecialchars($order); ?>" />
        <input type="hidden" name="type" value="<?php echo htmlspecialchars($type); ?>" />
     <div class="tabla-controles">
        <div class="resultados-por-pagina">
            <label for="pageSize">Escuderías por página:</label>
            <select name="pageSize" id="pageSize" onchange="this.form.submit()">
                <?php foreach (array(5, 10, 20, 50) as $value): ?>
                <option value="<?php echo $value ?>" <?php if ($pageSize == $value) echo 'selected="selected"' ?>>
                        <?php echo $value ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="buscador-box">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search) ?>" placeholder="Buscar escudería..." />
            <button type="submit" class="btn-nav">Buscar</button>
            <?php if (!empty($search)): ?>
                <a href="view_escuderias.php" class="btn-nav">Limpiar filtro</a>
            <?php endif; ?>
        </div>
     </div>
    </form>
    <div class="table-responsive">
     <table>
        <thead>
            <tr>
            <?php
            $columns = array(
                "id_escuderia"      => "ID",
                "logo_escuderia"    => "Logo",
                "nombre_escuderia"  => "Marca",
                "codigo_iso"        => "País",
                "web_escuderia"     => "Web"
            );

            // Columnas no ordenables
            $noSortable = array("logo_escuderia", "web_escuderia");

            foreach ($columns as $colKey => $colName): 
                $nextType = ($order == $colKey && $type == "ASC") ? "DESC" : "ASC";
                $icon = ($type == "ASC") ? "▲" : "▼";
            ?>
                <th>
                    <?php if (in_array($colKey, $noSortable)): ?>
                        <?php echo htmlspecialchars($colName); ?>
                    <?php else: ?>
                        <a href="view_escuderias.php?order=<?php echo $colKey ?>&amp;type=<?php echo $nextType ?>&amp;pageSize=<?php echo $pageSize ?>&amp;search=<?php echo urlencode($search) ?>">
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
        <tbody>
<?php
    $rowCount = 0;
    foreach($lista_escuderias as $escuderia):
        $rowCount++;
?>
        <tr<?php if ($rowCount % 2 == 0) echo " class='alt'" ?>>
            <td><?php echo $escuderia->getValue('id_escuderia') ?></td>
            <td>
                <?php 
                    $logoEscuderia = trim((string)$escuderia->getValue('logo_escuderia'));
                    $tieneLogoReal = !empty($logoEscuderia) && strtolower($logoEscuderia) !== 'default.webp';
                    $srcLogo = IMAGE_LOGOS_ESCUDERIAS_DIRECTORY . ($tieneLogoReal ? $escuderia->getValueEncoded('logo_escuderia') : 'default.webp');
                ?>

                <?php if ($tieneLogoReal): ?>
                    <img src="<?php echo $srcLogo; ?>" class="foto foto-click" onclick="openModal(this.src, this.alt)" alt="Logo <?php echo $escuderia->getValueEncoded('nombre_escuderia'); ?>" />
                <?php else: ?>
                    <img src="<?php echo $srcLogo; ?>" class="foto" alt="------" style="cursor: default;" />
                <?php endif; ?>
            </td>
            <td><?php echo $escuderia->getValueEncoded("nombre_escuderia") ?></td>
            <td class="text-center">
                <?php
                    $bandera = $escuderia->getValue("codigo_iso");
                    if ($bandera && $bandera !== 'xx'):
                ?>
                    <span class="fi fi-<?php echo strtolower($escuderia->getValue("codigo_iso")); ?>"></span>
                <?php else: ?>
                    ---
                <?php endif; ?>
            </td>
            <td class="text-center">
                <?php if ($escuderia->getValue('web_escuderia')): ?>
                    <a href="<?php echo htmlspecialchars($escuderia->getValue('web_escuderia')); ?>" target="_blank" rel="noopener noreferrer">Visitar</a>
                <?php else: ?>
                    ---
                <?php endif; ?>
            </td>
        </tr>
<?php
    endforeach;
?>
        </tbody>
     </table>
    </div>

    <!-- PAGINACIÓN -->
    <div class="pagination-container">
        <p class="info-text">
            Mostrando <?php echo ($totalRows > 0) ? $start + 1 : 0; ?> - <?php echo min($start + $pageSize, $totalRows) ?> de <?php echo $totalRows ?>
        </p>
        
        <?php if ($start > 0): ?>
            <a href="view_escuderias.php?start=<?php echo max($start - $pageSize, 0) ?>&amp;order=<?php echo $order ?>&amp;type=<?php echo $type ?>&amp;pageSize=<?php echo $pageSize ?>&amp;search=<?php echo urlencode($search) ?>" class="btn-nav">&laquo; Página anterior</a>
        <?php endif; ?>
        
        &nbsp;
        
        <?php if ($start + $pageSize < $totalRows): ?>
            <a href="view_escuderias.php?start=<?php echo ($start + $pageSize) ?>&amp;order=<?php echo $order ?>&amp;type=<?php echo $type ?>&amp;pageSize=<?php echo $pageSize ?>&amp;search=<?php echo urlencode($search) ?>" class="btn-nav">Página siguiente &raquo;</a>
        <?php endif; ?>
    </div> 

<?php
    displayPageFooter();
?>
