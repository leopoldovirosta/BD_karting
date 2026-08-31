<?php
// Incluye las funciones de common.inc.php
require_once 'common.inc.php'; 

// Renderiza el encabezado
displayPageHeader('Inicio - Panel de Karting'); 
?>

<div style="padding: 20px;">
    <h2>Bienvenido al sistema</h2>
    <p>Utiliza el menú superior para navegar por los pilotos, carreras y clasificaciones.</p>
    <p>Si quieres actualizar algun dato de la ficha de piloto, o de algún resultado de carrera escríbeme a esta
        <a href="mailto:kartingblog@gmail.com" title="Enviar un correo electrónico" style="text-decoration: none; color: inherit;">
            <span class="material-symbols-outlined">mail</span>
        </a> dirección de correo<p>
</div>

<?php 
// Renderiza el pie de página y scripts
displayPageFooter(); 
?>
