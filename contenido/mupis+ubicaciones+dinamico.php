<?php
error_reporting(E_STRICT | E_ALL);
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
			//retornar ("Mupi: " . $parte[0]. ", Catorcena: ". $parte[1]. ", Usuario:".$parte[2]);
			retornar ( Buscar ($parte[0], $parte[1], $parte[2] ) );
		} else {
			retornar ( "Ud. esta utilizando incorrectamente este script de soporte. 1" );
		}
		break;
	case "calles":
		if ( isset( $_GET['catorcena'] ) ) {
			$script = SCRIPT('$("#combo_calles").click(function (){$("#grafico_mapa").load("contenido/mupis+ubicaciones+dinamico.php?accion=mapas&catorcena="+document.getElementsByName(\'combo_catorcenas\')[0].value+"&calle="+document.getElementsByName(\'combo_calles\')[0].value);});').'<br /><br />';
			retornar ('Ver Calle:<br />' . $database->Combobox_CallesConPresencia("combo_calles",$session->codigo,$_GET['catorcena']).$script);
		} else {
			retornar ( "Ud. esta utilizando incorrectamente este script de soporte. 2" );
		}
		break;
	case "mapas":
		if ( isset( $_GET['catorcena'] ) && isset( $_GET['calle'] ) ) {
			retornar (Mostrar_Mapa($_GET['catorcena'], $_GET['calle'] ));
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
   if ( $session->isAdmin() ) {
	$q = "SELECT * FROM ".TBL_MUPI." WHERE codigo_mupi='".$MUPI."';";
   } else {
	$q = "select codigo_pantalla_mupi, foto_real, (SELECT foto_pantalla FROM emupi_mupis_pedidos as b where a.codigo_pedido=b.codigo_pedido) AS arte from emupi_mupis_caras as a where catorcena=$catorcena AND codigo_pedido IN (SELECT codigo_pedido FROM emupi_mupis_pedidos where codigo='$usuario') AND codigo_mupi='$codigo_mupi';";
   }
   $result = @mysql_query($q, $link) or retornar ('!->Ocurrió un error mientras se revisaba la disponibilidad del MUPI.');
   /* Error occurred, return given name by default */
   $num_rows = mysql_numrows($result);
   if(!$result || ($num_rows < 0)){
      retornar("Error mostrando la información");
   }
 
 if($num_rows == 0){
      retornar ("¡No hay "._NOMBRE_." con ese código ($MUPI)!");
   }
   $datos .= '<h2>Datos del MUPI seleccionado</h2>';
   $arte = mysql_result($result,0,"arte");
   $tipoPantalla = ''; //Par
   for($i=0; $i<$num_rows; $i++){
      $codigo_pantalla_mupi  = mysql_result($result,$i,"codigo_pantalla_mupi");
      $foto_real = mysql_result($result,$i,"foto_real");
      // si es par es vehicular
      if ( ($codigo_pantalla_mupi % 2) == 0 ) {
		$tipoPantalla = 'Vehicular';
      }else{
		$tipoPantalla = 'Peatonal';
      }
	$datos .= "<h3>Imagen actual de su pantalla ".$tipoPantalla.":</h3>".'<img src="include/ver.php?id='.$foto_real.'">';	
   }
   $datos .= "<h3>Arte digital de su pantalla:</h3>".'<img src="include/ver.php?id='.$arte.'">';
retornar($datos);
}

function Mostrar_Mapa($catorcena, $calle){
global $session, $map, $database;
// setup database for geocode caching
$map->setDSN('mysql://'.DB_USER.':'.DB_PASS.'@'.DB_SERVER.'/'.DB_NAME);
//Google Map Key
$map->setAPIKey(GOOGLE_MAP_KEY);
//Imagen de los marcadores
//$map->setMarkerIcon('../hojita.gif','../hojita.gif',0,0,10,10);
// proporción de la ventana que tomará el mapa.
 $map->setWidth('100%');
// Cargar puntos mupis.
$usuario = $session->codigo;
 if ( $session->isAdmin()  && !$usuario ) {
	$q = "SELECT * FROM ".TBL_MUPI.";";
   } else {
	$q = "select codigo_mupi, direccion, foto_generica, lon, lat, codigo_evento, codigo_calle, (SELECT logotipo from emupi_usuarios where codigo='$usuario') as logotipo  from emupi_mupis where codigo_calle=$calle and codigo_mupi IN (select codigo_mupi FROM emupi_mupis_caras WHERE catorcena=$catorcena AND codigo_pedido IN (SELECT codigo_pedido FROM emupi_mupis_pedidos WHERE codigo='$usuario'));";
	//echo $q."<br>";
   }
   //echo $q;
   $result = $database->query($q);
   /* Error occurred, return given name by default */
   $num_rows = mysql_numrows($result);
   if(!$result || ($num_rows < 0)){
      exit ( "Error mostrando la información<br />");
   }
   
   if($num_rows == 0){
      exit ("¡No hay "._NOMBRE_." ingresados!<br />");
   }
   
   for($i=0; $i<$num_rows; $i++){
      $codigo_mupi  = mysql_result($result,$i,"codigo_mupi");
      $direccion = "<b>Dirección: </b>".mysql_result($result,$i,"direccion")."<br />";
      $foto_generica = mysql_result($result,$i,"foto_generica");
      $lon  = mysql_result($result,$i,"lon");
      $lat  = mysql_result($result,$i,"lat");
      $codigo_evento = mysql_result($result,$i,"codigo_evento");
      $logotipo = "<br />".mysql_result($result,$i,"logotipo");
      $html = $direccion.$logotipo;
      
      $map->addMarkerByCoords($lon, $lat, $codigo_mupi . ' | ' . $direccion, $html, $codigo_mupi, $codigo_mupi . "|" . $catorcena . "|" . $usuario);
   }
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
$datos = '';
$datos .= $map->getMapJS();
$datos .= $map->getMap();
$datos .= $map->getSidebar();
$datos .= SCRIPT('onLoad();');
return $datos;
}
?>
