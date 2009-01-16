<?php
function CONTENIDO_global_estadisticas(){
global $session;
if ( $session->isAdmin() ) {
  echo "<h1>Estadísticas de clientes</h1>";
}
echo "<h1>Estadísticas de MUPIS contratados</h1>";
}
?>
