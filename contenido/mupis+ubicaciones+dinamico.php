<?php
error_reporting(E_STRICT | E_ALL);
ob_start("ob_gzhandler"); 
date_default_timezone_set ('America/El_Salvador');
require_once('../include/const.php');
require_once('../include/sesion.php');
require_once('../include/fecha.php');
require_once('sub.php');
require_once('../include/maps/GoogleMapAPI.class.php');
$map = new GoogleMapAPI;
if ( isset( $_GET['accion'] ) ) {
	switch ( $_GET['accion'] ) {
	case "mupi":
		if ( isset( $_GET['MUPI'] ) ) {
		
			$parte = explode ('|',$_GET['MUPI'] ); 
			if ( count($parte) == 3 ) {
				//retornar ("Mupi: " . $parte[0]. ", Catorcena: ". $parte[1]. ", Usuario:".$parte[2]);
				retornar ( Buscar ($parte[0], $parte[1], $parte[2] ) );
			}
		} else {
			retornar ( "Ud. esta utilizando incorrectamente este script de soporte. 1" );
		}
		break;
	case "calles":
		if ( isset( $_GET['catorcena'] ) && isset ( $_GET['usuario'] ) ) {
			$script = SCRIPT('
			$("#grafico_mapa").load("contenido/mupis+ubicaciones+dinamico.php?accion=mapas&usuario='.$_GET['usuario'].'&catorcena="+document.getElementsByName(\'combo_catorcenas\')[0].value+"&calle="+document.getElementsByName(\'combo_calles\')[0].value);
			$("#combo_calles").change(function (){$("#grafico_mapa").load("contenido/mupis+ubicaciones+dinamico.php?accion=mapas&usuario='.$_GET['usuario'].'&catorcena="+document.getElementsByName(\'combo_catorcenas\')[0].value+"&calle="+document.getElementsByName(\'combo_calles\')[0].value);});
			').'<br />';
			retornar ('<b>Ver Calle:</b><br />' . $database->Combobox_CallesConPresencia("combo_calles",$_GET['usuario'],$_GET['catorcena']).$script);
		} else {
			retornar ( "Ud. esta utilizando incorrectamente este script de soporte. 2" );
		}
		break;
	case "mapas":
		if ( isset( $_GET['catorcena'] ) && isset( $_GET['calle'] ) && isset ( $_GET['usuario'] ) ) {
			retornar (Mostrar_Mapa($_GET['catorcena'], $_GET['calle'], $_GET['usuario']));
		} else {
			retornar ( "Ud. esta utilizando incorrectamente este script de soporte. 3" );
		}	
		break;
	}
} else {
		retornar ( "Ud. esta utilizando incorrectamente este script de soporte. 0" );
}

function retornar($texto) {
	exit ('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />' . $texto . '<br />');
}

function Buscar ($codigo_mupi, $catorcena, $usuario) {
   global $session;
   /* La logica aqui es que si el usuario que solicitó la búsqueda es administrador, entonces se le muestran todos los MUPIS, si no solo se le muestran los suyos */
   $datos ="";
   $link = @mysql_connect(DB_SERVER, DB_USER, DB_PASS) or die('Por favor revise sus datos, puesto que se produjo el siguiente error:<br /><pre>' . mysql_error() . '</pre>');
   mysql_select_db(DB_NAME, $link) or die('!->La base de datos seleccionada "'.$DB_base.'" no existe');
   if ( $session->isAdmin() || $session->userlevel == SALESMAN_LEVEL) {
	$q = "select tipo_pantalla, foto_real, (SELECT foto_pantalla FROM emupi_mupis_pedidos as b where a.codigo_pedido=b.codigo_pedido) AS arte from emupi_mupis_caras as a where catorcena=$catorcena AND codigo_mupi = (SELECT id_mupi FROM emupi_mupis WHERE id_mupi=$codigo_mupi);";
   } else {
	$q = "select tipo_pantalla, foto_real, (SELECT foto_pantalla FROM emupi_mupis_pedidos as b where a.codigo_pedido=b.codigo_pedido) AS arte from emupi_mupis_caras as a where catorcena=$catorcena AND codigo_pedido IN (SELECT codigo_pedido FROM emupi_mupis_pedidos where codigo='$usuario') AND codigo_mupi = (SELECT id_mupi FROM emupi_mupis WHERE id_mupi=$codigo_mupi);";
   }
   //echo $q.'<br />';
   $result = @mysql_query($q, $link) or retornar ('!->Ocurrió un error mientras se revisaba la disponibilidad del MUPI.');
   /* Error occurred, return given name by default */
   $num_rows = mysql_numrows($result);
   if(!$result || ($num_rows < 0)){
      retornar("Error mostrando la información");
   }
 
 if($num_rows == 0){
      retornar (Mensaje("¡No hay datos para ese código ($codigo_mupi)!",_M_ERROR));
   }
   $datos .= '<h2>Datos del MUPI seleccionado</h2>';
   $datos .= '<table>';
   $tipoPantalla = ''; //Par
   for($i=0; $i<$num_rows; $i++){
	  $arte = mysql_result($result,$i,"arte");
      $tipo_pantalla  = mysql_result($result,$i,"tipo_pantalla");
      $foto_real = mysql_result($result,$i,"foto_real");
      // si es par es vehicular
      if ( ($tipo_pantalla % 2) == 0 ) {
		$tipoPantalla = 'vehicular';
		   
      }else{
		$tipoPantalla = 'peatonal';
      }
	
	$datos .= "<tr><th><center>Imagen actual de su pantalla ".$tipoPantalla.":</center></th></tr>";
	$datos .= "<tr><td><center>" . CargarImagenDesdeBD($foto_real,"	300px","300px") . "</center></td>";
	$datos .= "<tr><th><center>Arte digital de su pantalla:</center></th></tr>";
	$datos .= "<tr><td><center>" . CargarImagenDesdeBD($arte,"300px","300px") . "</center></td></tr>";	
   }
   $datos .= '</table>';
retornar($datos);
}

function Mostrar_Mapa($catorcena, $calle, $usuario){
global $session, $map, $database;
// setup database for geocode caching
$map->setDSN('mysql://'.DB_USER.':'.DB_PASS.'@'.DB_SERVER.'/'.DB_NAME);
//Google Map Key
$map->setAPIKey(GOOGLE_MAP_KEY);
// proporción de la ventana que tomará el mapa.
$map->setWidth('100%');
// Desactivar controles de Zoom y movimiento para cliente.
if ( !$session->isAdmin() && $session->userlevel != SALESMAN_LEVEL ) {
	$map->map_controls = false;
	$map->disable_drag = true;
}
// Cargar puntos mupis.
$WHERE_USER = '';
if ( ($session->isAdmin() && !$usuario) || $session->userlevel == SALESMAN_LEVEL ) {
	$q = "select id_mupi, codigo_mupi, direccion, foto_generica, lon, lat, codigo_evento, codigo_calle from emupi_mupis AS a where codigo_calle='$calle' and id_mupi IN (select codigo_mupi FROM emupi_mupis_caras WHERE catorcena=$catorcena);";
} else {
 	$q = "select id_mupi, codigo_mupi, direccion, foto_generica, lon, lat, codigo_evento, codigo_calle, (SELECT logotipo from emupi_usuarios where codigo='$usuario') as logotipo from emupi_mupis where codigo_calle='$calle' and id_mupi IN (select codigo_mupi FROM emupi_mupis_caras WHERE catorcena=$catorcena AND codigo_pedido IN (SELECT codigo_pedido FROM emupi_mupis_pedidos WHERE codigo='$usuario'));";
}
   //DEPURAR($q,1);
   $result = $database->query($q);
   $num_rows = mysql_numrows($result);
   if(!$result || ($num_rows < 0)){
      exit ( "Error mostrando la información<br />");
   }
   
   if($num_rows == 0){
      exit ("¡No hay "._NOMBRE_." ingresados!<br />");
   }
	//Imagen de los marcadores
	//Removido por petición. 06/02/09
	//if ( !$session->isAdmin() || $usuario ) $map->setMarkerIcon('http://'.$_SERVER['SERVER_ADDR'].'/mupi/include/ver.php?id='.mysql_result($result,0,"logotipo"),'',0,0,0,0);
   
   for($i=0; $i<$num_rows; $i++){
      $id_mupi  = mysql_result($result,$i,"id_mupi");
      $codigo_mupi  = mysql_result($result,$i,"codigo_calle") . "." .mysql_result($result,$i,"codigo_mupi");
      $direccion = truncate(mysql_result($result,$i,"direccion"));
      $foto_generica = mysql_result($result,$i,"foto_generica");
      $lon  = mysql_result($result,$i,"lon");
      $lat  = mysql_result($result,$i,"lat");
      $codigo_evento = mysql_result($result,$i,"codigo_evento");
		if ( ($session->isAdmin() && !$usuario) || $session->userlevel == SALESMAN_LEVEL ) {
			$q = "SELECT DISTINCT logotipo FROM emupi_usuarios where codigo IN (SELECT codigo from emupi_mupis_pedidos where codigo_pedido IN (SELECT codigo_pedido FROM emupi_mupis_caras as b WHERE catorcena=$catorcena AND b.codigo_mupi=".mysql_result($result,$i,"id_mupi")."));";
			//echo $q."<br>";
			$result2 = $database->query($q);
			$num_rows2 = mysql_numrows($result2);
			$logotipo = '<br />';
			
			if($num_rows > 0){
				   for($ii=0; $ii<$num_rows2; $ii++){
					   $logotipo .= '<img style="width:200px;height:200px;" src="include/ver.php?id='.mysql_result($result2,$ii,"logotipo").'" />';
				   }
			}
		} else {
			$logotipo = "<br />".CargarImagenDesdeBD(mysql_result($result,$i,"logotipo"), "200px","200px");
		}
      
      $html = "<b>Dirección: </b>".$direccion."<br /><center>".$logotipo."</center>";
      $map->addMarkerByCoords($lon, $lat, $codigo_mupi . ' | ' . $direccion, $html, $codigo_mupi, $id_mupi . "|" . $catorcena . "|" . $usuario);
	  $map->addMarkerIcon(public_base_directory().'/punto.gif','',12,12,0,0);
   }
   
   // Mostrar referencias. 10/02/09
   $q = "SELECT * FROM emupi_referencias WHERE codigo_calle='$calle'".";";
   $result = $database->query($q);
   $num_rows = mysql_numrows($result);
   
   for($i=0; $i<$num_rows; $i++){
      $lon  = mysql_result($result,$i,"lon");
      $lat  = mysql_result($result,$i,"lat");
	  $logotipo = "<br />".CargarImagenDesdeBD(mysql_result($result,$i,"imagen_referencia"), "200px","200px");
	  $map->addMarkerByCoords($lon, $lat, "Referencia" , "Este es un punto de referencia<br />".$logotipo, '', '');
	  $map->addMarkerIcon(public_base_directory(). '/include/ver.php?id='.mysql_result($result,$i,"imagen_referencia"),'',0,0,50,50);
	  
   }
   
   
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
$datos = '';
$datos .= $map->getMapJS();
$datos .= $map->getMap();
$datos .= $map->getSidebar();
$datos .= SCRIPT('onLoad();');
return $datos;
}

function public_base_directory()
{
	$url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://';
	$url .= $_SERVER['HTTP_HOST'];
    //get public directory structure eg "/top/second/third"
    $public_directory = dirname($_SERVER['PHP_SELF']);
    //place each directory into array
    $directory_array = explode('/', $public_directory);
    //get highest or top level in array of directory strings
    $public_base = max($directory_array);
   
    return $url."/".$public_base;
} 
?>
