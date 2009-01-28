<?php
function CONTENIDO_global_estadisticas(){
global $session, $database;
if ( $session->isAdmin() ) {
  echo "<h1>Estadísticas de clientes</h1><hr />";
}
echo "<h1>Estadísticas</h1>";

$q = "SELECT COUNT(*) as cuenta FROM ". TBL_MUPI_FACES ." WHERE codigo_pedido IN (SELECT codigo_pedido from ".TBL_MUPI_ORDERS." WHERE codigo = '".$session->codigo."');";
$result = $database->query($q);
echo "Número de caras publicitarias contratadas en catorcena actual: " . mysql_result($result,0,"cuenta")."<br />";

$q = "SELECT COUNT(distinct catorcena_inicio) AS cuenta FROM ".TBL_MUPI_ORDERS." WHERE codigo='".$session->codigo."';";
$result = $database->query($q);
echo "Número de catorcenas contratadas: " . mysql_result($result,0,"cuenta")."<br />";

$q = "SELECT SUM((SELECT impactos FROM " . TBL_STREETS . " WHERE codigo_calle = (SELECT codigo_calle FROM ".TBL_MUPI." AS c WHERE c.codigo_mupi=a.codigo_mupi))) AS 'Impactos' FROM ". TBL_MUPI_FACES ." AS a WHERE catorcena=1231826400 AND codigo_pedido IN (SELECT codigo_pedido FROM ".TBL_MUPI_ORDERS." WHERE codigo='cliente2')".";";
$result = $database->query($q);
echo "Número de impactos publicitarios diarios: " . mysql_result($result,0,"Impactos")."<br />";

return;
}
?>
