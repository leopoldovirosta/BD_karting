<?php
require_once "common.inc.php";
require_once "config.php";
require_once "circuitos.class.php";
require_once "carreras.class.php";

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
            <span class="pais-badge"><?php echo $circuito->getValueEncoded("nombre_area") ?></span>
            <h1><?php echo $circuito->getValue("nombre_circuito") ?></h1>
            <p class="nombre-oficial"><?php echo $circuito->getValue("direccion_circuito") . " (" . $circuito->getValue("localidad_circuito") .")" ?></p>
            <p class="nombre-oficial"><?php echo $circuito->getValue("telefono_circuito") ?></p>
            <p class="nombre-oficial"><a href="<?php echo $circuito->getValue("web_circuito") ?>" target="_blank">Visitar</a></p>
        </div>
        <div class="circuito-mapa">
            <img src="<?php echo IMAGE_CIRCUITO_DIRECTORY . ($circuito->getValue('silueta') ?: 'default.jpg') ?>" alt="Mapa del trazado">
        </div>
    </div>

    <div class="circuito-grid">
        <div class="data-item">
            <span class="material-symbols-outlined">filter_hdr</span>
            <label>Altitud</label>
            <div class="value"><?php echo $circuito->getValue("altitud") ?> m</div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">straighten</span>
            <label>Longitud</label>
            <div class="value"><?php echo $circuito->getValue("longitud") ?> km</div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">conversion_path</span>
            <label>Curvas IZQ</label>
            <div class="value"><?php echo $circuito->getValue("curvasizd") ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">conversion_path</span> 
            <label>Curvas DCHA</label>
            <div class="value"><?php echo $circuito->getValue("curvasdcha") ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">speed</span>
            <label>Velocidad Max</label>
            <div class="value"><?php echo $circuito->getValue("velocidadmax") ?> km/h</div>
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

<?php       
$ganadores = Carrera::getEstadisticasGanadores($id);
if (count($ganadores) > 0):
?>  
             
<section class="carrera-stats">
    <h2>Récords del Circuito</h2>
    <div class="stats-container">
        <?php foreach ($ganadores as $g): ?>
            <div class="stat-card">
                <img src="images/piloto/<?php echo $g['foto_piloto'] ?: 'default.jpg' ?>" class="foto-mini">
                <div class="victoria-count"><?php echo $g['victorias'] ?></div> 
                <div class="label">Victorias</div>
                <p><strong><?php echo htmlspecialchars($g['nombre_piloto'] . " " . $g['apellido_piloto']) ?></strong></p>
            </div>
        <?php endforeach; ?>
     </div>
</section>
<?php endif; ?>

<?php displayPageFooter(); ?>

