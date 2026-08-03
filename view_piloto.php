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
        <img src="<?php echo IMAGE_PILOT_DIRECTORY . ($piloto->getValueEncoded("foto_piloto") ?: 'default.jpg') ?>" 
             alt="Foto de <?php echo $piloto->getValueEncoded('nombre_piloto') ?>" 
             class="foto-perfil" />
        <h3><?php echo $piloto->getValueEncoded("nombre_piloto") ?></h3>
        <?php
            $bandera = $piloto->getValue("codigo_iso");
            if ($bandera != 'xx'):
        ?>
                <span class="fi fi-<?php echo $piloto->getValue("codigo_iso"); ?>"></span>
        <?php endif; ?>
        <p style="color: #3498db;"><?php echo $piloto->getValueEncoded("nombre_pais") ?></p>
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
                <span><?php echo mostrarValor($piloto->getValueEncoded("fecha_nacimiento")); ?></span>
            </div>
            <div class="info-item">
                <label>Escudería</label>
                <span><?php echo mostrarValor($piloto->getValueEncoded("nombre_escuderia")); ?></span>
            </div>
            <div class="info-item">
                <label>Patrocinador</label>
                <span><?php echo mostrarValor($piloto->getValueEncoded("nombre_patrocinador")); ?></span>
            </div>
            <div class="info-item" style="grid-column: span 2;">
                <label>Email de contacto</label>
                <?php
                    $mail = $piloto->getValueEncoded("email_piloto");
                    if (!empty($mail)): // Si la URL no está vacía...
                ?>
                <span><a href="mailto:<?php echo $mail ?>" style="color: inherit; text-decoration: none;"><?php echo $mail ?></a></span>
                <?php else: ?><td class="text-center">---</td>
                <?php endif; ?>
            </div>
            <div class="info-item" style="grid-column: span 2;">
                <label>Página web</label>
                <?php
                    $url = $piloto->getValueEncoded("web_piloto");
                    if (!empty($url)): // Si la URL no está vacía...
                ?>
                <span><a href="<?php echo $url ?>" style="color: inherit; text-decoration: none;" target="_blank">Visitar<span class="material-symbols-outlined" style="font-size: 14px;">open_in_new</span></a></span>
                <?php else: ?><td class="text-center">---</td>
                <?php endif; ?>
            </div>
        </div>

        <a href="view_pilotos.php" class="btn-back">&larr; Volver al panel de pilotos</a>
    </div>
</div>

<?php displayPageFooter(); ?>




