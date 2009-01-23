<?php
function CONTENIDO_global_estadisticas(){
global $session, $database;
if ( $session->isAdmin() ) {
  echo "<h1>Estadísticas de clientes</h1>";
}
$q = "select count(*) as cuenta from emupi_mupis_caras where codigo_pedido IN (select codigo_pedido from emupi_mupis_pedidos where codigo = '".$session->codigo."');";
$result = $database->query($q);
echo "Número de caras publicitarias contratadas en catorcena actual: " . mysql_result($result,0,"cuenta")."<br />";
$q = "select count(distinct alquilado_desde) as cuenta from emupi_mupis_pedidos where codigo='".$session->codigo."';";
$result = $database->query($q);
echo "Número de catorcenas contratadas: " . mysql_result($result,0,"cuenta")."<br />";
return;
}
?>
