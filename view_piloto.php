<?php
require_once "common.inc.php";
require_once "config.php";
require_once "pilotos.class.php";

$id = isset($_GET["id_piloto"]) ? (int)$_GET["id_piloto"] : 0;
$piloto = Piloto::getPiloto($id);

if (!$piloto) {
    displayPageHeader("Error");
    echo "<div>Piloto no encontrado.</div>";
    displayPageFooter();
    exit;
}

displayPageHeader("Ficha del Piloto: " . $piloto->getValueEncoded("nombre_piloto") . " " . $piloto->getValueEncoded("apellido_piloto"));

?>
<div class="card-piloto">
    <div class="card-header-side">
        <img src="<?php echo IMAGE_PILOT_DIRECTORY . ($piloto->getValue("foto_piloto") ?: 'default.jpg') ?>" 
             alt="Foto de <?php echo $piloto->getValueEncoded('nombre_piloto') ?>" 
             class="foto-perfil" />
        <h3><?php echo $piloto->getValueEncoded("nombre_piloto") ?></h3>
        <p style="color: #3498db;"><?php echo $piloto->getValueEncoded("nombre_escuderia") ?></p>
    </div>

    <div class="card-body">
        <h2>Detalles Técnicos</h2>
        
        <div class="info-grid">
            <div class="info-item">
                <label>Apellido</label>
                <span><?php echo $piloto->getValueEncoded("apellido_piloto") ?></span>
            </div>
            <div class="info-item">
                <label>Fecha de Nacimiento</label>
                <span><?php echo $piloto->getValueEncoded("fecha_nacimiento") ?></span>
            </div>
            <div class="info-item">
                <label>Federación</label>
                <span><?php echo $piloto->getValueEncoded("nombre_federacion") ?></span>
            </div>
            <div class="info-item">
                <label>Patrocinador</label>
                <span><?php echo $piloto->getValueEncoded("nombre_sponsor") ?></span>
            </div>
            <div class="info-item" style="grid-column: span 2;">
                <label>Email de contacto</label>
                <span><a href="mailto:<?php echo $piloto->getValueEncoded('email_piloto') ?>" style="color: inherit; text-decoration: none;"><?php echo $piloto->getValueEncoded("email_piloto") ?></a></span>
            </div>
            <div class="info-item" style="grid-column: span 2;">
                <label>Página web</label>
                <span><a href="<?php echo $piloto->getValueEncoded('web_piloto') ?>" style="color: inherit; text-decoration: none;"><?php echo $piloto->getValueEncoded("web_piloto") ?></a></span>
            </div>
        </div>

        <a href="view_pilotos.php" class="btn-back">&larr; Volver al panel de pilotos</a>
    </div>
</div>

<?php displayPageFooter(); ?>




