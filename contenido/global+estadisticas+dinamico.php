<?php
error_reporting(E_STRICT | E_ALL);
ob_start("ob_gzhandler");
date_default_timezone_set ('America/El_Salvador');
require_once('../include/const.php');
require_once('../include/sesion.php');
require_once('sub.php');

if ( isset( $_GET['catorcena'] ) ) {
	retornar ( Buscar (strip_tags($_GET['catorcena'])) );
} else {
	retornar ( "Ud. esta utilizando incorrectamente este script de soporte." );
}

function retornar($texto) {
	exit ('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />' . $texto . '<br />');
}

function Buscar ($catorcena) {
   global $session;
   $datos ="";
   $link = @mysql_connect(DB_SERVER, DB_USER, DB_PASS) or die('Por favor revise sus datos, puesto que se produjo el siguiente error:<br /><pre>' . mysql_error() . '</pre>');
   mysql_select_db(DB_NAME, $link) or die(Mensaje('!->La base de datos seleccionada "'.$DB_base.'" no existe',_M_ERROR));
   $q = "SELECT SUM((SELECT impactos FROM " . TBL_STREETS . " WHERE codigo_calle = (SELECT codigo_calle FROM ".TBL_MUPI." AS c WHERE c.id_mupi=a.codigo_mupi))) AS 'Impactos' FROM ". TBL_MUPI_FACES ." AS a WHERE catorcena=$catorcena AND codigo_pedido IN (SELECT codigo_pedido FROM ".TBL_MUPI_ORDERS." WHERE codigo='".$session->codigo."')".";";
   $result = @mysql_query($q, $link) or retornar (Mensaje('Ocurrió un error mientras se obtenian las estadísticas.',_M_ERROR));
   $num_rows = mysql_numrows($result);
   
   if(!$result || ($num_rows < 0)){
      retornar("Error mostrando la información");
   }
 
   if($num_rows == 0){
      retornar ("¡No hay pantallas registradas a su nombre en la catorcena seleccionada!");
   }

   $Impactos  = mysql_result($result,0,"Impactos");
   $ImpactosCatorcena  = bcmul ($Impactos, "14");
   if (!$Impactos) {
	   retornar (Mensaje("¡ups!... parece que no existe referencia número de impactos para sus calles",_M_ERROR)); 
   }
   $datos .= '<b>'. ($Impactos) . "</b> Impactos diarios" . '<br />';
   $datos .= '<b>'. ($ImpactosCatorcena) . "</b> Impactos en esta catorcena" . '<br />';

   $q = "SELECT SUM(Impactos) AS impactos FROM (SELECT DISTINCT @calle := (SELECT codigo_calle FROM emupi_mupis AS c WHERE c.id_mupi=a.codigo_mupi) AS 'Calle', (SELECT impactos FROM emupi_calles WHERE codigo_calle = @calle) AS 'Impactos' FROM emupi_mupis_caras AS a WHERE catorcena=$catorcena AND codigo_pedido IN (SELECT codigo_pedido FROM emupi_mupis_pedidos WHERE codigo='".$session->codigo."')) AS a;";
   $result = @mysql_query($q, $link) or retornar ('!->Ocurrió un error mientras se revisaba las estadísticas.');
   
   if(!$result || ($num_rows < 0)){
      retornar("Error mostrando la información",_M_ERROR));
   }
 
   if($num_rows == 0){
      retornar ("¡No hay pantallas registradas a su nombre en la catorcena seleccionada!");
   }
   
   $personasDiaro = mysql_result($result,0,"Impactos");
   $personasCatorcena = bcmul($personasDiaro, "14");
   $datos .= '<b>'. ($personasDiaro) . "</b> personas al menos visualizan su anuncio diariamente" . '<br />';
   $datos .= '<b>'. ($personasCatorcena) . "</b> personas al menos visualizan su anuncio en esta catorcena" . '<br />';
   
   $q = "select SUM(costo) AS cuenta from emupi_mupis_pedidos where codigo_pedido IN (select distinct codigo_pedido from emupi_mupis_caras where catorcena=$catorcena and codigo_pedido IN (SELECT codigo_pedido from emupi_mupis_pedidos where codigo='".$session->codigo."'));";
   $result = @mysql_query($q, $link) or retornar ('!->Ocurrió un error mientras se revisaba las estadísticas.');
   
   if(!$result || ($num_rows < 0)){
      retornar("Error mostrando la información");
   }
 
   if($num_rows == 0){
      retornar ("¡No hay pantallas registradas a su nombre en la catorcena seleccionada!");
   }
   
   $costo = mysql_result($result,0,"cuenta");
   $datos .= 'Costo por impacto: <b>$' . bcdiv ($costo,$ImpactosCatorcena,10) . '</b><br />';
   $datos .= 'Número de impactos por persona: <b>' . bcdiv($Impactos,$personasDiaro,0) . '</b><br />';
   
   retornar($datos);
}
?>
