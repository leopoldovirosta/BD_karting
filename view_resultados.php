<?php

require_once "common.inc.php";
require_once "config.php";
require_once "resultados.class.php";
require_once "carreras.class.php";
require_once "pilotos.class.php";
require_once "categorias.class.php";
require_once "marcas.class.php";
 
// 1. Detectamos el sentido (por defecto ASC)
$type = isset($_GET["type"]) && $_GET["type"] == "DESC" ? "DESC" : "ASC";

// 2. Limpiamos las variables
$start = isset($_GET["start"]) ? (int)$_GET["start"] : 0;
$order = isset($_GET["order"]) ? preg_replace("/[^a-zA-Z_]/", "", $_GET["order"]) : "id_carrera";
if ($order === 'acciones') {
    $order = 'id_carrera';
}
$pageSize = isset($_GET["pageSize"]) ? (int)$_GET["pageSize"] : PAGE_SIZE;

// 3. OBTENER LOS DATOS PARA LOS DESPLEGABLES
list($carreras_opt) = Carrera::getCarreras(0, 9999, "fecha_Carrera DESC");

// Cargar las categorías para el desplegable
$categorias_opt = Categoria::getCategorias();

// Cargar las marcas de chasis y motor para el desplegable
$chasis_opt = Marcas::getMarcaChasis();
$motor_opt = Marcas::getMarcaMotor();

// Codigo para realizar las consultas
// ----------------------------------

// 1. Inicializamos los identificadores
$raw_carrera = $_GET['f_carrera'] ?? '';
$id_carrera = 0;
$id_edicion = 0;

// 2. Si viene el parámetro f_carrera con el formato "id_carrera-id_edicion"
if (!empty($raw_carrera)) {
    if (strpos($raw_carrera, '-') !== false) {
        $partes = explode('-', $raw_carrera);
        $id_carrera = isset($partes[0]) ? (int)$partes[0] : 0;
        $id_edicion = isset($partes[1]) ? (int)$partes[1] : 0;
    } else {
        $id_carrera = (int)$raw_carrera;
    }
}

// 3. Mapeamos las entradas recibidas por GET
$filtros = [
    'id_edicion'  => $id_edicion,
    'id_carrera'  => $id_carrera,
    'piloto'      => trim($_GET['f_piloto'] ?? ''),
    'id_categoria'   => trim($_GET['f_categoria'] ?? ''),
    'posicion'    => trim($_GET['f_posicion'] ?? ''),
    'chasis'      => trim($_GET['f_chasis'] ?? ''),
    'motor'       => trim($_GET['f_motor'] ?? '')
];

// Asignamos las variables f_* para utilizarlas en el formulario HTML
$f_carrera   = $_GET['f_carrera'] ?? '';
$f_piloto    = $filtros['piloto'];
$f_categoria = $filtros['id_categoria'];
$f_posicion  = $filtros['posicion'];
$f_chasis    = $filtros['chasis'];
$f_motor     = $filtros['motor'];

// 4. Consulta limpia a la base de datos
list($lista_resultados, $totalRows) = Resultado::getResultadosFiltrados($start, $pageSize, $order . " " . $type, $filtros);

displayPageHeader("Lista de Resultados");

?>
    <form action="view_resultados.php" method="get" class="search-form">
            <!-- Ocultos para mantener el ordenamiento en cada envio -->
            <input type="hidden" name="order" value="<?php echo htmlspecialchars($order); ?>" />
            <input type="hidden" name="type" value="<?php echo htmlspecialchars($type); ?>" />
<!-- BARRA SUPERIOR DE CONTROLES (Resultados por página y Paginación rápida) -->
    <div class="tabla-controles">
        <div class="resultados-por-pagina">
            <label for="pageSize">Resultados por página:</label>
            <select name="pageSize" id="pageSize" class="form-control" onchange="this.form.submit()">
                <?php foreach (array(5, 10, 20, 50) as $value): ?>
                    <option value="<?php echo $value ?>" <?php if ($pageSize == $value) echo 'selected="selected"' ?>>
                        <?php echo $value ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- TABLA DE RESULTADOS CON FILTROS -->
    <div class="table-responsive">
            
        <table class="tabla-resultados">
            <thead>
            <tr>
            <?php
            $columns = array(
                'id_carrera'        => 'Carrera',
                'apellido_piloto'   => 'Piloto',
                'id_categoria'      => 'Categoria',
                'posicion'          => 'Posicion',
                'id_chasis'         => 'Chasis',
                'id_motor'          => 'Motor',
                'acciones'          => 'Acciones'
            );

            foreach ($columns as $colKey => $colName): 
                $nextType = ($order == $colKey && $type == "ASC") ? "DESC" : "ASC";
                $icon = ($type == "ASC") ? "▲" : "▼";
                
                $sortUrl = Resultado::buildSortUrl($colKey, $order, $type);
            ?>
                    <th>
                        <?php if ($colKey === 'acciones'): ?>
                            <!-- Columna no ordenable: solo texto -->
                            <?php echo htmlspecialchars($colName); ?>
                        <?php else: ?>
                            <a href="<?php echo htmlspecialchars($sortUrl); ?>">
                                <?php echo htmlspecialchars($colName); ?>
                                <?php if ($order == $colKey): ?>
                                    <span class="sort-icon"><?php echo $icon ?></span>
                                <?php endif; ?>
                            </a>
                        <?php endif; ?>
                    </th>
            <?php endforeach; ?>
                </tr>

                <!-- FILA MAESTRA DE FILTROS -->
                <tr class="bg-light">
                    <!-- Filtro por Carrera -->
                    <th>
                        <div class="select-container">
                            <select name="f_carrera" id="f_carrera" class="form-control select-expansible" onchange="this.form.submit()">
                                <option value="">-- Todas --</option>
                                <?php if (!empty($carreras_opt) && (is_array($carreras_opt) || is_object($carreras_opt))): ?>
                                    <?php foreach ($carreras_opt as $c): ?>
                                        <?php 
                                            $idC = is_object($c) ? $c->getValue('id_carrera') : ($c['id_carrera'] ?? 0);
                                            $idE = is_object($c) ? $c->getValue('id_edicion') : ($c['id_edicion'] ?? 0);
                                            $nombre = implode(' - ', array_filter([
                                                is_object($c) ? $c->getValue('nombre_circuito') : ($c['nombre_circuito'] ?? ''),
                                                !empty(is_object($c) ? $c->getValue('fecha_carrera') : ($c['fecha_carrera'] ?? '')) 
                                                    ? date('d/m/Y', strtotime(is_object($c) ? $c->getValue('fecha_carrera') : $c['fecha_carrera'])) 
                                                    : '',is_object($c) ? $c->getValue('nombre_cto') : ($c['nombre_cto'] ?? '')
                                                    ,is_object($c) ? $c->getValue('nombre_carrera_tipo') : ($c['nombre_carrera_tipo'] ?? '')
                                                    ]));
                                            $valCombo = $idC . '-' . $idE;
                                            $isSelected = ($f_carrera === $valCombo) ? 'selected' : '';
                                        ?>
                                        <option value="<?php echo $valCombo; ?>" <?php echo $isSelected; ?>>
                                            <?php echo htmlspecialchars((string)$nombre); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </th>

                    <!-- Filtro por Piloto -->
                    <th>
                        <input type="text" name="f_piloto" class="form-control form-control-sm" placeholder="Buscar piloto..." 
                            value="<?php echo htmlspecialchars($f_piloto); ?>">
                    </th>

                    <!-- Filtro por Categoría -->
                    <th>
                        <select name="f_categoria" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="">-- Todas --</option>
                            <?php if (!empty($categorias_opt) && is_array($categorias_opt)): ?>
                                <?php foreach ($categorias_opt as $cat): ?>
                                    <?php 
                                        $idCat  = is_object($cat) ? $cat->getValue('id_categoria') : ($cat['id_categoria'] ?? '');
                                        $nombre = is_object($cat) ? $cat->getValue('nombre_categoria') : ($cat['nombre_categoria'] ?? '');
                                    ?>
                                    <option value="<?php echo htmlspecialchars((string)$idCat); ?>" <?php echo ((string)$f_categoria === (string)$idCat && $f_categoria !== '') ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars((string)$nombre); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </th>

                    <!-- Filtro por Posición -->
                    <th>
                        <select name="f_posicion" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="">-- Todos --</option>
                            <option value="top3" <?php echo ($f_posicion == 'top3') ? 'selected' : ''; ?>>Podio (Top 3)</option>
                            <option value="1" <?php echo ($f_posicion == '1') ? 'selected' : ''; ?>>Ganadores (1º)</option>
                        </select>
                    </th>

                    <!-- Filtro por Marca Chasis -->
                    <th>
                        <select name="f_chasis" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="">-- Todas --</option>
                            <?php if (!empty($chasis_opt) && is_array($chasis_opt)): ?>
                                <?php foreach ($chasis_opt as $cha): ?>
                                    <?php 
                                        $nombre = is_object($cha) ? $cha->getValue('nombre_marca') : ($cha['nombre_marca'] ?? '');
                                    ?>
                                    <option value="<?php echo htmlspecialchars((string)$nombre); ?>" <?php echo ((string)$f_chasis === (string)$nombre && $f_chasis !== '') ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars((string)$nombre); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </th>

                    <!-- Filtro por Marca Motor -->
                    <th>
                        <select name="f_motor" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="">-- Todas --</option>
                            <?php if (!empty($motor_opt) && is_array($motor_opt)): ?>
                                <?php foreach ($motor_opt as $mot): ?>
                                    <?php 
                                        $nombre = is_object($mot) ? $mot->getValue('nombre_marca') : ($mot['nombre_marca'] ?? '');
                                    ?>
                                    <option value="<?php echo htmlspecialchars((string)$nombre); ?>" <?php echo ((string)$f_motor === (string)$nombre && $f_motor !== '') ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars((string)$nombre); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </th>

                    <!-- Botones de Acción -->
                    <th class="text-center">
                        <button type="submit" class="btn btn-sm btn-primary">Filtrar</button>
                        <a href="view_resultados.php" class="btn btn-sm btn-secundary">Limpiar</a>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $rowCount = 0;
                    foreach ($lista_resultados as $r):
                    $rowCount++;
                ?>
                    <tr <?php if ($rowCount % 2 == 0) echo " class='alt'" ?>>
                        <td><?php echo $r->getValueEncoded('nombre_circuito'); ?></td>
                        <td><?php echo $r->getValueEncoded('nombre_piloto') . ' ' . $r->getValueEncoded('apellido_piloto'); ?></td>
                        <td><?php echo $r->getValueEncoded('nombre_categoria'); ?></td>
                        <td><?php echo $r->getValueEncoded('posicion'); ?></td>
                        <td class="text-center"><?php echo mostrarValor($r->getValueEncoded('marca_chasis')); ?></td>
                        <td class="text-center"><?php echo mostrarValor($r->getValueEncoded('marca_motor')); ?></td>
                        <td>
                            <a href="view_resultado.php?id_resultado=<?php echo $r->getValue('id_resultado'); ?>&id_piloto=<?php echo $r->getValue('id_piloto'); ?>&id_edicion=<?php echo $r->getValue('id_edicion'); ?>" class="btn btn-sm btn-info">Ver</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
      </div>
    </form>
        
    <!-- PAGINACIÓN -->
    <div class="pagination-container">
        <p class="info-text">
            Mostrando <?php echo ($totalRows > 0) ? $start + 1 : 0; ?> - <?php echo min($start + $pageSize, $totalRows); ?> de <?php echo $totalRows; ?>
        </p>
        
        <?php if ($start > 0): ?>
            <a href="<?php echo htmlspecialchars(Resultado::buildUrl(['start' => max($start - $pageSize, 0)])); ?>" class="btn-nav">&laquo; Página anterior</a>
        <?php endif; ?>
        
        &nbsp;
        
        <?php if (($start + $pageSize) < $totalRows): ?>
            <a href="<?php echo htmlspecialchars(Resultado::buildUrl(['start' => ($start + $pageSize)])); ?>" class="btn-nav">Página siguiente &raquo;</a>
        <?php endif; ?>
    </div>
  

<?php
    displayPageFooter();
    ?>
