<?php
function CONTENIDO_mupis_ubicaciones($usuario=''){
global $map, $session, $database;
if ( !$session->isAdmin() ) { $usuario = $session->codigo; }
echo "<h1>Ubicaciones de MUPIS contratados</h1><hr />";
/* Iniciar gestor de mapas de google */
// AJAX ;)
echo 
SCRIPT('
	$("#ver_catorcenas").click(function (){
	    $("#ver_calles").load("contenido/mupis+ubicaciones+dinamico.php?accion=calles&catorcena="+document.getElementsByName(\'ver_catorcenas\')[0].value);
	});
');
// setup database for geocode caching
$map->setDSN('mysql://'.DB_USER.':'.DB_PASS.'@'.DB_SERVER.'/'.DB_NAME);
//Google Map Key
$map->setAPIKey(GOOGLE_MAP_KEY);
//Imagen de los marcadores
$map->setMarkerIcon('hojita.gif','hojita.gif',0,0,10,10);
// proporción de la ventana que tomará el mapa.
 $map->setWidth('100%');
// Cargar puntos mupis.
AgregarPuntosMupis($usuario);
$map->printHeaderJS();
$map->printMapJS();
$map->printOnLoad();
echo '<table>';
echo '<tr>';

echo '<td width="80%">';
echo $map->printMap();
echo '</td>';

echo '<td>';
echo 'Ver Catorcena:<br />' . $database->Combobox_CatorcenasConPresencia("ver_catorcenas",$usuario).'<br /><br />';
echo '<span id="ver_calles">Seleccione una catorcena por favor<br /></span>';
echo 'Ver Eco Mupis:<br /> '.$map->getSidebar().'<br /><br />';
echo '</td>';

echo '</tr>';
echo '</table>';
echo '<span id="datos_cara_mupis">Seleccione un '._NOMBRE_.' por favor</span>';
}

function AgregarPuntosMupis($usuario=''){
   global $database, $map, $session;
   
   if ( $session->isAdmin()  && !$usuario ) {
	$q = "SELECT * FROM ".TBL_MUPI.";";
   } else {
	$q = "SELECT * FROM " . TBL_MUPI . " WHERE codigo_mupi IN (select distinct codigo_mupi from " . TBL_MUPI_FACES . " WHERE codigo_pedido IN (SELECT codigo_pedido from " . TBL_MUPI_ORDERS . " where codigo='".$usuario."'));";
   }
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
}
?>
