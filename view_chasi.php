<?php
require_once "common.inc.php";
require_once "config.php";
require_once "chasis.class.php";

$id = isset($_GET["id_chasis"]) ? (int)$_GET["id_chasis"] : 0;

$chasis = Chasis::getChasisById($id);

if (!$chasis) {
    displayPageHeader("Error");
    echo "<div>Chasis no encontrado.</div>";
    displayPageFooter();
    exit;
}

displayPageHeader("Ficha de Chasis");

?>
<div class="circuito-card">
    <div class="circuito-header">
        <div class="header-info">
            <span class="pais-badge"><?php echo $chasis->getValueEncoded("nombre_pais"); ?></span>
            <h1><?php echo $chasis->getValueEncoded("modelo_chasis"); ?></h1>
            <p>
                <?php 
                    $logoMarca = trim((string)$chasis->getValue('logo_marca'));
                    $tieneLogoReal = !empty($logoMarca) && strtolower($logoMarca) !== 'default.webp';
                    $srcLogo = IMAGE_LOGOS_MARCAS_DIRECTORY . ($tieneLogoReal ? $chasis->getValueEncoded('logo_marca') : 'default.webp');
                ?>

                <?php if ($tieneLogoReal): ?>
                    <img src="<?php echo $srcLogo; ?>" alt="Logo <?php echo $chasis->getValueEncoded('nombre_marca'); ?>" />
                <?php else: ?>
                    <img src="<?php echo $srcLogo; ?>" style="cursor: default;" />
                <?php endif; ?>
            </p>
            <p class="nombre-marca"><?php echo $chasis->getValueEncoded("nombre_marca"); ?></p>
            <?php if ($chasis->getValueEncoded("pagina_web")): ?>
                <p>
                    <a href="<?php echo htmlspecialchars($chasis->getValue("pagina_web")); ?>" target="_blank">Visitar web</a>
                </p>
            <?php endif; ?>
        </div>
            <?php
                $fotoChasis = trim((string)$chasis->getValue('foto_chasis'));
                $tieneFotoReal = !empty($fotoChasis) && strtolower($fotoChasis) !== 'default.webp';
            ?>

            <?php if ($tieneFotoReal): ?>
                <div class="foto-ficha-container">
                    <img src="<?php echo IMAGE_CHASIS_DIRECTORY . $chasis->getValueEncoded('foto_chasis'); ?>" 
                        onclick="openModal(this.src, this.alt)" 
                        alt="Foto <?php echo $chasis->getValueEncoded('modelo_chasis'); ?>">
                </div>
            <?php endif; ?>
    </div>

    <div class="circuito-grid">
        <div class="data-item">
            <span class="material-symbols-outlined">build</span>
            <label>Material</label>
            <div class="value"><?php echo mostrarValor($chasis->getValueEncoded("material")); ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">outbound</span>
            <label>Diámetro tubo</label>
            <div class="value"><?php echo mostrarValor($chasis->getValueEncoded("tubo_diametro")); ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">arrow_range</span>
            <label>Distancia ejes</label>
            <div class="value"><?php echo mostrarValor($chasis->getValueEncoded("distancia_ejes")); ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">arrow_range</span> 
            <label>Eje trasero</label>
            <div class="value"><?php echo mostrarValor($chasis->getValueEncoded("eje_trasero")); ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">build</span>
            <label>Frenos</label>
            <div class="value"><?php echo mostrarValor($chasis->getValueEncoded("sistema_frenado")); ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">category</span>
            <label>Categorías</label>
            <div class="value"><?php echo mostrarValor($chasis->getValueEncoded("categoria_objetivo")); ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">calendar_today</span>
            <label>Año</label>
            <div class="value"><?php echo mostrarValor($chasis->getValueEncoded("ano")); ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">verified</span>
            <label>Homologación</label>
            <div class="value"><?php echo mostrarValor($chasis->getValueEncoded("homologacion")); ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">description</span>
            <label>Ficha Técnica</label>
            <div class="value">
                <?php if ($chasis->getValue("url_homologacion")): ?>
                    <a href="<?php echo htmlspecialchars($chasis->getValue("url_homologacion")); ?>" target="_blank">Visitar</a>
                <?php else: ?>
                    <div class="value">---</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div style="margin-top: 20px;">
    <?php 
        // Definimos la URL de retorno (p. ej., usando HTTP_REFERER o una URL por defecto)
        $url_retorno = $_SERVER['HTTP_REFERER'] ?? 'view_chasis.php';
        ?>

        <a href="<?php echo htmlspecialchars($url_retorno); ?>" class="btn btn-nav">
            &laquo; Volver al listado
        </a>
    </div>
</div>
<?php displayPageFooter(); ?>
