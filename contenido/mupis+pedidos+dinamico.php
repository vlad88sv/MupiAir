<?php
error_reporting(E_STRICT | E_ALL);
ob_start("ob_gzhandler"); 
date_default_timezone_set ('America/El_Salvador');
require_once('../include/const.php');
require_once('../include/sesion.php');
require_once('../include/fecha.php');
require_once('sub.php');
verpedidos();
function verPedidos(){
   global $database;
   
   $usuario = isset($_GET['usuario']) ? $_GET['usuario'] : "";   
   $WHERE = "";
   $num_rows = "";
   $w_usuario = $usuario ? "AND codigo='".$usuario."'" : "";
   $w_catorcena = isset($_GET['catorcena']) ? "AND catorcena_inicio <= '".$_GET['catorcena']."' AND catorcena_fin >= '".$_GET['catorcena'] ."'" : "";
   $q = "SELECT codigo_pedido, codigo, (SELECT nombre from ". TBL_USERS . " AS b WHERE a.codigo = b.codigo) as nombre, catorcena_inicio, catorcena_fin, foto_pantalla, costo , descripcion FROM ".TBL_MUPI_ORDERS." AS a WHERE 1 $w_usuario $w_catorcena ORDER BY codigo_pedido;";
   DEPURAR($q,0);
   $result = $database->query($q);
   
   if ( !$result ) {
      echo "Error mostrando la información";
      return;
   }
   
   $num_rows = mysql_numrows($result);
   if ( $num_rows == 0 ) {
      echo Mensaje ("¡No hay Pedidos "._NOMBRE_." ingresados!",_M_NOTA);
      return;
   }
   
echo '<table>';
echo "<tr><th>Código Pedido "._NOMBRE_."</th><th>Nombre cliente</th><th>Intervalo de alquiler</th><th>Número de catorcenas</th><th>Arte Pantalla</th><th>Costo</th><th>Descripción</th><th>Acciones</th></tr>";
   for($i=0; $i<$num_rows; $i++){
      $codigo_pedido  = mysql_result($result,$i,"codigo_pedido");
      $codigo =  CREAR_LINK_GET("gestionar+pedidos:".mysql_result($result,$i,"codigo"), mysql_result($result,$i,"nombre"), "Ver los pedidos de este cliente");
      $catorcena_inicio  = AnularFechaNula(mysql_result($result,$i,"catorcena_inicio"));
      $catorcena_fin  = AnularFechaNula(mysql_result($result,$i,"catorcena_fin"));
      $NumeroDeCatorcenas = Contar_catorcenas(mysql_result($result,$i,"catorcena_inicio"), mysql_result($result,$i,"catorcena_fin"));
      $foto_pantalla  = mysql_result($result,$i,"foto_pantalla");
	  if ( $foto_pantalla ) { $foto_pantalla = "<span ".GenerarTooltip(CargarImagenDesdeBD(mysql_result($result,$i,"foto_pantalla"),'200px'))." />". $foto_pantalla."</span>"; }
      $costo = "$". (int)(mysql_result($result,$i,"costo"));
	  $descripcion = (mysql_result($result,$i,"descripcion"));
      $Eliminar = CREAR_LINK_GET("gestionar+pedidos&amp;eliminar=".mysql_result($result,$i,"codigo_pedido")."&amp;imagen=" . mysql_result($result,$i,"foto_pantalla") ,"Eliminar", "Eliminar los datos de este pedido");
      $codigo_pedido  = CREAR_LINK_GET("gestionar+pedidos&amp;pedido=".$codigo_pedido,$codigo_pedido, "Editar los datos de este pedido");
      echo "<tr><td>$codigo_pedido</td><td>$codigo</td><td>$catorcena_inicio al $catorcena_fin</td><td>$NumeroDeCatorcenas</td><td>$foto_pantalla</td><td>$costo</td><td>$descripcion</td><td>$Eliminar</tr>";
   }
   echo "<tfoot>";
   echo "<td colspan='7'>Total de pedidos</td><td>$num_rows</td>";
   echo "</tfoot>";
   echo "</table><br>";
}    
?>
