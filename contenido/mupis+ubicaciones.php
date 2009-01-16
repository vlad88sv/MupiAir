<?php
function CONTENIDO_mupis_ubicaciones(){
global $map;
echo "<h1>Ubicaciones de MUPIS contratados</h1><hr />";
// setup database for geocode caching
$map->setDSN('mysql://'.DB_USER.':'.DB_PASS.'@'.DB_SERVER.'/'.DB_NAME);
//Google Map Key
$map->setAPIKey(GOOGLE_MAP_KEY);
//Imagen de los marcadores
$map->setMarkerIcon('hojita.gif','hojita.gif',0,0,10,10);
// proporción de la ventana que tomará el mapa.
 $map->setWidth('100%');
// Cargar puntos mupis.
AgregarPuntosMupis();
$map->printHeaderJS();
$map->printMapJS();
$map->printOnLoad();
echo '
<table>
<tr>
<td width="80%">';
$map->printMap();
echo '
</td>
<td width="20%">
<h2>Sus MUPIS</h2>';
$map->printSidebar();
echo '
</td>
</tr>
<tr>
<td colspan="2"><h2>Datos del MUPI seleccionado</h2></td>
</tr>
<tr>
<td>
<span id="datos_cara_mupis">Seleccione un '._NOMBRE_.' por favor</span>
</td>
</tr>
</table>
</body>';
}

function AgregarPuntosMupis(){
   global $database, $map;
   $q = "SELECT * FROM ".TBL_MUPI.";";
   $result = $database->query($q);
   /* Error occurred, return given name by default */
   $num_rows = mysql_numrows($result);
   if(!$result || ($num_rows < 0)){
      echo "Error mostrando la información";
      return;
   }
   if($num_rows == 0){
      echo "¡No hay "._NOMBRE_." ingresados!<BR />";
      return;
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
