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
			retornar ( Buscar (strip_tags($_GET['MUPI'])) );
		} else {
			retornar ( "Ud. esta utilizando incorrectamente este script de soporte." );
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
			retornar ( "Ud. esta utilizando incorrectamente este script de soporte. 2" );
		}	
		break;
	}
} else {
		retornar ( "Ud. esta utilizando incorrectamente este script de soporte." );
}

function retornar($texto) {
	exit ('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />' . $texto . '<br />');
}

function Buscar ($MUPI) {
   global $session;
   //echo $session->codigo;
   /* La logica aqui es que si el usuario que solicitó la búsqueda es administrador, entonces se le muestran todos los MUPIS, si no solo se le muestran los suyos */
   $datos ="";
   $link = @mysql_connect(DB_SERVER, DB_USER, DB_PASS) or die('Por favor revise sus datos, puesto que se produjo el siguiente error:<br /><pre>' . mysql_error() . '</pre>');
   mysql_select_db(DB_NAME, $link) or die('!->La base de datos seleccionada "'.$DB_base.'" no existe');
   if ( $session->isAdmin() ) {
	$q = "SELECT * FROM ".TBL_MUPI." WHERE codigo_mupi='".$MUPI."';";
   } else {
	$q = "SELECT * FROM ".TBL_MUPI." WHERE codigo_mupi='".$MUPI."';";
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
   echo '<h2>Datos del MUPI seleccionado</h2>';
    $datos .=  '<table>';
    $datos .=  "<tr><th>Código "._NOMBRE_."</th><th>Dirección</th><th>Foto genérica</th><th>Longitud</th><th>Latitud</th><th>Código evento</th></tr>";
   for($i=0; $i<$num_rows; $i++){
      $codigo_mupi  = mysql_result($result,$i,"codigo_mupi");
      $direccion = mysql_result($result,$i,"direccion");
      $foto_generica = mysql_result($result,$i,"foto_generica");
      $lon  = mysql_result($result,$i,"lon");
      $lat  = mysql_result($result,$i,"lat");
      $codigo_evento = mysql_result($result,$i,"codigo_evento");
      $datos .=  "<tr><td>$codigo_mupi</td><td>$direccion</td><td>$foto_generica</td><td>$lon</td><td>$lat</td><td>$codigo_evento</td></tr>";
   }
    $datos .=  "</table>";
    
    /* Pantallas */
    $datos .= '<hr />';
    $datos .= "<h2>Pantallas alquiladas de este MUPI</h2>";
    $q = "SELECT * FROM ".TBL_MUPI_FACES." WHERE codigo_mupi='".$MUPI."';";
   $result = @mysql_query($q, $link) or die('!->Ocurrió un error mientras se revisaba la disponibilidad del MUPI.<pre>'.mysql_error().'</pre>');
   /* Error occurred, return given name by default */
   $num_rows = mysql_numrows($result);
   if(!$result || ($num_rows < 0)){
      retornar("Error mostrando la información");
   }
 
 if($num_rows == 0){
      $datos .= "¡No hay pantallas alquiladas para ese código ($MUPI)!";
      retornar ($datos);
   }

    $datos .=  '<table>';
    $datos .=  "<tr><th>Código pantalla</th><th>Código pedido</th><th>Foto real</th><th>Código evento</th></tr>";
   for($i=0; $i<$num_rows; $i++){
      $codigo_pantalla = mysql_result($result,$i,"codigo_pantalla_mupi");
      $codigo_pedido = mysql_result($result,$i,"codigo_pedido");
      $foto_real = mysql_result($result,$i,"foto_real");
      $codigo_evento = mysql_result($result,$i,"codigo_evento");
      $datos .=  "<tr><td>$codigo_pantalla</td><td>$codigo_pedido</td><td>$foto_real</td><td>$codigo_evento</td></tr>";
   }
    $datos .=  "</table>";
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
	$q = "select * from emupi_mupis where codigo_calle=$calle and codigo_mupi IN (select codigo_mupi FROM emupi_mupis_caras WHERE catorcena=$catorcena AND codigo_pedido IN (SELECT codigo_pedido FROM emupi_mupis_pedidos WHERE codigo='$usuario'));";
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
      $direccion = mysql_result($result,$i,"direccion");
      $foto_generica = mysql_result($result,$i,"foto_generica");
      $lon  = mysql_result($result,$i,"lon");
      $lat  = mysql_result($result,$i,"lat");
      $codigo_evento = mysql_result($result,$i,"codigo_evento");
      $map->addMarkerByCoords($lon, $lat, $codigo_mupi, $direccion . "<br />[" . $codigo_mupi . "]");
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
