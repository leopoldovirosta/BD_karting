<?php

/**
 * Devuelve el valor escapado para HTML o '---' si está vacío/null.
 * Respetando el 0 numérico o en texto.
 * 
 * @param mixed $valor El valor a comprobar e imprimir.
 * @return string Texto listo para imprimir de forma segura.
 */
function mostrarValor($valor) {
    // Comprobamos que no sea null ni cadena vacía, pero permitiendo el número 0 o "0"
    if ($valor === null || trim((string)$valor) === '') {
        return '---';
    }
    
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

/**
 * Formatea un valor numérico añadiendo su unidad de medida.
 */
function formatearMedida($valor, $unidad = 'mm') {
    if ($valor === null || trim((string)$valor) === '') {
        return '---';
    }
    $valorLimpio = htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
    return !empty($unidad) ? $valorLimpio . ' ' . $unidad : $valorLimpio;
}


function formatearFecha($fecha) {
    if (empty($fecha) || $fecha === '0000-00-00') {
        return '---';
    }
    
    $date = new DateTime($fecha);
    return $date->format('d-m-Y');
}




function displayPageHeader($pageTitle) {
?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
        <link rel="stylesheet" type="text/css" href="common.css" />
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.2.3/css/flag-icons.min.css"/>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    </head>
    <body>
    <nav>
        <ul class="nav-links">
            <li><a href="view_pilotos.php">Pilotos</a></li>
            <li><a href="view_carreras.php">Carreras</a></li>
            <li><a href="view_resultados.php">Resultados</a></li>
            <li class="dropdown">
                <span class="dropbtn">Anexos ▾</span>
                <div class="dropdown-content">
                    <a href="view_circuitos.php">Circuitos</a>
                    <a href="view_chasis.php">Chasis</a>
                    <a href="view_motores.php">Motores</a>
                    <a href="view_ruedas.php">Ruedas</a>
                    <a href="view_escuderias.php">Escuderías</a>
                    <a href="view_patrocinadores.php">Patrocinadores</a>
                </div>
            </li>
            <li><a href="https://karting.blog/events/" target="_blank">Eventos</a></li>
            <li><a href="https://karting.blog/" target="_blank">Blog</a></li>
        </ul>
    </nav>
    <!-- Abrimos la etiqueta main para envolver todo el contenido dinámico -->
        <main class="site-content">
    <?php
    }

    function displayPageFooter() {
    ?>
        </main> <!-- Cierre de .site-content -->
        <div id="myModal" class="modal">
            <span class="close">&times;</span>
            <img class="modal-content" id="img01">
            <div id="caption"></div>
        </div>
    <footer>
        <p>&copy; 2026 Leo Virosta</p>
    </footer>
    

    <script>
        function openModal(src, alt) {
            var modal = document.getElementById("myModal");
            var modalImg = document.getElementById("img01");
            var captionText = document.getElementById("caption");

            modal.style.display = "block";
        modalImg.src = src;
            captionText.innerHTML = alt;
        }
        // Cerrar el modal al hacer clic en la X
        var closeBtn = document.getElementsByClassName("close")[0];
        if (closeBtn) {
            closeBtn.onclick = function() {
                document.getElementById("myModal").style.display = "none";
            }
        }

        // Cerrar también si se hace clic fuera de la imagen (en el fondo oscuro)
        window.onclick = function(event) {
            var modal = document.getElementById("myModal");
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }    

    </script>

    </body>
</html>
<?php
}
?>
