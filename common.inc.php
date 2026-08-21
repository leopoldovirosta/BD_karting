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
 * Si el valor está vacío o es nulo, devuelve '---'.
 *
 * @param mixed $valor Valor recuperado de la base de datos
 * @param string $unidad Unidad de medida (ej: 'mm', 'cc', 'CV', 'kg')
 * @return string
 */
function formatearMedida($valor, $unidad = 'mm') {
    // Verificamos si el valor no es nulo ni cadena vacía
    if ($valor !== null && $valor !== '') {
        $valorLimpio = mostrarValor($valor);
        
        // Si el valor resultante tras pasar por mostrarValor es '---', lo mantenemos
        if ($valorLimpio === '---') {
            return '---';
        }
        
        return !empty($unidad) ? $valorLimpio . ' ' . $unidad : $valorLimpio;
    }
    
    return '---';
}
function displayPageHeader($pageTitle) {
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <meta charset="UTF-8">
        <title><?php echo $pageTitle ?></title>
        <link rel="stylesheet" type="text/css" href="common.css?v=<?php echo time(); ?>" />
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.2.3/css/flag-icons.min.css"/>
    </head>
    <body>
        <header>
<!--           <h1><?php echo $pageTitle ?></h1>  -->
        </header>
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
        <a href="#escuderias">Escuderías</a>
        <a href="#pilotos">Patrocinadores</a>
      </div>
    </li>
    <li><a href="https://karting.blog/events/" target="_blank">Eventos</a></li>
    <li><a href="https://karting.blog/" target="_blank">Blog</a></li>
  </ul>
</nav>

<?php
}

function displayPageFooter() {
?>
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
