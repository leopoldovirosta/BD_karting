<?php
require_once "common.inc.php";
require_once "config.php";
require_once "pilotos.class.php";

$id = isset($_GET["id_piloto"]) ? (int)$_GET["id_piloto"] : 0;

// Valores para la funcion getNavegacionId
$order_dir = (isset($_GET['dir']) && strtoupper($_GET['dir']) === 'DESC') ? 'DESC' : 'ASC';
$sort_by   = isset($_GET['sort']) ? $_GET['sort'] : 'apellido_piloto';

$piloto = Piloto::getPiloto($id);

// Validar si el usuario viene de la lista de pilotos para guardar su URL exacta
$url_volver = 'view_pilotos.php'; // URL por defecto

if (!empty($_SERVER['HTTP_REFERER'])) {
    $referer = $_SERVER['HTTP_REFERER'];
    
    // Si la URL previa contiene 'view_pilotos.php', la usamos como destino
    if (strpos($referer, 'view_pilotos.php') !== false) {
        $url_volver = $referer;
    }
}

// Cargar las estadísticas del piloto ACTUAL
$stats = Piloto::getEstadisticasPiloto($id);

// Obtener las victorias en carrera
$victoriasDetalle = Piloto::getVictoriasPiloto($id);

// Obtener el historial de poles del piloto
$polesDetalle = Piloto::getPolesPiloto($id);
$podiosDetalle = Piloto::getPodiosPiloto($id);

// Obtener la progresión por año/temporada
$progresionTemporadas = Piloto::getEstadisticasPorTemporada($id);

if (!$piloto) {
    displayPageHeader("Error");
    echo "<div>Piloto no encontrado.</div>";
    displayPageFooter();
    exit;
}

// 5. Ahora que $piloto EXISTE, extraer el apellido para buscar Siguiente y Anterior
$apellidoActual = $piloto->getValue('apellido_piloto');
$idSiguiente = Piloto::getNavegacionId($id, $apellidoActual, $order_dir, 'siguiente');
$idAnterior  = Piloto::getNavegacionId($id, $apellidoActual, $order_dir, 'anterior');

displayPageHeader("Ficha del Piloto: " . $piloto->getValueEncoded("nombre_piloto") . " " . $piloto->getValueEncoded("apellido_piloto"));

?>
<div class="card-piloto">
    <div class="card-header-side">
        <img src="<?php echo IMAGE_PILOT_DIRECTORY . ($piloto->getValueEncoded("foto_piloto") ?: 'default.webp') ?>" 
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
        <h2>Ficha piloto</h2>
        
        <div class="info-grid">
            <div class="info-item">
                <label>Apellido</label>
                <span><?php echo $piloto->getValueEncoded("apellido_piloto") ?></span>
            </div>
            <div class="info-item">
                <label>Fecha de Nacimiento</label>
                <span><?php echo formatearFecha($piloto->getValueEncoded("fecha_nacimiento")); ?></span>
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
            <br>
        </div>
        
        <div class="info-item" style="grid-column: span 2;">
            <!-- Anterior Carrera -->
            <?php if ($idAnterior): ?>
                <a href="view_piloto.php?id_piloto=<?php echo $idAnterior; ?>&sort=<?php echo urlencode($sort_by); ?>&dir=<?php echo urlencode($order_dir); ?>" class="btn btn-nav">
                    ← Anterior
                </a>
            <?php else: ?>
                <span class="btn btn-nav deshabilitado">Primero</span>
            <?php endif; ?>
            <span></span>
            <!-- Siguiente Carrera -->
            <?php if ($idSiguiente): ?>
                <a href="view_piloto.php?id_piloto=<?php echo $idSiguiente; ?>&sort=<?php echo urlencode($sort_by); ?>&dir=<?php echo urlencode($order_dir); ?>" class="btn btn-nav">
                    Siguiente →
                </a>
            <?php else: ?>
                <span class="btn btn-nav deshabilitado">Último</span>
            <?php endif; ?>
        </div>
    </div>
</div>
<div class="dashboard-piloto">
    <!-- COLUMNA IZQUIERDA: Estadísticas -->
    <div class="columna-izquierda">
    <!-- Contenedor de Estadísticas -->
        <div class="stats-container">
            <h3>Estadísticas Acumuladas</h3>
            <div class="stats-grid">
                <div class="stat-card">
                    <span class="stat-value"><?php echo $stats['total_carreras'] ?? 0; ?></span>
                    <span class="stat-label">Carreras</span>
                </div>
                <div class="stat-card victoria">
                    <span class="stat-value"><?php echo $stats['victorias'] ?? 0; ?></span>
                    <span class="stat-label">Victorias</span>
                </div>
                <div class="stat-card podio">
                    <span class="stat-value"><?php echo $stats['podios'] ?? 0; ?></span>
                    <span class="stat-label">Podios</span>
                </div>
                <div class="stat-card pole">
                    <span class="stat-value"><?php echo $stats['poles'] ?? 0; ?></span>
                    <span class="stat-label">Poles</span>
                </div>
                <div class="stat-card mejor">
                    <span class="stat-value"><?php echo $stats['mejor_resultado'] ? 'P' . $stats['mejor_resultado'] : '---'; ?></span>
                    <span class="stat-label">Mejor Resultado</span>
                </div>
            </div>
        </div>
    </div>

    <!-- COLUMNA DERECHA: Victorias arriba y Poles abajo -->
    <div class="columna-derecha">
        <!-- Bloque de Victorias -->
        <?php if (!empty($victoriasDetalle)): ?>
            <div class="stats-container">
                <h3>Historial de Victorias</h3>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Carrera</th>
                                <th>Circuito</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($victoriasDetalle as $vic): ?>
                                <tr>
                                    <td><?php echo formatearFecha($vic['fecha_carrera']); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($vic['nombre_cto']) . " " . htmlspecialchars($vic['nombre_carrera_tipo']); ?>
                                    </td>
                                    <td class="circuito-nombre" title="<?php echo htmlspecialchars($vic['nombre_circuito']); ?>">
                                        <?php echo htmlspecialchars($vic['nombre_circuito']); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- Historial de Pole Positions -->
        <?php if (!empty($polesDetalle)): ?>
            <div class="stats-container margin-top">
                <h3>Historial de Pole Positions</h3>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Carrera</th>
                                <th>Circuito</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($polesDetalle as $pole): ?>
                                <tr>
                                    <td><?php echo formatearFecha($pole['fecha_carrera']); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($pole['nombre_cto']); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($pole['nombre_circuito']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    <!-- Bloque: 2º y 3º Puestos -->
    <?php if (!empty($podiosDetalle)): ?>
        <div class="stats-container">
            <h3>Historial 2º y 3º Puestos</h3>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th class="text-center">Posición</th>
                            <th>Fecha</th>
                            <th>Carrera</th>
                            <th>Circuito</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($podiosDetalle as $podio): ?>
                            <tr>
                                <td class="text-center">
                                    <span class="badge-posicion pos-<?php echo $podio['posicion']; ?>">
                                        P<?php echo $podio['posicion']; ?>
                                    </span>
                                </td>
                                <td><?php echo formatearFecha($podio['fecha_carrera']); ?></td>
                                <td><?php echo htmlspecialchars($podio['nombre_cto']) . " " . htmlspecialchars($podio['nombre_carrera_tipo']) ; ?></td>
                                <td class="circuito-nombre" title="<?php echo htmlspecialchars($podio['nombre_circuito']); ?>">
                                    <?php echo htmlspecialchars($podio['nombre_circuito']); ?>
                                </td>    
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($progresionTemporadas)): 
        // Revertir para mostrar cronológicamente de izquierda a derecha (año más antiguo al más reciente)
        $tempCronologicas = array_reverse($progresionTemporadas);
        
        $anios = array_column($tempCronologicas, 'temporada');
        $victoriasArr = array_column($tempCronologicas, 'victorias');
        $podiosArr = array_column($tempCronologicas, 'podios');
        $polesArr = array_column($tempCronologicas, 'poles');
    ?>
        <div class="stats-container">
            <h3>Evolución por Temporada</h3>
            <div style="position: relative; height:260px; width:100%">
                <canvas id="chartTemporadas"></canvas>
            </div>
        </div>

        <script>
        document.addEventListener("DOMContentLoaded", function() {
            const ctx = document.getElementById('chartTemporadas').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($anios); ?>,
                    datasets: [
                        {
                            label: 'Victorias',
                            data: <?php echo json_encode($victoriasArr); ?>,
                            backgroundColor: '#d97706'
                        },
                        {
                            label: 'Podios',
                            data: <?php echo json_encode($podiosArr); ?>,
                            backgroundColor: '#3b82f6'
                        },
                        {
                            label: 'Poles',
                            data: <?php echo json_encode($polesArr); ?>,
                            backgroundColor: '#10b981'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1 }
                        }
                    }
                }
            });
        });
        </script>
    <?php endif; ?>



    </div>
</div>







<div class="contenedor-seccion-podios">
    <div class="info-item" style="grid-column: span 2;">
        <a href="<?php echo htmlspecialchars($url_volver); ?>" class="btn btn-nav">Volver al listado</a>
    </div>
</div>

<?php displayPageFooter(); ?>




