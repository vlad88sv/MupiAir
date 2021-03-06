<?php
error_reporting(E_STRICT | E_ALL);
date_default_timezone_set ('America/El_Salvador');
require_once('../include/const.php');
require_once('../include/sesion.php');
require_once('../include/fecha.php');
require_once('sub.php');

if ( isset($_GET['completo']) ) CONTENIDO__usuarios_completos();
if ( isset($_GET['resumen']) && isset($_GET['catorcena']) ) CONTENIDO__usuarios_resumen();

function CONTENIDO__usuarios_completos(){
global $session, $database, $form;
  $html = $where = '';
   if ( !$session->isAdmin() ) {
	   $where .= " AND userlevel = ".CLIENT_LEVEL;
   } else {
	   if ( isset($_GET['nivel']) ) {
		   if ($_GET['nivel']) {
		   $where .= " AND userlevel='".mysql_real_escape_string($_GET['nivel'])."'";
		   }
	   }
   }
   if ( isset($_GET['catorcena']) ) {
	   if ($_GET['catorcena']) {
	   $where .= " AND codigo IN (SELECT codigo FROM ".TBL_MUPI_ORDERS." WHERE codigo_pedido IN (SELECT codigo_pedido FROM ".TBL_MUPI_FACES." WHERE catorcena='".mysql_real_escape_string($_GET['catorcena'])."'))";
	   }
   }

   $q = "SELECT * FROM ".TBL_USERS." WHERE 1=1 $where ORDER BY userlevel DESC;";
   DEPURAR ($q, 0);
   $result = $database->query($q);
   /* Error occurred, return given name by default */
   $num_rows = mysql_numrows($result);
   if(!$result || ($num_rows < 0)){
      $html .= "Error mostrando la información";
      return;
   }
   if($num_rows == 0){
      /*Esto nunca deberia de pasar realmente...*/
      $html .= Mensaje ("¡No hay clientes/usuarios ingresados que coincidan con los criterios del filtro!", _M_INFO);
   }

echo '<hr />';
   echo '<table border="0">';
	if ( $session->isAdmin() ) {
	   echo "<tr><th>Código</th><th>Nombre</th><th>Nivel</th><th>Email</th><th>Última actividad</th><th>Acciones</th></tr>";
	} else {
	   echo "<tr><th>Nombre</th><th>Código</th><th>Acciones</th></tr>";
	}
   for($i=0; $i<$num_rows; $i++){
      $acciones='';
      $uname  = mysql_result($result,$i,"codigo");
      $logotipo = CargarImagenDesdeBD(mysql_result($result,$i,"logotipo"));
      $nombre = mysql_result($result,$i,"nombre");
      if ($session->isAdmin()) $ulevel = mysql_result($result,$i,"userlevel");
      if ($session->isAdmin()) $email  = mysql_result($result,$i,"email");
      if ($session->isAdmin()) $time   = date("d-m-y\nh:ia", mysql_result($result,$i,"timestamp"));
      if ($session->isAdmin()) $acciones .= CREAR_LINK_GET("gestionar+pedidos:$uname", "Pedidos", "Le mostrara los pedidos realizados por este cliente y le dará la opción de agregar más.")."<br />";
      if ($session->isAdmin()) $acciones .= CREAR_LINK_GET("gestionar+pantallas:$uname", "Pantallas", "Le mostrara las pantallas en las cuales se encuentran colocados los pedidos.")."<br />";
      $acciones .= CREAR_LINK_GET("ver+ubicaciones:$uname", "Ubicaciones", "Le mostrara las ubicaciones de los MUPIS de este cliente.")."<br />";
      $acciones .= CREAR_LINK_GET("ver+estadisticas:$uname", "Estadísticas", "Le mostrara las estadísticas de este cliente.")."<br />";
      if ($session->isAdmin()) $acciones .= "<hr />".CREAR_LINK_GET("ver+reportes:$uname", "Reporte", "Le generará un reporte sobre este cliente.");
      if ($session->isAdmin()) $uname = CREAR_LINK_GET("ver+cliente:".$uname, $uname, "Ver datos de este cliente");
      if ($session->isAdmin()) {
	  $html .= "<tr><td style='text-align:center;'>$logotipo<br />$uname</td><td>$nombre</td><td>$ulevel</td><td>$email</td><td>$time</td><td>$acciones</td></tr>";
      } else {
	  $html .= "<tr><td style='text-align:center;'>$logotipo<br />$nombre</td><td>$uname</td><td>$acciones</td></tr>";
      }
   }
$html .= "<tfoot>";
   if ( $session->isAdmin() ) {
	$html .= "<td colspan='5'>Total de registros encontrados para el filtro seleccionado</td><td>$num_rows</td>";
   } else {
	$html .= "<td colspan='2'>Total de registros encontrados para el filtro seleccionado</td><td>$num_rows</td>";
   }
   $html .= "</tfoot>";
   $html .= "</table><br />";
   exit ($html);
}
 
function CONTENIDO__usuarios_resumen(){
	global $session, $database, $form;
	$html = '';
	$catorcena = mysql_real_escape_string($_GET['catorcena']);
	$q_nPantalas = "(SELECT COUNT(*) as cuenta FROM ". TBL_MUPI_FACES ." AS c WHERE catorcena=".$catorcena." AND codigo_pedido IN (SELECT codigo_pedido from ".TBL_MUPI_ORDERS." AS b WHERE b.codigo = a.codigo)) AS npantallas";
	$q_nPantalas_Veh = "(SELECT COUNT(*) as cuenta FROM ". TBL_MUPI_FACES ." AS c WHERE tipo_pantalla='0' AND catorcena=".$catorcena." AND codigo_pedido IN (SELECT codigo_pedido from ".TBL_MUPI_ORDERS." AS b WHERE b.codigo = a.codigo)) AS npantallasv";
	$q_nPantalas_Pea = "(SELECT COUNT(*) as cuenta FROM ". TBL_MUPI_FACES ." AS c WHERE tipo_pantalla='1' AND catorcena=".$catorcena." AND codigo_pedido IN (SELECT codigo_pedido from ".TBL_MUPI_ORDERS." AS b WHERE b.codigo = a.codigo)) AS npantallasp";
	$q = "SELECT nombre, $q_nPantalas, $q_nPantalas_Veh , $q_nPantalas_Pea FROM ".TBL_USERS." AS a WHERE codigo IN (SELECT codigo FROM ".TBL_MUPI_ORDERS." WHERE codigo_pedido IN (SELECT codigo_pedido FROM ".TBL_MUPI_FACES." WHERE catorcena='".$catorcena."'));";
   DEPURAR ($q, 0);
   $result = $database->query($q);
   /* Error occurred, return given name by default */
   $num_rows = mysql_numrows($result);
   if(!$result || ($num_rows < 0)){
      $html .= "Error mostrando la información";
      return;
   }
   if($num_rows == 0){
      /*Esto nunca deberia de pasar realmente...*/
      $html .= Mensaje ("¡No hay clientes/usuarios ingresados que coincidan con los criterios del filtro!", _M_INFO);
   }

echo '<hr />';
   echo '<table border="0">';
   echo "<tr><th>Nombre</th><th>Número de pantallas</th><th>Vehiculares</th><th>Peatonales</th></tr>";
   for($i=0; $i<$num_rows; $i++){
      $nombre = mysql_result($result,$i,"nombre");
      $npantallas = mysql_result($result,$i,"npantallas");
      $npantallasv = mysql_result($result,$i,"npantallasv");
      $npantallasp = mysql_result($result,$i,"npantallasp");
	  $html .= "<tr><td style='text-align:center;'>$nombre</td><td>$npantallas</td><td>$npantallasv</td><td>$npantallasp</td></tr>";
   }
	$html .= "<tfoot>";
	$html .= "<td colspan='3'>Total de registros encontrados para el filtro seleccionado</td><td>$num_rows</td>";
   $html .= "</tfoot>";
   $html .= "</table><br />";
   exit ($html);
}

?>
