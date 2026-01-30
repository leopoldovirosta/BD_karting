<?php
require_once "common.inc.php";
require_once "config.php";
require_once "circuitos.class.php";

$id = isset($_GET["id_circuito"]) ? (int)$_GET["id_circuito"] : 0;
$circuito = Circuito::getCircuito($id);

if (!$circuito) {
    displayPageHeader("Error");
    echo "<div>Circuito no encontrado.</div>";
    displayPageFooter();
    exit;
}

displayPageHeader("Ficha del Circuito");

?>
<div class="circuito-card">
    <div class="circuito-header">
        <div class="header-info">
            <span class="pais-badge"><?php echo $circuito->getValue("nombre_area") ?></span>
            <h1><?php echo $circuito->getValue("nombre_circuito") ?></h1>
            <p class="nombre-oficial"><?php echo $circuito->getValue("nombre_circuito") ?></p>
        </div>
        <div class="circuito-mapa">
            <img src="<?php echo IMAGE_CIRCUITO_DIRECTORY . ($circuito->getValue('silueta') ?: 'default.jpg') ?>" alt="Mapa del trazado">
        </div>
    </div>

    <div class="circuito-grid">
        <div class="data-item">
            <label>Página web</label>
            <div class="value"><?php echo $circuito->getValue("web_circuito") ?></div>
        </div>
        <div class="data-item">
            <label>Longitud</label>
            <div class="value"><?php echo $circuito->getValue("longitud") ?> km</div>
        </div>
        <div class="data-item">
            <label>Curvas</label>
            <div class="value"><?php echo $circuito->getValue("curvas") ?></div>
        </div>
        <div class="data-item">
            <label>Record de velocidad</label>
            <div class="value"><?php echo $circuito->getValue("velocidadmax") ?> km</div>
        </div>
    </div>

    <div class="circuito-footer">
        <div class="record-box">
            <label>Récord de vuelta</label>
            <div class="record-value"><?php echo $circuito->getValue("altitud") ?></div>
            <div class="record-author"><?php echo $circuito->getValue("localidad_circuito") ?> (<?php echo $circuito->getValue("localidad_circuito") ?>)</div>
        </div>

        <a href="view_circuitos.php" class="btn-back">&larr; Volver al panel de circuitos</a>
    </div>
</div>

<?php displayPageFooter(); ?>

