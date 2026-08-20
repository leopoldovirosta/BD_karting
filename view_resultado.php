<?php
require_once "common.inc.php";
require_once "config.php";
require_once "resultados.class.php";

$id_resultado = isset($_GET["id_resultado"]) ? (int)$_GET["id_resultado"] : 0;
$id_piloto = isset($_GET["id_piloto"]) ? (int)$_GET["id_piloto"] : 0;
$id_edicion = isset($_GET["id_edicion"]) ? (int)$_GET["id_edicion"] : 0;
$resultado = Resultado::getResultado($id_resultado,$id_piloto,$id_edicion);

if (!$resultado) {
    displayPageHeader("Error");
    echo "<div>Resultado no encontrado.</div>";
    displayPageFooter();
    exit;
}

$tiempo_total_formateado = Resultado::formatearTiempo($resultado->getValue("tiempo_total"));
$mejor_vuelta_formateada = Resultado::formatearTiempo($resultado->getValue("mejor_vuelta"));

$v_media_carrera = Resultado::calcularVelocidadMediaCarrera(
    $resultado->getValue("longitud"),
    $resultado->getValue("num_vueltas_completadas"),
    $resultado->getValue("tiempo_total")
);

$v_media_vuelta = Resultado::calcularVelocidadMedia(
    $resultado->getValue("longitud"),
    $resultado->getValue("mejor_vuelta")
);

displayPageHeader("Detalles de la carrera");

?>
    <div class="circuito-card">
    <div class="circuito-header">
        <div class="header-info">
            <span class="pais-badge"><?php echo $resultado->getValueEncoded("nombre_cto") ?></span>
            <h2><?php echo $resultado->getValueEncoded("nombre_circuito") ?></h2>
        </div>
        <div class="circuito-mapa">
            <img src="<?php echo IMAGE_PILOT_DIRECTORY . ($resultado->getValueEncoded('foto_piloto') ?: 'default.jpg') ?>" class="foto-perfil" alt="Foto del piloto">
        </div>
    </div>
    
    <div class="circuito-grid">
        <div class="data-item">
            <span class="material-symbols-outlined">calendar_month</span>
            <label>Fecha</label>
            <div class="value"><?php echo $resultado->getValueEncoded("fecha_carrera") ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">calendar_month</span>
            <label>Temporada</label>
            <div class="value"><?php echo $resultado->getValueEncoded("anio_edicion") ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">flag</span>
            <label>Carrera</label>
            <div class="value"><?php echo $resultado->getValueEncoded("nombre_carrera_tipo") ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">sports_motorsports</span>
            <label>Categoria</label>
            <div class="value"><?php echo $resultado->getValueEncoded("nombre_categoria") ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">badge</span>
            <label>Nombre</label>
            <div class="value"><?php echo $resultado->getValueEncoded("nombre_piloto") ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">badge</span>
            <label>Apellidos</label>
            <div class="value"><?php echo mostrarValor($resultado->getValueEncoded("apellido_piloto")); ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">pin</span>
            <label>Dorsal</label>
            <div class="value"><?php echo mostrarValor($resultado->getValueEncoded("dorsal")); ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">timer</span>
            <label>Tiempo total</label>
            <div class="value"><?php echo $tiempo_total_formateado ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">speed</span>
            <label>Velocidad media</label>
            <div class="value"><?php echo ($v_media_carrera > 0) ? $v_media_carrera . ' <small>km/h</small>' : '---'; ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">timer</span>
            <label>Vuelta rápida</label>
            <div class="value"><?php echo $mejor_vuelta_formateada ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">speed</span>
            <label>Velocidad media</label>
            <div class="value"><?php echo ($v_media_vuelta > 0) ? $v_media_vuelta . ' <small>km/h</small>' : '---'; ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">conversion_path</span> 
            <label>Vueltas totales</label>
            <div class="value"><?php echo mostrarValor($resultado->getValue("num_vueltas")); ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">conversion_path</span>
            <label>Vueltas completadas</label>
            <div class="value"><?php echo mostrarValor($resultado->getValue("num_vueltas_completadas")); ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">leaderboard</span>
            <label>Posicion</label>
            <div class="value"><?php echo mostrarValor($resultado->getValue("posicion")); ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">comment</span>
            <label>Observaciones</label>
            <div class="value"><?php echo mostrarValor($resultado->getValue("comentario_posicion")); ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">format_list_numbered</span>
            <label>Puntos</label>
            <div class="value"><?php echo mostrarValor($resultado->getValue("puntos")); ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">directions_car</span>
            <label>Marca chasis</label>
            <div class="value"><?php echo mostrarValor($resultado->getValue("marca_chasis")); ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">directions_car</span>
            <label>Modelo chasis</label>
            <div class="value"><?php echo mostrarValor($resultado->getValue("modelo_chasis")); ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">settings</span>
            <label>Marca motor</label>
            <div class="value"><?php echo mostrarValor($resultado->getValue("marca_motor")); ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">settings</span>
            <label>Modelo motor</label>
            <div class="value"><?php echo mostrarValor($resultado->getValue("modelo_motor")); ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">tire_repair</span>
            <label>Marca ruedas</label>
            <div class="value"><?php echo mostrarValor($resultado->getValue("marca_ruedas")); ?></div>
        </div>
        <div class="data-item">
            <span class="material-symbols-outlined">tire_repair</span>
            <label>Modelo ruedas</label>
            <div class="value"><?php echo mostrarValor($resultado->getValue("modelo_ruedas")); ?></div>
        </div>
        
    </div>
    
    <div class="circuito-header">
        <?php 
            // Definimos la URL de retorno (p. ej., usando HTTP_REFERER o una URL por defecto)
            $url_retorno = $_SERVER['HTTP_REFERER'] ?? 'view_resultados.php';
            ?>

            <a href="<?php echo htmlspecialchars($url_retorno); ?>" class="btn btn-nav">
                &laquo; Volver al listado
            </a>
    </div>

</div>

<?php displayPageFooter(); ?>
