<?php

require_once "common.inc.php";
require_once "config.php";
require_once "motores.class.php";
 
// 1. Detectamos el sentido (por defecto ASC)
$type = isset($_GET["type"]) && $_GET["type"] == "DESC" ? "DESC" : "ASC";

// 2. Limpiamos las variables
$search = isset($_GET["search"]) ? trim($_GET["search"]) : "";
$start = isset($_GET["start"]) ? (int)$_GET["start"] : 0;
$order = isset($_GET["order"]) ? preg_replace("/[^a-zA-Z_]/", "", $_GET["order"]) : "id_motor";
$pageSize = isset($_GET["pageSize"]) ? (int)$_GET["pageSize"] : PAGE_SIZE;

// 3. Llamamos al método
list($lista_motores, $totalRows) = Motor::getMotores($start, $pageSize, $order . " " . $type, $search);

displayPageHeader("Lista de motores);

?>
    <form action="view_motores.php" method="get" class="search-form">
        <!-- Ocultos para mantener ordenación y sentido -->
        <input type="hidden" name="order" value="<?php echo htmlspecialchars($order); ?>" />
        <input type="hidden" name="type" value="<?php echo htmlspecialchars($type); ?>" />

        <label for="pageSize">Motores por página:</label>
        <select name="pageSize" id="pageSize" onchange="this.form.submit()">
            <?php foreach (array(5, 10, 20, 50) as $value): ?>
               <option value="<?php echo $value ?>" <?php if ($pageSize == $value) echo 'selected="selected"' ?>>
                    <?php echo $value ?>
                </option>
            <?php endforeach; ?>
        </select>
        <br><br>
        <input type="text" name="search" value="<?php echo htmlspecialchars($search) ?>" placeholder="Buscar motores..." />
        <button type="submit" class="btn-nav">Buscar</button>
        <?php if (!empty($search)): ?>
            <a href="view_motores.php" class="btn-nav">Limpiar filtro</a>
        <?php endif; ?>
    </form>
    <br>
    <table>
        <thead>
            <tr>
            <?php
            $columns = array(
                "id_motor"              => "ID",
                "logo_marca"            => "Logo",
                "nombre_marca"          => "Marca",
                "codigo_iso"            => "País",
                "modelo"                => "Modelo",
                "clase"                 => "Clase",
                "cilindrada"            => "Cilindrada",
                "admision"              => "Admision",
                "encendido"             => "Encendido",
                "refrigeracion"         => "Refrigeracion",
                "starter"               => "Starter",
                "carburador"            => "Carburador",
                "homologacion"          => "Homologación",
                "url_homologacion"      => "Ficha",
                "foto_chasis"           => "Foto"
            );

            // Columnas no ordenables
            $noSortable = array("logo_marca", "url_homologacion", "foto_chasis");

            foreach ($columns as $colKey => $colName): 
                $nextType = ($order == $colKey && $type == "ASC") ? "DESC" : "ASC";
                $icon = ($type == "ASC") ? "▲" : "▼";
            ?>
                <th>
                    <?php if (in_array($colKey, $noSortable)): ?>
                        <?php echo htmlspecialchars($colName); ?>
                    <?php else: ?>
                        <a href="view_chasis.php?order=<?php echo $colKey ?>&amp;type=<?php echo $nextType ?>&amp;pageSize=<?php echo $pageSize ?>&amp;search=<?php echo urlencode($search) ?>">
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
    foreach($lista_chasis as $chasi):
        $rowCount++;
?>
        <tr<?php if ($rowCount % 2 == 0) echo " class='alt'" ?>>
            <td><a href="view_chasi.php?id_chasis=<?php echo $chasi->getValue('id_chasis') ?>"><?php echo $chasi->getValue("id_chasis")?></a></td>
            <td>
                <?php 
                    $logoMarca = trim((string)$chasi->getValue('logo_marca'));
                    $tieneLogoReal = !empty($logoMarca) && strtolower($logoMarca) !== 'default.webp';
                    $srcLogo = IMAGE_LOGOS_MARCAS_DIRECTORY . ($tieneLogoReal ? $chasi->getValueEncoded('logo_marca') : 'default.webp');
                ?>

                <?php if ($tieneLogoReal): ?>
                    <img src="<?php echo $srcLogo; ?>" class="foto foto-click" onclick="openModal(this.src, this.alt)" alt="Logo <?php echo $chasi->getValueEncoded('nombre_marca'); ?>" />
                <?php else: ?>
                    <img src="<?php echo $srcLogo; ?>" class="foto" alt="------" style="cursor: default;" />
                <?php endif; ?>
            </td>
            <td><?php echo $chasi->getValueEncoded("nombre_marca") ?></td>
            <td>
                <?php
                    $bandera = $chasi->getValue("codigo_iso");
                    if ($bandera != 'xx'):
                ?>
                    <span class="fi fi-<?php echo $chasi->getValue("codigo_iso"); ?>"></span>
                <?php endif; ?>
            </td>
            <td><?php echo $chasi->getValueEncoded("modelo_chasis") ?></td>
            <td><?php echo $chasi->getValueEncoded("material") ?></td>
            <td><?php echo $chasi->getValueEncoded("tubo_diametro") ?></td>
            <td><?php echo $chasi->getValueEncoded("distancia_ejes") ?></td>
            <td><?php echo $chasi->getValueEncoded("eje_trasero") ?></td>
            <td><?php echo $chasi->getValueEncoded("sistema_frenado") ?></td>
            <td><?php echo $chasi->getValueEncoded("categoria_objetivo") ?></td>
            <td><?php echo $chasi->getValueEncoded("ano") ?></td>
            <td class="text-center"><?php echo $chasi->getValueEncoded("homologacion") ?></td>
            <td>
                <?php if ($chasi->getValue('url_homologacion')): ?>
                    <a href="<?php echo $chasi->getValueEncoded('url_homologacion') ?>" target="_blank">Ficha</a>
                <?php endif; ?>
            </td>
            <td>
                <?php
                    $fotoChasis = trim((string)$chasi->getValue('foto_chasis'));
                    $tieneFotoReal = !empty($fotoChasis) && strtolower($fotoChasis) !== 'default.jpg';
                    $srcFoto = IMAGE_CHASIS_DIRECTORY . ($tieneFotoReal ? $chasi->getValueEncoded('foto_chasis') : 'default.jpg');
                ?>

                <?php if ($tieneFotoReal): ?>
                    <img src="<?php echo $srcFoto; ?>" class="foto foto-click" onclick="openModal(this.src, this.alt)" alt="Foto <?php echo $chasi->getValueEncoded('modelo_chasis'); ?>" />
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

    <!-- PAGINACIÓN -->
    <div class="pagination-container">
        <p class="info-text">
            Mostrando <?php echo ($totalRows > 0) ? $start + 1 : 0; ?> - <?php echo min($start + $pageSize, $totalRows) ?> de <?php echo $totalRows ?>
        </p>
        
        <?php if ($start > 0): ?>
            <a href="view_chasis.php?start=<?php echo max($start - $pageSize, 0) ?>&amp;order=<?php echo $order ?>&amp;type=<?php echo $type ?>&amp;pageSize=<?php echo $pageSize ?>&amp;search=<?php echo urlencode($search) ?>" class="btn-nav">&laquo; Página anterior</a>
        <?php endif; ?>
        
        &nbsp;
        
        <?php if ($start + $pageSize < $totalRows): ?>
            <a href="view_chasis.php?start=<?php echo ($start + $pageSize) ?>&amp;order=<?php echo $order ?>&amp;type=<?php echo $type ?>&amp;pageSize=<?php echo $pageSize ?>&amp;search=<?php echo urlencode($search) ?>" class="btn-nav">Página siguiente &raquo;</a>
        <?php endif; ?>
    </div> 

<?php
    displayPageFooter();
?>
