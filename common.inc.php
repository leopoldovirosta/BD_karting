<?php

function displayPageHeader($pageTitle) {
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <meta charset="UTF-8">
        <title><?php echo $pageTitle ?></title>
        <link rel="stylesheet" type="text/css" href="common.css" />
    </head>
    <body>
        <header>
           <h1><?php echo $pageTitle ?></h1>
        </header>
<nav>
  <ul class="nav-links">
    <li><a href="view_pilotos.php">Pilotos</a></li>
    <li><a href="view_carreras.php">Carreras</a></li>
    <li class="dropdown">
      <a href="javascript:void(0)" class="dropbtn">Campeonato ▾</a>
      <div class="dropdown-content">
        <a href="#circuitos">Circuitos</a>
        <a href="#circuitos">Campeonatos</a>
        <a href="#circuitos">Chasis</a>
        <a href="#circuitos">Motores</a>
        <a href="#escuderias">Escuderías</a>
        <a href="#pilotos">Patrocinadores</a>
      </div>
    </li>
    <li><a href="#contacto">Contacto</a></li>
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
            <p>&copy; 2026 <a href="https://leovirosta.blog" target="_blank">Leo Virosta</a></p>
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
