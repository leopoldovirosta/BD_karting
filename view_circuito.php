<?php
require_once "common.inc.php";
require_once "config.php";
require_once "circuitos.class.php";
require_once "resultados.class.php";

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
            <span class="pais-badge"><?php echo $circuito->getValueEncoded("nombre_pais") ?></span>
            <h1><?php echo $circuito->getValue("nombre_circuito") ?></h1>

            <?php 
                $logoMarca = trim((string)$circuito->getValue('logo_circuito'));
                $tieneLogoReal = !empty($logoMarca) && strtolower($logoMarca) !== 'default.webp';
                $srcLogo = IMAGE_LOGOS_CIRCUITOS_DIRECTORY . ($tieneLogoReal ? $circuito->getValueEncoded('logo_circuito') : 'default.webp');
            ?>

            <?php if ($tieneLogoReal): ?>
                <p>
                    <img src="<?php echo $srcLogo; ?>" alt="Logo <?php echo $circuito->getValueEncoded('nombre_circuito'); ?>" />
                </p>
            <?php endif; ?>

            <p class="nombre-oficial"><?php echo $circuito->getValue("direccion_circuito") . " (" . $circuito->getValue("localidad_circuito") .")" ?></p>
            <p class="nombre-oficial">Teléfono: <?php echo $circuito->getValue("telefono_circuito") ?></p>
            <p class="nombre-oficial"><a href="<?php echo $circuito->getValue("web_circuito") ?>" target="_blank"><span class="material-symbols-outlined">web</span</a></p>
        </div>
        <div class="circuito-mapa">
            <img src="<?php echo IMAGE_CIRCUITO_DIRECTORY . ($circuito->getValue('silueta') ?: 'default.webp') ?>" alt="Mapa del trazado">
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
    $record = Resultado::getRecordVuelta($id); 
    if ($record):
        $tiempo_limpio = ltrim($record['mejor_vuelta'], '0:');
        // Llamamos a la función que acabamos de guardar
        $v_media = Resultado::calcularVelocidadMedia(
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

    <?php       
    $ganadores = Resultado::getEstadisticasGanadores($id);
    if (count($ganadores) > 0):
    ?>  
                
    <div class="contenedor-seccion-podios">
        <h2 class="text-center">Muro de los Campeones</h2>
        <div class="podium-container">
            <?php foreach ($ganadores as $g): ?>
                <div class="pilot-card">
                    <div>
                        <img src="images/pilotos/<?php echo $g['foto_piloto'] ?: 'default.webp' ?>"
                            class="foto-perfil">
                        <h2><?php echo $g['victorias'] ?><span class="material-symbols-outlined">emoji_events</span></h2> 
                        <a href="view_piloto.php?id_piloto=<?php echo (int)$g['id_piloto']; ?>" class="enlace-piloto" target="_blank" rel="noopener noreferrer"><?php echo htmlspecialchars($g['nombre_piloto'] . " " . $g['apellido_piloto']) ?></a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
    <?php endif; ?>
    <div>
            <a href="view_circuitos.php" class="btn btn-nav">&larr; Volver al listado de circuitos</a>
    </div>
</div>
<?php displayPageFooter(); ?>
