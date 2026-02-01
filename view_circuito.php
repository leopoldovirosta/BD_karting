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
            <p class="nombre-oficial">Teléfono: <?php echo $circuito->getValue("telefono_circuito") ?></p>
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
            <div class="value"><?php echo $circuito->getValue("altitud") ?><small> m</small></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">straighten</span>
            <label>Longitud</label>
            <div class="value"><?php echo $circuito->getValue("longitud") ?><small> m</small></div>
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
            <div class="value"><?php echo $circuito->getValue("velocidadmax") ?><small> km/h</small></div>
        </div>
    </div>
    <?php 
    $record = Carrera::getRecordVuelta($id); 
    if ($record):
        $tiempo_limpio = ltrim($record['mejor_vuelta'], '0:');
        // Llamamos a la función que acabamos de guardar
        $v_media = Carrera::calcularVelocidadMedia(
        $circuito->getValue("longitud"), 
        $record['mejor_vuelta']
        );
    ?>
    <div class="record-container">
        <div class="record-header">
            <span class="material-symbols-outlined">timer</span>
            <h3>Récord de vuelta rápida</h3>
        </div>
        <div class="record-body">
            <div class="time-main"><?php echo $tiempo_limpio; ?></div>
            <div class="driver-info">
                <span class="driver-name"><?php echo $record['nombre_piloto'] . " " . $record['apellido_piloto'] ?></span>
                <span class="record-date"><?php echo $record['fecha_carrera'] ?></span>
            </div>
        </div>
        <div class="avg-speed-tag">
            Velocidad media: <strong><?php echo $v_media; ?> <small>km/h</small></strong>
        </div>

    </div>
    <?php endif; ?>
</div>

<?php       
$ganadores = Carrera::getEstadisticasGanadores($id);
if (count($ganadores) > 0):
?>  
             
<section class="carrera-stats">
    <h2>Muro de los Campeones</h2>
    <div class="stats-container">
        <?php foreach ($ganadores as $g): ?>
            <div class="stat-card">
                <img src="images/piloto/<?php echo $g['foto_piloto'] ?: 'default.jpg' ?>" class="foto-mini">
                <div class="victoria-count"><?php echo $g['victorias'] ?><span class="material-symbols-outlined">emoji_events</span></div> 
                <div class="label">Victorias</div>
                <p><strong><?php echo htmlspecialchars($g['nombre_piloto'] . " " . $g['apellido_piloto']) ?></strong></p>
            </div>
        <?php endforeach; ?>
     </div>

        <a href="view_circuitos.php" class="btn-back">&larr; Volver al panel de circuitos</a>
</section>
<?php endif; ?>

<?php displayPageFooter(); ?>

