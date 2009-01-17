<?php
error_reporting(E_STRICT | E_ALL);
require_once('../include/const.php');
if ( isset( $_GET['MUPI'] ) ) {
	return retornar ( Buscar (strip_tags($_GET['MUPI'])) );
} else {
	return retornar ( "Este MUPI no tiene caras asignadas" );
}

function retornar($texto) {
	exit ('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />' . $texto . '<br />');
}

function Buscar ($MUPI) {
   $datos ="";
   $link = @mysql_connect(DB_SERVER, DB_USER, DB_PASS) or die('Por favor revise sus datos, puesto que se produjo el siguiente error:<br /><pre>' . mysql_error() . '</pre>');
   mysql_select_db(DB_NAME, $link) or die('!->La base de datos seleccionada "'.$DB_base.'" no existe');
   $q = "SELECT * FROM ".TBL_MUPI." WHERE codigo_mupi='".$MUPI."';";
   $result = @mysql_query($q, $link) or die('!->Ocurrió un error mientras se revisaba la disponibilidad del MUPI.');
   /* Error occurred, return given name by default */
   $num_rows = mysql_numrows($result);
   if(!$result || ($num_rows < 0)){
      retornar("Error mostrando la información");
   }
 
 if($num_rows == 0){
      retornar ("¡No hay "._NOMBRE_." con ese código ($MUPI)!");
   }

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
retornar($datos);
}
?>
