<?php
require_once "common.inc.php";
require_once "config.php";
require_once "ruedas.class.php";

$id = isset($_GET["id_rueda"]) ? (int)$_GET["id_rueda"] : 0;

$rueda = Rueda::getRuedaById($id);

if (!$rueda) {
    displayPageHeader("Error");
    echo "<div>Rueda no encontrada.</div>";
    displayPageFooter();
    exit;
}

displayPageHeader("Ficha de rueda");

?>
<div class="circuito-card">
    <div class="circuito-header">
        <div class="header-info">
            <span class="pais-badge"><?php echo $rueda->getValueEncoded("nombre_pais"); ?></span>
            <h1><?php echo $rueda->getValueEncoded("modelo"); ?></h1>
            <p>
                <?php 
                    $logoMarca = trim((string)$rueda->getValue('logo_marca'));
                    $tieneLogoReal = !empty($logoMarca) && strtolower($logoMarca) !== 'default.webp';
                    $srcLogo = IMAGE_LOGOS_MARCAS_DIRECTORY . ($tieneLogoReal ? $rueda->getValueEncoded('logo_marca') : 'default.webp');
                ?>

                <?php if ($tieneLogoReal): ?>
                    <img src="<?php echo $srcLogo; ?>" alt="Logo <?php echo $rueda->getValueEncoded('nombre_marca'); ?>" />
                <?php else: ?>
                    <img src="<?php echo $srcLogo; ?>" style="cursor: default;" />
                <?php endif; ?>
            </p>
            <p class="nombre-marca"><?php echo $rueda->getValueEncoded("nombre_marca"); ?></p>
            <?php if ($rueda->getValueEncoded("pagina_web")): ?>
                <p>
                    <a href="<?php echo htmlspecialchars($rueda->getValue("pagina_web")); ?>" target="_blank">Visitar web</a>
                </p>
            <?php endif; ?>
        </div>
            <?php
                $fotoRueda = trim((string)$rueda->getValue('foto_rueda'));
                $tieneFotoReal = !empty($fotoRueda) && strtolower($fotoRueda) !== 'default.webp';
            ?>

            <?php if ($tieneFotoReal): ?>
                <div class="foto-ficha-container">
                    <img src="<?php echo IMAGE_RUEDAS_DIRECTORY . $rueda->getValueEncoded('foto_rueda'); ?>" 
                        onclick="openModal(this.src, this.alt)" 
                        alt="Foto <?php echo $rueda->getValueEncoded('modelo'); ?>">
                </div>
            <?php endif; ?>
    </div>

    <div class="circuito-grid">
        <div class="data-item">
            <span class="material-symbols-outlined">Partly_cloudy_day</span>
            <label>Tipo</label>
            <div class="value"><?php echo mostrarValor($rueda->getValueEncoded("tipo")); ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">outbound</span>
            <label>Compuesto</label>
            <div class="value"><?php echo mostrarValor($rueda->getValueEncoded("compuesto")); ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">category</span>
            <label>Categoría</label>
            <div class="value"><?php echo mostrarValor($rueda->getValueEncoded("categoria")); ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">width_wide</span> 
            <label>Tamaño delantera</label>
            <div class="value"><?php echo mostrarValor($rueda->getValueEncoded("tam_front")); ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">verified</span>
            <label>Homologación</label>
            <div class="value"><?php echo mostrarValor($rueda->getValueEncoded("homo_front")); ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">description</span>
            <label>Ficha Técnica</label>
            <div class="value">
                <?php if ($rueda->getValue("url_homo_front")): ?>
                    <a href="<?php echo htmlspecialchars($rueda->getValue("url_homo_front")); ?>" target="_blank">Visitar</a>
                <?php else: ?>
                    ---
                <?php endif; ?>
            </div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">width_wide</span>
            <label>Tamaño trasera</label>
            <div class="value"><?php echo mostrarValor($rueda->getValueEncoded("tam_rear")); ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">verified</span>
            <label>Homologación</label>
            <div class="value"><?php echo mostrarValor($rueda->getValueEncoded("homo_rear")); ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">description</span>
            <label>Ficha Técnica</label>
            <div class="value">
                <?php if ($rueda->getValue("url_homo_rear")): ?>
                    <a href="<?php echo htmlspecialchars($rueda->getValue("url_homo_rear")); ?>" target="_blank">Visitar</a>
                <?php else: ?>
                    ---
                <?php endif; ?>
            </div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">speed</span>
            <label>Máxima velocidad</label>
            <div class="value"><?php echo mostrarValor($rueda->getValueEncoded("max_velocidad")); ?></div>
        </div>
    </div>
    
    <div style="margin-top: 20px;">
    <?php 
        // Definimos la URL de retorno (p. ej., usando HTTP_REFERER o una URL por defecto)
        $url_retorno = $_SERVER['HTTP_REFERER'] ?? 'view_ruedas.php';
        ?>

        <a href="<?php echo htmlspecialchars($url_retorno); ?>" class="btn btn-nav">
            &laquo; Volver al listado
        </a>
    </div>
</div>
<?php displayPageFooter(); ?>
