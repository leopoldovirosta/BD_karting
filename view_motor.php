<?php
require_once "common.inc.php";
require_once "config.php";
require_once "motores.class.php";

$id = isset($_GET["id_motor"]) ? (int)$_GET["id_motor"] : 0;

$motor = Motor::getMotorById($id);

if (!$motor) {
    displayPageHeader("Error");
    echo "<div>Motor no encontrado.</div>";
    displayPageFooter();
    exit;
}

displayPageHeader("Ficha de Motor");

?>
<div class="circuito-card">
    <div class="circuito-header">
        <div class="header-info">
            <span class="pais-badge"><?php echo $motor->getValueEncoded("nombre_pais"); ?></span>
            <h1><?php echo $motor->getValueEncoded("modelo"); ?></h1>
            <p>
                <?php 
                    $logoMarca = trim((string)$motor->getValue('logo_marca'));
                    $tieneLogoReal = !empty($logoMarca) && strtolower($logoMarca) !== 'default.webp';
                    $srcLogo = IMAGE_LOGOS_MARCAS_DIRECTORY . ($tieneLogoReal ? $motor->getValueEncoded('logo_marca') : 'default.webp');
                ?>

                <?php if ($tieneLogoReal): ?>
                    <img src="<?php echo $srcLogo; ?>" alt="Logo <?php echo $motor->getValueEncoded('nombre_marca'); ?>" />
                <?php else: ?>
                    <img src="<?php echo $srcLogo; ?>" style="cursor: default;" />
                <?php endif; ?>
            </p>
            <p class="nombre-marca"><?php echo $motor->getValueEncoded("nombre_marca"); ?></p>
            <?php if ($motor->getValueEncoded("pagina_web")): ?>
                <p>
                    <a href="<?php echo htmlspecialchars($motor->getValueEncoded("pagina_web")); ?>" target="_blank"><span class="material-symbols-outlined">web</span></a>
                </p>
            <?php endif; ?>
        </div>
            <?php
                $fotoMotor = trim((string)$motor->getValue('foto_motor'));
                $tieneFotoReal = !empty($fotoMotor) && strtolower($fotoMotor) !== 'default.jpg';
            ?>

            <?php if ($tieneFotoReal): ?>
                <div class="foto-ficha-container">
                    <img src="<?php echo IMAGE_MOTORES_DIRECTORY . $motor->getValueEncoded('foto_motor'); ?>" 
                        onclick="openModal(this.src, this.alt)" 
                        alt="Foto <?php echo $motor->getValueEncoded('modelo'); ?>">
                </div>
            <?php endif; ?>
    </div>

    <div class="circuito-grid">
        <div class="data-item">
            <span class="material-symbols-outlined">Description</span>
            <label>Descripcion</label>
            <div class="value"><?php echo mostrarValor($motor->getValueEncoded("descripcion")); ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">Label</span>
            <label>Clase</label>
            <div class="value"><?php echo mostrarValor($motor->getValueEncoded("clase")); ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">Block</span>
            <label>Diámetro</label>
            <div class="value"><?php echo formatearMedida($motor->getValueEncoded("diametro"), "mm"); ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">Block</span>
            <label>Máximo Diámetro</label>
            <div class="value"><?php echo formatearMedida($motor->getValueEncoded("max_diametro"), "mm"); ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">Block</span> 
            <label>Carrera</label>
            <div class="value"><?php echo formatearMedida($motor->getValueEncoded("carrera"), "mm"); ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">Circle</span>
            <label>Cilindrada</label>
            <div class="value"><?php echo formatearMedida($motor->getValueEncoded("cilindrada"), "cm³"); ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">Water_pump</span>
            <label>Admisión</label>
            <div class="value"><?php echo mostrarValor($motor->getValueEncoded("admision")); ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">Oil_barrel</span>
            <label>Lubricación</label>
            <div class="value"><?php echo mostrarValor($motor->getValueEncoded("lubricacion")); ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">Water_pump</span>
            <label>Lumbreras</label>
            <div class="value"><?php echo mostrarValor($motor->getValueEncoded("lumbreras")); ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">Water_pump</span>
            <label>Encendido</label>
            <div class="value"><?php echo mostrarValor($motor->getValueEncoded("encendido")); ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">Speed</span>
            <label>Régimen máximo</label>
            <div class="value"><?php echo formatearMedida($motor->getValueEncoded("regimen_max_rpm"), "rpm"); ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">Water_pump</span>
            <label>Transmisión</label>
            <div class="value"><?php echo mostrarValor($motor->getValueEncoded("transmision")); ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">Snowflake</span>
            <label>Refrigeración</label>
            <div class="value"><?php echo mostrarValor($motor->getValueEncoded("refrigeracion")); ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">Water_pump</span>
            <label>Starter</label>
            <div class="value"><?php echo mostrarValor($motor->getValueEncoded("starter")); ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">Weight</span>
            <label>Peso</label>
            <div class="value"><?php echo formatearMedida($motor->getValueEncoded("peso"), "kg"); ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">Valve</span>
            <label>Carburador</label>
            <div class="value"><?php echo mostrarValor($motor->getValueEncoded("carburador")); ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">Speed</span>
            <label>Potencia máxima</label>
            <div class="value"><?php echo formatearMedida($motor->getValueEncoded("potencia_max"), "CV"); ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">Cyclone</span>
            <label>Par motor máximo</label>
            <div class="value"><?php echo formatearMedida($motor->getValueEncoded("par_motor_max"), "Nm"); ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">verified</span>
            <label>Homologación</label>
            <div class="value"><?php echo mostrarValor($motor->getValueEncoded("homologacion")); ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">description</span>
            <label>Ficha Técnica</label>
            <div class="value">
                <?php if ($motor->getValue("url_homologacion")): ?>
                    <a href="<?php echo htmlspecialchars($motor->getValue("url_homologacion")); ?>" target="_blank"><span class="material-symbols-outlined">file_open</span></a>
                <?php else: ?>
                    <div class="value">---</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div style="margin-top: 20px;">
    <?php 
        // Definimos la URL de retorno (p. ej., usando HTTP_REFERER o una URL por defecto)
        $url_retorno = $_SERVER['HTTP_REFERER'] ?? 'view_motores.php';
        ?>

        <a href="<?php echo htmlspecialchars($url_retorno); ?>" class="btn btn-nav">
            &laquo; Volver al listado
        </a>
    </div>
</div>
<?php displayPageFooter(); ?>
