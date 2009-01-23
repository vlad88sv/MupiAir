<?php
function CONTENIDO_mupis($usuario="",$mupi="") {
	global $session;
	echo '<h1>Gestión de ' . _NOMBRE_ . '</h1>';
	if ( $session->isAdmin() && isset($_POST['registrar_mupi']) ) {
	//Nos toca registrar un MUPI
	MUPI_REGISTRAR();
	}
	echo '<hr /><h2>'._NOMBRE_." disponibles</h2>";
	verMUPIS();
	if ( $session->isAdmin() ) {
	verMUPISregistro($usuario,$mupi);
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
	echo "<tr><th>Código "._NOMBRE_."</th><th>Dirección</th><th>Foto Genérica</th><th>Longitud</th><th>Latitud</th><th>Evento</th><th>Acciones</th></tr>";
	for($i=0; $i<$num_rows; $i++){
		$codigo_mupi  = CREAR_LINK_GET("gestionar+mupis&amp;mupi=".mysql_result($result,$i,"codigo_mupi"), mysql_result($result,$i,"codigo_mupi"), "Carga los datos del "._NOMBRE_. " seleccionado para editar");
		$direccion = mysql_result($result,$i,"direccion");
		$foto_generica = mysql_result($result,$i,"foto_generica");
		$Longitud  = mysql_result($result,$i,"lon");
		$Latitud  = mysql_result($result,$i,"lat");
		$codigo_evento  = mysql_result($result,$i,"codigo_evento");
		$Eliminar = CREAR_LINK_GET("gestionar+mupis&amp;accion=eliminar&amp;mupi=".mysql_result($result,$i,"codigo_mupi"),"Eliminar", "Eliminar los datos de este "._NOMBRE_);
	echo "<tr><td>$codigo_mupi</td><td>$direccion</td><td>$foto_generica</td><td>$Longitud</td><td>$Latitud</td><td>$codigo_evento</td><td>$Eliminar</td></tr>";
	}
	echo "</table><br />";
}

function verMUPISregistro($usuario="",$mupi="") {
global $form, $database;
$BotonCancelar = '';
$NombreBotonAccion = '';
if ($mupi) {
	$q = "SELECT * FROM ".TBL_MUPI." WHERE codigo_mupi='$mupi';";
	$result = $database->query($q);
	
	switch ( mysql_numrows($result) ) {
	case 1:
		$form->setValue("codigo", mysql_result($result,0,"codigo_mupi"));
		$form->setValue("direccion", mysql_result($result,0,"direccion"));
		$form->setValue("foto", mysql_result($result,0,"foto_generica"));
		$form->setValue("lon", mysql_result($result,0,"lon"));
		$form->setValue("lat", mysql_result($result,0,"lat"));
	
		$CampoCodigoMupi = '<input type="hidden" name="codigo_mupi" value="'.$mupi.'">';
		$NombreBotonAccion = "Editar";
		$BotonCancelar = '<input type="button" OnClick="window.location=\'./?'._ACC_.'=gestionar+mupis\'" value="Cancelar">';
		break;
	case 0:
		echo "No se encontró un Eco Mupis con este código<br />";
		return;
		break;
	default:
		echo "Error al búscar Eco Mupis solicitado<br />";
		return;
		break;
	}
} else {
	$CampoCodigoMupi = '<tr><td>Código '._NOMBRE_.':</td><td><input type="text" name="codigo_mupi" maxlength="100" style="width: 100%;" value="' . $form->value("codigo_mupi"). '"></td></tr>';
	$NombreBotonAccion = "Registrar";
}
	$mupiex ="";
	if ($mupi) $mupiex = " (".$mupi.")";
echo '<hr /><h2>'.$NombreBotonAccion.' '._NOMBRE_.$mupiex.'</h2>';
echo '
<form action="./?'._ACC_.'=gestionar+mupis" method="POST">
<table>
'.$CampoCodigoMupi.'
<tr><td>Dirección:</td><td><input type="text" name="direccion" style="width: 100%;" maxlength="255" value="' . $form->value("direccion"). '"></tr>
<tr><td>Foto genérica:</td><td><input type="text" name="foto" style="width: 100%;" maxlength="255" value="' . $form->value("foto"). '"></td></tr>
<tr><td>Longitud:</td><td><input type="text" name="lon" style="width: 100%;" maxlength="50" value="' . $form->value("lon"). '"></td></tr>
<tr><td>Latitud:</td><td><input type="text" name="lat" style="width: 100%;" maxlength="50" value="' . $form->value("lat"). '"></td></tr>
</table>
<input type="submit" value="'.$NombreBotonAccion.'">
'.$BotonCancelar.'
<input type="hidden" name="registrar_mupi" value="1">
</form>';
}

function MUPI_REGISTRAR() {
global $database,$form;
$form->setValue("codigo_mupi", $_POST['codigo_mupi']);
$form->setValue("direccion", $_POST['direccion']);
$form->setValue("foto", $_POST['foto']);
$form->setValue("lon", $_POST['lon']);
$form->setValue("lat", $_POST['lat']);
$q = "INSERT INTO ".TBL_MUPI." (codigo_mupi, direccion, foto_generica, lon, lat) VALUES ('".$_POST['codigo_mupi'] . "', '" . $_POST['direccion'] . "', '" . $_POST['foto'] . "', '" . $_POST['lon'] . "', '" . $_POST['lat'] . "') ON DUPLICATE KEY UPDATE codigo_mupi=VALUES(codigo_mupi), direccion=VALUES(direccion), foto_generica=VALUES(foto_generica), lon=VALUES(lon), lat=VALUES(lat);";
DEPURAR ($q);	
$result = $database->query($q);
}
?>
