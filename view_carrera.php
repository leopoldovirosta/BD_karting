<?php
require_once "common.inc.php";
require_once "config.php";
require_once "carreras.class.php";

$id_carrera = isset($_GET["id_carrera"]) ? (int)$_GET["id_carrera"] : 0;
$id_cto = isset($_GET["id_cto"]) ? (int)$_GET["id_cto"] : 0;
$carrera = Carrera::getCarrera($id_carrera, $id_cto);

if (!$carrera) {
    displayPageHeader("Error");
    echo "<div>Carrera no encontrada.</div>";
    displayPageFooter();
    exit;
}

displayPageHeader("Ficha de carrera: " . $carrera->getValueEncoded("fecha_carrera") . " " . $carrera->getValueEncoded("nombre_circuito"));

?>
<div class="card-piloto">
    <div class="card-header-side">
    <div style="font-size: 3rem; margin-bottom: 10px;">🏁</div>
        <h3><?php echo $carrera->getValueEncoded("fecha_carrera") ?></h3>
        <p style="color: #3498db; font-weight: bold;"><?php echo $carrera->getValueEncoded("nombre_circuito") ?></p>
        <div class="badge-pista" style="margin-top: 20px; background: rgba(255,255,255,0.2); padding: 5px 15px; border-radius: 20px;">
            <?php echo $carrera->getValueEncoded("pista") ?>
        </div>
        <div class="badge-pista" style="margin-top: 20px; background: rgba(255,255,255,0.2); padding: 5px 15px; border-radius: 20px;">
            <?php if ($carrera->getValue("num_vueltas")) {
                     echo $carrera->getValue("num_vueltas") . " Vueltas";
                } else {
                    echo "Clasificación";
                } ?>
        </div><p>
        <p style="color: #3498db; font-weight: bold;"><?php echo $carrera->getValueEncoded("nombre_cto") ?></p>
    </div>

    <div class="card-body">
        <h2>Detalles Técnicos</h2>
        
        <div class="info-grid">
            <div class="info-item">
                <label>Día de competición</label>
                <span><?php echo $carrera->getValueEncoded("dia") ?></span>
            </div>
            <div class="info-item">
                <label>Carrera</label>
                <span><?php echo $carrera->getValueEncoded("nombre_carrera_tipo") ?></span>
            </div>
            <div class="info-item">
                <label>Temperatura Ambiente</label>
                <span><?php echo $carrera->getValue("temperatura") ?> ºC</span>
            </div>
            <div class="info-item">
                <label>Temperatura Asfalto</label>
                <span><?php echo $carrera->getValue("tasfalto") ?> ºC</span>
            </div>
            <div class="info-item">
                <label>Humedad Relativa</label>
                <span><?php echo $carrera->getValue("humedad") ?> %</span>
            </div>
            <div class="info-item">
                <label>Presión atmosférica</label>
                <span><?php echo $carrera->getValue("presion") ?> hPa</span>
            </div>
            <div class="info-item">
                <label>Viento</label>
                <span><?php echo $carrera->getValue("viento") ?> Km/h</span>
            </div>
            <div class="info-item">
                <label>Orientación</label>
                <span><?php echo $carrera->getValueEncoded("orientacion") ?></span>
            </div>
            <div class="info-item" style="grid-column: span 2;">
                <label>Circuito</label>
                <span style="font-size: 1.2rem; color: #2c3e50;">📍 <?php echo $carrera->getValueEncoded('nombre_circuito') ?></span>
            </div>
        </div>

        <a href="view_carreras.php" class="btn-back">&larr; Volver al panel de carrera</a>
    </div>
</div>


<?php displayPageFooter(); ?>
