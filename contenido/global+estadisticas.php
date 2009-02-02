<?php
function CONTENIDO_global_estadisticas(){
global $session, $database;
if ( $session->isAdmin() ) {
  echo "<h1>Estadísticas y notas administrativas</h1><hr />";
  echo "<h2>Comentarios publicados esta catorcena</h2>";
  $inicioCatorcena = Obtener_catorcena_cercana();
  $finCatorcena = Fin_de_catorcena($inicioCatorcena);
  $q = "SELECT (SELECT nombre FROM emupi_usuarios AS b WHERE b.codigo=a.codigo) AS codigo, comentario, timestamp, tipo FROM emupi_comentarios AS a WHERE timestamp>=$inicioCatorcena AND timestamp<=$finCatorcena ORDER BY tipo;";
  $result = $database->query($q);
  $num_rows = mysql_numrows($result);
  if ( $num_rows == 0 ) {
	  echo "¡No hay comentarios ingresados!<BR />";
      return;
   }
	echo '<table>';
	echo "<tr><th>Cliente</th><th>Comentario</th><th>Fecha</th><th>Tipo</th><th>Acciones</th></tr>";
   for($i=0; $i<$num_rows; $i++){
      $codigo  = mysql_result($result,$i,"codigo");
      $comentario  = mysql_result($result,$i,"comentario");
      $timestamp = date( "h:i:s @ d/m/Y", mysql_result($result,$i,"timestamp"));
      $tipo  = mysql_result($result,$i,"tipo") == '1' ? 'Público' : 'Privado';
      $Eliminar = CREAR_LINK_GET("gestionar+pedidos&amp;accion=eliminar&amp;pedido=".$i,"Eliminar", "Eliminar los datos de este pedido");
      echo "<tr><td>$codigo</td><td>$comentario</td><td>$timestamp</td><td>$tipo</td><td>$Eliminar</tr>";
   }
   echo "</table><br><hr />";
   echo "<h2>Pantallas activas esta catorcena</h2>";
  return;
}
echo "<h1>Estadísticas</h1>";
//Dinamismo en selección de catorcenas.
echo SCRIPT('
                $("#catorcenas_presencia").click(function (){$("#datos_catorcena").load("contenido/global+estadisticas+dinamico.php?catorcena="+document.getElementsByName(\'catorcenas_presencia\')[0].value);});
');
echo "Catorcena actual: <b>" . date("d/m/Y", Obtener_catorcena_cercana()) . ' a ' . date("d/m/Y", Fin_de_catorcena(Obtener_catorcena_cercana())) . "</b><br />";

$q = "SELECT COUNT(*) as cuenta FROM ". TBL_MUPI_FACES ." WHERE catorcena=".Obtener_catorcena_cercana()." AND codigo_pedido IN (SELECT codigo_pedido from ".TBL_MUPI_ORDERS." WHERE codigo = '".$session->codigo."');";
$result = $database->query($q);
echo "Número de caras publicitarias contratadas en catorcena actual: <b>" . mysql_result($result,0,"cuenta")."</b><br />";

$q = "SELECT SUM(catorcena_fin - catorcena_inicio) as cuenta FROM emupi_mupis_pedidos WHERE codigo='".$session->codigo."';";
$result = $database->query($q);
echo "Número de catorcenas contratadas: <b>" . Contar_catorcenas(mysql_result($result,0,"cuenta"))."</b><br />";

$q = "SELECT SUM((SELECT impactos FROM " . TBL_STREETS . " WHERE codigo_calle = (SELECT codigo_calle FROM ".TBL_MUPI." AS c WHERE c.codigo_mupi=a.codigo_mupi))) AS 'Impactos' FROM ". TBL_MUPI_FACES ." AS a WHERE catorcena=".Obtener_catorcena_cercana()." AND codigo_pedido IN (SELECT codigo_pedido FROM ".TBL_MUPI_ORDERS." WHERE codigo='".$session->codigo."')".";";
$result = $database->query($q);
echo "Número de impactos publicitarios diarios: <b>" . (int) (mysql_result($result,0,"Impactos"))."</b><br />";

/*********************************************************************************************/
// Inicio de parte dinámica.
/*********************************************************************************************/
echo "<br />".$database->Combobox_CatorcenasConPresencia("catorcenas_presencia",$session->codigo);
echo '<hr><span id="datos_catorcena">Seleccione una catorcena por favor</span>';

return;
}
?>
