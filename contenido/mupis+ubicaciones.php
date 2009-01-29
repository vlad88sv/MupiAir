<?php
function CONTENIDO_mupis_ubicaciones($usuario=''){
global $session, $database,$map;
if ( !$session->isAdmin() ) { $usuario = $session->codigo; }
echo "<h1>Ubicaciones de MUPIS contratados</h1><hr />";
/* Iniciar gestor de mapas de google */
// AJAX ;)
echo 
SCRIPT('
	$("#combo_catorcenas").click(function (){$("#datos_calles").load("contenido/mupis+ubicaciones+dinamico.php?accion=calles&catorcena="+document.getElementsByName(\'combo_catorcenas\')[0].value);});
');
echo '<table>';
echo '<tr>';
// setup database for geocode caching
$map->setDSN('mysql://'.DB_USER.':'.DB_PASS.'@'.DB_SERVER.'/'.DB_NAME);
//Google Map Key
$map->setAPIKey(GOOGLE_MAP_KEY);
//Imagen de los marcadores
$map->setMarkerIcon('hojita.gif','hojita.gif',0,0,10,10);
// proporción de la ventana que tomará el mapa.
 $map->setWidth('100%');
// Cargar puntos mupis.
AgregarPuntosMupis($session->codigo,Obtener_catorcena_cercana(), 1);
$datos = '';
$datos .= $map->getHeaderJS();
$datos .= $map->getMapJS();
$datos .= $map->getOnLoad();
$datos .= $map->getMap();
$datos .= $map->getSidebar();
echo '<td id="grafico_mapa" width="80%">';
echo $datos;
echo '</td>';

echo '<td>';
echo 'Ver Catorcena:<br />' . $database->Combobox_CatorcenasConPresencia("combo_catorcenas",$usuario).'<br /><br />';
echo '<span id="datos_calles">Seleccione una catorcena por favor<br /><br /></span>';
echo '<span id="lista_mupis">Seleccione una calle por favor<br /><br /></span>';
//echo 'Ver Eco Mupis:<br /> '.$map->getSidebar().'<br /><br />';
echo '</td>';

echo '</tr>';
echo '</table>';
echo '<span id="datos_mupis">Seleccione un '._NOMBRE_.' por favor</span>';
}
function AgregarPuntosMupis($usuario='', $catorcena='', $calle=''){
   global $database, $map, $session;

   if ( $session->isAdmin()  && !$usuario ) {
	$q = "SELECT * FROM ".TBL_MUPI.";";
   } else {
	$q = "SELECT * FROM ".TBL_MUPI." WHERE codigo_mupi IN (SELECT DISTINCT codigo_mupi FROM emupi_mupis_caras AS a WHERE catorcena=$catorcena AND codigo_pedido IN (SELECT codigo_pedido FROM ".TBL_MUPI_ORDERS." WHERE codigo='$usuario'));";
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
}
?>
