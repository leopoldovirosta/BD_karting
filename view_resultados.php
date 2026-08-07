<?php

require_once "common.inc.php";
require_once "config.php";
require_once "resultados.class.php";
require_once "pilotos.class.php";
 
// 1. Detectamos el sentido (por defecto ASC)
$type = isset($_GET["type"]) && $_GET["type"] == "DESC" ? "DESC" : "ASC";

// 2. Limpiamos las variables
$start = isset($_GET["start"]) ? (int)$_GET["start"] : 0;
$order = isset($_GET["order"]) ? preg_replace("/[^a-zA-Z_]/", "", $_GET["order"]) : "id_resultado";
$pageSize = isset($_GET["pageSize"]) ? (int)$_GET["pageSize"] : PAGE_SIZE;

// 3. Llamamos al método (asegúrate de que tu SQL en Piloto ahora use $order y $type)
//list($resultados, $totalRows) = Resultado::getResultados($start, $pageSize, $order . " " . $type);

// Codigo para realizar las consultas
// ----------------------------------
// 1. Mapeamos las entradas recibidas por GET
$filtros = [
    'id_edicion'   => $_GET['id_edicion']   ?? 0,
    'id_carrera'   => $_GET['f_carrera']    ?? 0,
    'piloto'       => $_GET['f_piloto']     ?? '',
    'id_categoria' => $_GET['f_categoria']  ?? 0,
    'posicion'     => $_GET['f_posicion']   ?? '',
    'chasis'       => $_GET['f_chasis']     ?? '',
    'motor'        => $_GET['f_motor']      ?? ''
];

// 2. Una sola llamada limpia a la clase
//$lista_resultados = Resultado::getResultadosFiltrados($filtros);
list($lista_resultados, $totalRows) = Resultado::getResultados($start, $pageSize, $order . " " . $type); 

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
        <?php
        $columns = array(
            'id_carrera'    => 'Carrera',
            'piloto'        => 'Piloto',
            'id_categoria'  => 'Categoria',
            'posicion'      => 'Posicion',
            'chasis'        => 'Chasis',
            'motor'         => 'Motor'
        );

        foreach ($columns as $colKey => $colName): 
            // Si la columna es la actual, el siguiente clic debe ser el opuesto
            $nextType = ($order == $colKey && $type == "ASC") ? "DESC" : "ASC";
            $icon = ($type == "ASC") ? "▲" : "▼";
        ?>
            <!-- Cabecera Normal de la Tabla -->
            <tr>
                <th>
                    <a href="view_resultados.php?order=<?php echo $colKey ?>&amp;type=<?php echo $nextType ?>&amp;pageSize=<?php echo $pageSize ?>">
                        <?php echo $colName ?>
                        <?php if ($order == $colKey): ?>
                            <span class="sort-icon"><?php echo $icon ?></span>
                        <?php endif; ?>
                    </a>
                </th>
        <?php endforeach; ?>
            <!--
                <th>Carrera</th>
                <th>Piloto</th>
                <th>Categoría</th>
                <th>Posición</th>
                <th>Chasis</th>
                <th>Motor</th>
                <th>Acciones</th>
            -->
            </tr>

            <!-- 🎯 FILA MAESTRA DE FILTROS -->
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





        <br><br>
    </form>
    <br>
    <table>
    <tr>
        <?php
        // Definimos las columnas que queremos mostrar
    $columns = array(
            "id_resultado" => "ID",
            "fecha_carrera" => "Fecha",
            "anio_edicion" => "Temporada",
            "nombre_cto" => "Campeonato",
            "nombre_circuito" => "Circuito",
            "nombre_carrera_tipao" => "Tipo",
            "nombre_categoria" => "Categoría",
            "nombre_piloto" => "Nombre",
            "apellido_piloto" => "Apellido",
            "foto_piloto" => "Foto",
            "dorsal" => "Dorsal",
            "tiempo_total" => "Tiempo total",
            "mejor_vuelta" => "Mejor vuelta",
            "num_vueltas" => "Vueltas",
            "num_vueltas_completadas" => "Completadas",
            "posicion" => "Posición",
            "comentario_posicion" => "Observaciones",
            "puntos" => "Puntos",
            "marca_chasis" => "Chasis",
            "modelo_chasis" => "Modelo",
            "marca_motor" => "Motor",
            "modelo_motor" => "Modelo",
            "marca_rueda" => "Rueda",
            "modelo_rueda" => "Modelo"
        );

        foreach ($columns as $colKey => $colName): 
                // Si la columna es la actual, el siguiente clic debe ser el opuesto
                $nextType = ($order == $colKey && $type == "ASC") ? "DESC" : "ASC";
                $icon = ($type == "ASC") ? "▲" : "▼";
            ?>
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

<?php
    $rowCount = 0;
    
    foreach($resultados as $resultado):
        $rowCount++;
?>
        <tr<?php if ($rowCount % 2 == 0) echo " class='alt'" ?>>
            <td><a href="view_resultado.php?id_resultado=<?php echo $resultado->getValue('id_resultado') ?>&id_piloto=<?php echo $resultado->getValue("id_piloto")?>&id_edicion=<?php echo $resultado->getValue("id_edicion")?>"><?php echo $resultado->getValue('id_resultado') ?></a></td>
            <td><?php echo $resultado->getValueEncoded("fecha_carrera") ?></td>
            <td class="text-center"><?php echo $resultado->getValueEncoded("anio_edicion") ?></td>
            <td><?php echo $resultado->getValueEncoded("nombre_cto") ?></td>
            <td><?php echo $resultado->getValueEncoded("nombre_circuito") ?></td>
            <td><?php echo $resultado->getValueEncoded("nombre_carrera_tipo") ?></td>
            <td class="text-center"><?php echo $resultado->getValueEncoded("nombre_categoria") ?></td>
            <td><?php echo $resultado->getValueEncoded("nombre_piloto") ?></td>
            <td><?php echo $resultado->getValueEncoded("apellido_piloto") ?></td>
            <td><img src="<?php echo IMAGE_PILOT_DIRECTORY . ($resultado->getValueEncoded('foto_piloto') ?: 'default.jpg') ?>" class="foto foto-click" onclick="openModal(this.src, this.alt)" /></td>
            <td class="text-center"><?php echo mostrarValor($resultado->getValue("dorsal")); ?></td>
            <td class="text-center"><?php echo mostrarValor(Resultado::formatearTiempo($resultado->getValueEncoded("tiempo_total"))); ?></td>
            <td class="text-center"><?php echo mostrarValor(Resultado::formatearTiempo($resultado->getValueEncoded("mejor_vuelta"))); ?></td>
            <td class="text-center"><?php echo mostrarValor($resultado->getValue("num_vueltas")); ?></td>
            <td class="text-center"><?php echo mostrarValor($resultado->getValue("num_vueltas_completadas")); ?></td>
            <td class="text-center"><?php echo mostrarValor($resultado->getValue("posicion")); ?></td>
            <td><?php echo $resultado->getValueEncoded("comentario_posicion") ?></td>
            <td class="text-center"><?php echo mostrarValor($resultado->getValue("puntos")); ?></td>
            <td><?php echo mostrarValor($resultado->getValueEncoded("marca_chasis")); ?></td>
            <td><?php echo mostrarValor($resultado->getValueEncoded("modelo_chasis")); ?></td>
            <td><?php echo mostrarValor($resultado->getValueEncoded("marca_motor")); ?></td>
            <td><?php echo mostrarValor($resultado->getValueEncoded("modelo_motor")); ?></td>
            <td><?php echo mostrarValor($resultado->getValueEncoded("marca_rueda")); ?></td>
            <td><?php echo mostrarValor($resultado->getValueEncoded("modelo_rueda")); ?></td>
        </tr>
<?php
    endforeach;
?>
    </table>
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
