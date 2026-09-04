<?php
require_once "common.inc.php";
require_once "config.php";
require_once "ruedas.class.php";
 
// 1. Detectamos el sentido (por defecto ASC)
$type = isset($_GET["type"]) && $_GET["type"] == "DESC" ? "DESC" : "ASC";

// 2. Limpiamos las variables
$search = isset($_GET["search"]) ? trim($_GET["search"]) : "";
$start = isset($_GET["start"]) ? (int)$_GET["start"] : 0;
$order = isset($_GET["order"]) ? preg_replace("/[^a-zA-Z_]/", "", $_GET["order"]) : "id_rueda";
$pageSize = isset($_GET["pageSize"]) ? (int)$_GET["pageSize"] : PAGE_SIZE;

// 3. Llamamos al método
list($lista_ruedas, $totalRows) = Rueda::getRuedas($start, $pageSize, $order . " " . $type, $search);

displayPageHeader("Lista de ruedas");

?>
    <form action="view_ruedas.php" method="get" class="search-form">
        <!-- Ocultos para mantener ordenación y sentido -->
        <input type="hidden" name="order" value="<?php echo htmlspecialchars($order); ?>" />
        <input type="hidden" name="type" value="<?php echo htmlspecialchars($type); ?>" />
    <div class="tabla-controles">
      <div class="resultados-por-pagina">
        <label for="pageSize">Ruedas por página:</label>
        <select name="pageSize" id="pageSize" class="form-control" onchange="this.form.submit()">
            <?php foreach (array(5, 10, 20, 50) as $value): ?>
               <option value="<?php echo $value ?>" <?php if ($pageSize == $value) echo 'selected="selected"' ?>>
                    <?php echo $value ?>
                </option>
            <?php endforeach; ?>
        </select>
      </div>
      <div class="buscador-box">
        <input type="text" name="search" value="<?php echo htmlspecialchars($search) ?>" placeholder="Buscar ruedas..." />
        <button type="submit" class="btn-nav">Buscar</button>
        <?php if (!empty($search)): ?>
            <a href="view_ruedas.php">Limpiar filtro</a>
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
                "id_rueda"              => "ID",
                "logo_marca"            => "Logo",
                "nombre_marca"          => "Marca",
                "codigo_iso"            => "País",
                "modelo"                => "Modelo",
                "tipo"                  => "Tipo",
                "compuesto"             => "Compuesto",
                "categoria"             => "Categoría",
                "tam_front"             => "Delanteras",
                "homo_front"            => "Homologación",
                "url_homo_front"        => "Ficha",
                "tam_rear"              => "Traseras",
                "homo_rear"             => "Homologación",
                "url_homo_rear"         => "Ficha",
                "foto_rueda"            => "Foto"
            );

            // Columnas no ordenables
            $noSortable = array("logo_marca", "url_homo_front", "url_homo_rear", "foto_rueda");

            foreach ($columns as $colKey => $colName): 
                $nextType = ($order == $colKey && $type == "ASC") ? "DESC" : "ASC";
                $icon = ($type == "ASC") ? "▲" : "▼";
            ?>
                <th>
                    <?php if (in_array($colKey, $noSortable)): ?>
                        <?php echo htmlspecialchars($colName); ?>
                    <?php else: ?>
                        <a href="view_ruedas.php?order=<?php echo $colKey ?>&amp;type=<?php echo $nextType ?>&amp;pageSize=<?php echo $pageSize ?>&amp;search=<?php echo urlencode($search) ?>">
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
    foreach($lista_ruedas as $rueda):
        $rowCount++;
?>
        <tr<?php if ($rowCount % 2 == 0) echo " class='alt'" ?>>
            <td><a href="view_rueda.php?id_rueda=<?php echo $rueda->getValue('id_rueda') ?>"><span class="material-symbols-outlined">assignment</span></a></td>
            <td>
                <?php 
                    $logoMarca = trim((string)$rueda->getValue('logo_marca'));
                    $tieneLogoReal = !empty($logoMarca) && strtolower($logoMarca) !== 'default.webp';
                    $srcLogo = IMAGE_LOGOS_MARCAS_DIRECTORY . ($tieneLogoReal ? $rueda->getValueEncoded('logo_marca') : 'default.webp');
                ?>

                <?php if ($tieneLogoReal): ?>
                    <img src="<?php echo $srcLogo; ?>" class="foto foto-click" onclick="openModal(this.src, this.alt)" alt="Logo <?php echo $rueda->getValueEncoded('nombre_marca'); ?>" />
                <?php else: ?>
                    <img src="<?php echo $srcLogo; ?>" class="foto" alt="------" style="cursor: default;" />
                <?php endif; ?>
            </td>
            <td><?php echo $rueda->getValueEncoded("nombre_marca") ?></td>
            <td>
                <?php
                    $bandera = $rueda->getValue("codigo_iso");
                    if ($bandera != 'xx'):
                ?>
                    <span class="fi fi-<?php echo $rueda->getValue("codigo_iso"); ?>"></span>
                <?php endif; ?>
            </td>
            <td><?php echo $rueda->getValueEncoded("modelo") ?></td>
            <td class="text-center"><?php echo mostrarValor($rueda->getValueEncoded("tipo")); ?></td>
            <td class="text-center"><?php echo mostrarValor($rueda->getValueEncoded("compuesto")); ?></td>
            <td class="text-center"><?php echo mostrarValor($rueda->getValueEncoded("categoria")); ?></td>
            <td class="text-center"><?php echo mostrarValor($rueda->getValueEncoded("tam_front")); ?></td>
            <td class="text-center"><?php echo mostrarValor($rueda->getValueEncoded("homo_front")); ?></td>
            <td class="text-center">
                <?php if ($rueda->getValue('url_homo_front')): ?>
                    <a href="<?php echo htmlspecialchars($rueda->getValue('url_homo_front')); ?>" target="_blank"><span class="material-symbols-outlined">file_open</span</a>
                <?php else: ?>
                    ---
                <?php endif; ?>
            </td>
            <td class="text-center"><?php echo mostrarValor($rueda->getValueEncoded("tam_rear")); ?></td>
            <td class="text-center"><?php echo mostrarValor($rueda->getValueEncoded("homo_rear")); ?></td>
            <td class="text-center">
                <?php if ($rueda->getValue('url_homo_rear')): ?>
                    <a href="<?php echo htmlspecialchars($rueda->getValue('url_homo_rear')); ?>" target="_blank"><span class="material-symbols-outlined">file_open</span</a>
                <?php else: ?>
                    ---
                <?php endif; ?>
            </td>
            <td>
                <?php
                    $fotoRueda = trim((string)$rueda->getValue('foto_rueda'));
                    $tieneFotoReal = !empty($fotoRueda) && strtolower($fotoRueda) !== 'default.webp';
                    $srcFoto = IMAGE_RUEDAS_DIRECTORY . ($tieneFotoReal ? $rueda->getValueEncoded('foto_rueda') : 'default.webp');
                ?>

                <?php if ($tieneFotoReal): ?>
                    <img src="<?php echo $srcFoto; ?>" class="foto foto-click" onclick="openModal(this.src, this.alt)" alt="Foto <?php echo $rueda->getValueEncoded('modelo'); ?>" />
                <?php else: ?>
                    <img src="<?php echo $srcFoto; ?>" class="foto" alt="------" style="cursor: default;" />
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
            <a href="view_ruedas.php?start=<?php echo max($start - $pageSize, 0) ?>&amp;order=<?php echo $order ?>&amp;type=<?php echo $type ?>&amp;pageSize=<?php echo $pageSize ?>&amp;search=<?php echo urlencode($search) ?>" class="btn-nav">&laquo; Página anterior</a>
        <?php endif; ?>
        
        &nbsp;
        
        <?php if ($start + $pageSize < $totalRows): ?>
            <a href="view_ruedas.php?start=<?php echo ($start + $pageSize) ?>&amp;order=<?php echo $order ?>&amp;type=<?php echo $type ?>&amp;pageSize=<?php echo $pageSize ?>&amp;search=<?php echo urlencode($search) ?>" class="btn-nav">Página siguiente &raquo;</a>
        <?php endif; ?>
    </div> 

<?php
    displayPageFooter();
?>
