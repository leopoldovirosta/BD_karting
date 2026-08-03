<?php
require_once "common.inc.php";
require_once "config.php";
require_once "carreras.class.php";
require_once "resultados.class.php";
require_once "circuitos.class.php";

$id_carrera = isset($_GET["id_carrera"]) ? (int)$_GET["id_carrera"] : 0;
$id_cto = isset($_GET["id_cto"]) ? (int)$_GET["id_cto"] : 0;
$carrera = Carrera::getCarrera($id_carrera, $id_cto);

if (!$carrera) {
    displayPageHeader("Error");
    echo "<div>Carrera no encontrada.</div>";
    displayPageFooter();
    exit;
}

// --------------------------------------------------------------------------
// CARGAR EL CIRCUITO: Obtenemos el circuito asociado a esta carrera para
// obtener la longitud y calcula la velocidad media de la vuelta rapida
// --------------------------------------------------------------------------
$id_circuito = $carrera->getValue("id_circuito");
$circuito = $id_circuito ? Circuito::getCircuito($id_circuito) : null;

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
                <?php
                    $tasfalto = $carrera->getValue("tasfalto");
                    if (!empty($tasfalto)): // Si la URL no está vacía...
                ?>
                <td class="text-center"><?php echo $tasfalto ?> ºC</td>
                <?php else: ?><td class="text-center">---</td>
                <?php endif; ?>
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

            <?php
            $vr = Carrera::getVueltaRapida($id_carrera);
            
            if ($vr):
                $nombre   = is_object($vr) ? $vr->getValue('nombre_piloto') : $vr['nombre_piloto'];
                $apellido = is_object($vr) ? $vr->getValue('apellido_piloto') : $vr['apellido_piloto'];
                $tiempo   = is_object($vr) ? $vr->getValue('mejor_vuelta') : $vr['mejor_vuelta'];

                // Formato mm:ss.mmm (quitando los 3 primeros caracteres "00:")
                $tiempoFormateado = Resultado::formatearTiempo($tiempo);

                // 2. Cálculo de velocidad media DENTRO del IF (solo si existe $vr)
                $longitud = is_object($circuito) ? $circuito->getValue("longitud") : 0;
                $v_media  = Resultado::calcularVelocidadMedia($longitud, $tiempo);
            ?>
                <div class="info-item">
                    <label>⚡ Vuelta Rápida:</label>
                    <strong><?php echo htmlspecialchars($nombre . ' ' . $apellido); ?></strong> 
                    <?php echo $tiempoFormateado; ?>
                    <p class="avg-speed-tag">
                    Velocidad media: <strong><?php echo $v_media; ?> <small>km/h</small></strong></p>
                </div>
            <?php endif; ?>

            <div class="info-item" style="grid-column: span 2;">
                <a href="view_carreras.php" class="btn-back">&larr; Volver al panel de carrera</a>
            </div>
        </div>
    </div>
</div>


<?php
// Obtener el podio de la carrera 
$podiosPorCategoria = Carrera::getPodiosPorCategoria($id_carrera); 
?>

<div class="contenedor-seccion-podios">
    <?php if (empty($podiosPorCategoria)): ?>
        <p class="alerta-vacio">No hay resultados de podio registrados para esta carrera.</p>
    <?php else: ?>
        
        <!-- BUCLE 1: Recorremos cada Categoría -->
        <?php foreach ($podiosPorCategoria as $nombreCategoria => $pilotosCat): ?>
            
            <div class="bloque-categoria-podio">
                <h2 class="titulo-categoria">Categoría: <?php echo htmlspecialchars($nombreCategoria); ?></h2>
                
                <?php
                // --- REORDENAMIENTO PARA PODIO ESCALONADO ---
                // Mapeamos los índices de la base de datos (0 = 1º, 1 = 2º, 2 = 3º)
                // al orden visual deseado: [2º puesto, 1º puesto, 3º puesto]
                $mapaPodio = [
                    'step-2' => isset($pilotosCat[1]) ? $pilotosCat[1] : null, // 2º Puesto
                    'step-1' => isset($pilotosCat[0]) ? $pilotosCat[0] : null, // 1º Puesto
                    'step-3' => isset($pilotosCat[2]) ? $pilotosCat[2] : null, // 3º Puesto
                ];
                ?>

                <div class="podium-container">
                    
                    <!-- BUCLE 2: Pintamos las 3 posiciones del podio -->
                    <?php foreach ($mapaPodio as $claseEscalon => $piloto): ?>
                        <?php if ($piloto): ?>
                            <?php 
                                // Extracción de datos (compatible con Clase Resultado u Objeto/Array)
                                $posicion = is_object($piloto) ? $piloto->getValue('posicion') : $piloto['posicion'];
                                $nombre   = is_object($piloto) ? $piloto->getValue('nombre_piloto') : $piloto['nombre_piloto'];
                                $apellido = is_object($piloto) ? $piloto->getValue('apellido_piloto') : $piloto['apellido_piloto'];
                                $foto     = is_object($piloto) ? $piloto->getValue('foto_piloto') : $piloto['foto_piloto'];
                                $tiempo   = is_object($piloto) ? $piloto->getValue('mejor_vuelta') : $piloto['mejor_vuelta'];
                                
                                // Si tienes el equipo/chasis en la vista:
                                $marca_chasis    = is_object($piloto) ? $piloto->getValue('marca_chasis') : ($piloto['marca_chasis'] ?? '');
                                $marca_motor    = is_object($piloto) ? $piloto->getValue('marca_motor') : ($piloto['marca_motor'] ?? '');
                                $marca_rueda    = is_object($piloto) ? $piloto->getValue('marca_rueda') : ($piloto['marca_rueda'] ?? '');
                                
                                // Limpieza de tiempo (usando el substr que vimos antes)
                                $tiempoLimpio = !empty($tiempo) ? substr($tiempo, 3) : '---';
                            ?>

                            <div class="podium-step <?php echo $claseEscalon; ?>">
                                <div class="pilot-card">
                                    <div class="avatar-wrapper">
                                        <img src="images/pilotos/<?php echo $foto ?: 'default_user.png'; ?>" 
                                             alt="<?php echo htmlspecialchars($nombre . ' ' . $apellido); ?>" 
                                             class="pilot-avatar">
                                        <span class="badge badge-<?php echo $posicion; ?>">
                                            <?php echo $posicion; ?>
                                        </span>
                                    </div>
                                    
                                    <span class="pilot-name">
                                        <?php echo htmlspecialchars($nombre . ' ' . $apellido); ?>
                                    </span>
                                    
                                    <?php if (!empty($marca_chasis)): ?>
                                        <span class="pilot-team"><?php echo htmlspecialchars($marca_chasis); ?></span>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($marca_motor)): ?>
                                        <span class="pilot-team"><?php echo htmlspecialchars($marca_motor); ?></span>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($marca_rueda)): ?>
                                        <span class="pilot-team"><?php echo htmlspecialchars($marca_rueda); ?></span>
                                    <?php endif; ?>
                                    
                                    <span class="pilot-time"><?php echo $tiempoLimpio; ?></span>
                                </div>
                                
                                <div class="pedestal">
                                    <span class="step-number"><?php echo $posicion; ?></span>
                                </div>
                            </div>

                        <?php endif; ?>
                    <?php endforeach; ?>

                </div> <!-- .podium-container -->
            </div> <!-- .bloque-categoria-podio -->

        <?php endforeach; ?>
    <?php endif; ?>

        <a href="view_carreras.php" class="btn-back">&larr; Volver al panel de carrera</a>
</div>

<?php displayPageFooter(); ?>
