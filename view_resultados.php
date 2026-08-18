<?php

require_once "common.inc.php";
require_once "config.php";
require_once "resultados.class.php";
require_once "carreras.class.php";
require_once "pilotos.class.php";
 
// 1. Detectamos el sentido (por defecto ASC)
$type = isset($_GET["type"]) && $_GET["type"] == "DESC" ? "DESC" : "ASC";

// 2. Limpiamos las variables
$start = isset($_GET["start"]) ? (int)$_GET["start"] : 0;
$order = isset($_GET["order"]) ? preg_replace("/[^a-zA-Z_]/", "", $_GET["order"]) : "id_carrera";
$pageSize = isset($_GET["pageSize"]) ? (int)$_GET["pageSize"] : PAGE_SIZE;

// 3. OBTENER LOS DATOS PARA LOS DESPLEGABLES (AQUÍ DEFINES $carreras_opt)
$carreras_opt = Carrera::getCarreras($start, $pageSize, $order . " " . $type);     // 👈 Llamada a la DB para traer carreras
//$categorias_opt = Categoria::getCategorias(); // 👈 Llamada a la DB para traer categorías

// Codigo para realizar las consultas
// ----------------------------------
// 1. Mapeamos las entradas recibidas por GET
$filtros = [
    'id_edicion'   => $_GET['id_edicion']   ?? 0,
    'id_carrera'   => $_GET['id_carrera']    ?? 0,
    'id_piloto'    => $_GET['f_piloto']     ?? '',
    'id_categoria' => $_GET['f_categoria']  ?? 0,
    'posicion'     => $_GET['f_posicion']   ?? '',
    'id_chasis'    => $_GET['f_chasis']     ?? '',
    'id_motor'     => $_GET['f_motor']      ?? ''
];

// 2. Una sola llamada limpia a la clase
list($lista_resultados, $totalRows) = Resultado::getResultadosFiltrados($start, $pageSize, $order . " " . $type, $filtros);

displayPageHeader("Lista de Resultados");

?>
    <form action="view_resultados.php" method="get" class="search-form">
        <input type="hidden" name="order" value="<?php echo $order ?>" />
        <label for="pageSize">Resultados por página:</label>
        <select name="pageSize" id="pageSize" onchange="this.form.submit()">
            <?php foreach (array(5, 10, 20, 50) as $value): ?>
                <option value="<?php echo $value ?>" <?php if ($pageSize == $value) echo 'selected="selected"' ?>>
                    <?php echo $value ?>
                </option>
            <?php endforeach; ?>
        </select>


        <!-- Formulario de seleccion de campos para consulta -->
        <table>
        <thead>
        <tr>
        <?php
        $columns = array(
            'id_carrera'    => 'Carrera',
            'id_piloto'     => 'Piloto',
            'id_categoria'  => 'Categoria',
            'posicion'      => 'Posicion',
            'id_chasis'     => 'Chasis',
            'id_motor'      => 'Motor'
        );

        foreach ($columns as $colKey => $colName): 
            // Si la columna es la actual, el siguiente clic debe ser el opuesto
            $nextType = ($order == $colKey && $type == "ASC") ? "DESC" : "ASC";
            $icon = ($type == "ASC") ? "▲" : "▼";
        ?>
            <!-- Cabecera Normal de la Tabla -->
                <th>
                    <a href="view_resultados.php?order=<?php echo $colKey ?>&amp;type=<?php echo $nextType ?>&amp;pageSize=<?php echo $pageSize ?>">
                        <?php echo $colName ?>
                        <?php if ($order == $colKey): ?>
                            <span class="sort-icon"><?php echo $icon ?></span>
                        <?php endif; ?>
                    </a>
                </th>
        <?php endforeach; ?>
            </tr>

            <!-- FILA MAESTRA DE FILTROS -->
            <tr class="bg-light">
                <!-- Filtro por Carrera -->
                <th>
                    <select name="f_carrera" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">-- Todas --</option>
                        <?php foreach ($carreras_opt as $c): ?>
                            <option value="<?php echo $c['id_carrera']; ?>" <?php echo ($f_carrera == $c['id_carrera']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($c['nombre_circuito'] . " (" . $c['fecha_carrera'] . ")"); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </th>

                <!-- Filtro por Piloto (Texto o Select) -->
                <th>
                    <input type="text" name="f_piloto" class="form-control form-control-sm" placeholder="Buscar piloto..." 
                        value="<?php echo htmlspecialchars($f_piloto); ?>">
                </th>

                <!-- Filtro por Categoría -->
                <th>
                    <select name="f_categoria" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">-- Todas --</option>
                    <?php foreach ($categorias_opt as $cat): ?>
                            <option value="<?php echo $cat['id_categoria']; ?>" <?php echo ($f_categoria == $cat['id_categoria']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['nombre_categoria']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </th>

                <!-- Filtro por Posición (ej. Podios) -->
                <th>
                    <select name="f_posicion" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">-- Todos --</option>
                        <option value="top3" <?php echo ($f_posicion == 'top3') ? 'selected' : ''; ?>>Podio (Top 3)</option>
                        <option value="1" <?php echo ($f_posicion == '1') ? 'selected' : ''; ?>>Ganadores (1º)</option>
                    </select>
                </th>

                <!-- Filtro por Marca Chasis -->
                <th>
                    <input type="text" name="f_chasis" class="form-control form-control-sm" placeholder="Marca chasis..." 
                           value="<?php echo htmlspecialchars($f_chasis); ?>">
                </th>

                <!-- Filtro por Marca Motor -->
                <th>
                    <input type="text" name="f_motor" class="form-control form-control-sm" placeholder="Marca motor..." 
                           value="<?php echo htmlspecialchars($f_motor); ?>">
                </th>

                <!-- Botones de Acción -->
                <th class="text-center">
                    <button type="submit" class="btn btn-sm btn-primary">filtrar</button>
                    <a href="view_resultados.php<?php echo !empty($id_edicion) ? '?id_edicion='.$id_edicion : ''; ?>" class="btn btn-sm btn-outline-secondary">Limpiar</a>
                </th>
            </tr>
        </thead>
        <tbody>
            <!-- Aquí iteras los registros de $resultados -->
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
                    <td><?php echo $r->getValueEncoded('marca_chasis'); ?></td>
                    <td><?php echo $r->getValueEncoded('marca_motor'); ?></td>
                    <td>
                        <a href="view_resultado.php?id_resultado=<?php echo $r->getValue('id_resultado'); ?>&id_piloto=<?php echo $r->getValue('id_piloto'); ?>&id_edicion=<?php echo $r->getValue('id_edicion'); ?>" class="btn btn-sm btn-info">Ver</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </form>
    <div class="pagination-container">
    <p class="info-text">
        Mostrando <?php echo $start + 1 ?>-<?php echo min($start + $pageSize, $totalRows) ?> de <?php echo $totalRows ?>
    </p>
        <?php if ($start > 0 ): ?>
            <a href="view_resultados.php?start=<?php echo max($start - $pageSize, 0) ?>&amp;order=<?php echo $order ?>&amp;type=<?php echo $type ?>&amp;pageSize=<?php echo $pageSize ?>" class="btn-nav">&laquo; Página anterior</a>
        <?php endif; ?>
        
        &nbsp;
        
        <?php if ($start + $pageSize < $totalRows): ?>
            <a href="view_resultados.php?start=<?php echo ($start + $pageSize) ?>&amp;order=<?php echo $order ?>&amp;type=<?php echo $type ?>&amp;pageSize=<?php echo $pageSize ?>" class="btn-nav">Página siguiente &raquo;</a>
        <?php endif; ?>
    </div> 
<?php

    displayPageFooter();
?>
