<?php
function CONTENIDO_mupis($mupi="") {
	global $session;
	echo '<h1>Gestión de ' . _NOMBRE_ . '</h1>';
	if ( $session->isAdmin() && isset($_POST['registrar_mupi']) ) {
	//Nos toca registrar un MUPI
	MUPI_REGISTRAR();
	}
	echo '<hr /><h2>'._NOMBRE_." disponibles</h2>";
	verMUPIS();
	if ( $session->isAdmin() ) {
	if ($mupi) $mupiex = " (".$mupi.")";
	echo '<hr /><h2>Registrar/Actualizar '._NOMBRE_.$mupiex.'</h2>';
	verMUPISregistro($mupi);
	}
}
function verMUPIS(){
   global $database;
   //$q = "SELECT codigo_mupi 'Código "._NOMBRE_."', direccion 'Dirección', foto_generica 'Foto Genérica', lon 'Longitud', lat 'Latitud', codigo_evento 'Evento' FROM ".TBL_MUPI.";";
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
	echo '<table border="0">';
	echo "<tr><th>Código "._NOMBRE_."</th><th>Dirección</th><th>Foto Genérica</th><th>Longitud</th><th>Latitud</th><th>Evento</th></tr>";
	for($i=0; $i<$num_rows; $i++){
		$codigo_mupi  = CREAR_LINK_GET("gestionar+mupis:".mysql_result($result,$i,"codigo_mupi"), mysql_result($result,$i,"codigo_mupi"), "Carga los datos del "._NOMBRE_. " seleccionado para editar");
		$direccion = mysql_result($result,$i,"direccion");
		$foto_generica = mysql_result($result,$i,"foto_generica");
		$Longitud  = mysql_result($result,$i,"lon");
		$Latitud  = mysql_result($result,$i,"lat");
		$codigo_evento  = mysql_result($result,$i,"codigo_evento");
	echo "<tr><td>$codigo_mupi</td><td>$direccion</td><td>$foto_generica</td><td>$Longitud</td><td>$Latitud</td><td>$codigo_evento</td></tr>";
	}
	echo "</table><br />";
}

function verMUPISregistro($mupi="") {
global $form, $database;

if ($mupi) {
	$q = "SELECT * FROM ".TBL_MUPI." WHERE codigo_mupi='$mupi';";
	$result = $database->query($q);
	$form->setValue("codigo", mysql_result($result,0,"codigo_mupi"));
	$form->setValue("direccion", mysql_result($result,0,"direccion"));
	$form->setValue("foto", mysql_result($result,0,"foto_generica"));
	$form->setValue("lon", mysql_result($result,0,"lon"));
	$form->setValue("lat", mysql_result($result,0,"lat"));	
}
echo '
<form action="./?'._ACC_.'=gestionar+mupis" method="POST">
<table>
<tr><td>Código '._NOMBRE_.':</td><td><input type="text" name="codigo" maxlength="100" style="width: 100%;" value="' . $form->value("codigo"). '"></td></tr>
<tr><td>Dirección:</td><td><input type="text" name="direccion" style="width: 100%;" maxlength="255" value="' . $form->value("direccion"). '"></tr>
<tr><td>Foto genérica:</td><td><input type="text" name="foto" style="width: 100%;" maxlength="255" value="' . $form->value("foto"). '"></td></tr>
<tr><td>Longitud:</td><td><input type="text" name="lon" style="width: 100%;" maxlength="50" value="' . $form->value("lon"). '"></td></tr>
<tr><td>Latitud:</td><td><input type="text" name="lat" style="width: 100%;" maxlength="50" value="' . $form->value("lat"). '"></td></tr>
</table>
<input type="submit" value="Registrar">
<input type="hidden" name="registrar_mupi" value="1">
</form>';
}

function MUPI_REGISTRAR() {
global $database,$form;
$form->setValue("codigo", $_POST['codigo']);
$form->setValue("direccion", $_POST['direccion']);
$form->setValue("foto", $_POST['foto']);
$form->setValue("lon", $_POST['lon']);
$form->setValue("lat", $_POST['lat']);
$q = "INSERT INTO ".TBL_MUPI." (codigo_mupi, direccion, foto_generica, lon, lat) VALUES ('".$_POST['codigo'] . "', '" . $_POST['direccion'] . "', '" . $_POST['foto'] . "', '" . $_POST['lon'] . "', '" . $_POST['lat'] . "');";
DEPURAR ($q);	
$result = $database->query($q);
}
?>
