<?php
function CONTENIDO_mupis($usuario="",$mupi="") {
	global $session;
	echo '<h1>Gestión de ' . _NOMBRE_ . '</h1>';
	if ( $session->isAdmin() ) {
		
		//Nos toca registrar un MUPI
		if ( isset($_POST['registrar_mupi']) ) {
		MUPI_REGISTRAR();
		}

		//Nos toca elimiinar un MUPI
		if ( isset($_GET['eliminar']) ) {
		global $database;
		// Eliminamos la pantalla
		$q = "DELETE FROM " . TBL_MUPI . " WHERE codigo_mupi='" . $_GET['eliminar'] . "';";
		$result = $database->query($q);
		if ( $result ) { echo "Eco Mupis eliminado<br />"; }
		}
		
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
   $q = "SELECT codigo_mupi, direccion, foto_generica, lon, lat, codigo_evento, codigo_calle, (SELECT ubicacion FROM ".TBL_STREETS." AS b WHERE a.codigo_calle=b.codigo_calle) AS 'calle' FROM ".TBL_MUPI." as a;";
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
	echo "<tr><th>Código "._NOMBRE_."</th><th>Dirección</th><th>Foto Genérica</th><th>Longitud</th><th>Latitud</th><th>Calle</th><th>Evento</th><th>Acciones</th></tr>";
	for($i=0; $i<$num_rows; $i++){
		$codigo_mupi  = CREAR_LINK_GET("gestionar+mupis&amp;mupi=".mysql_result($result,$i,"codigo_mupi"), mysql_result($result,$i,"codigo_mupi"), "Carga los datos del "._NOMBRE_. " seleccionado para editar");
		$direccion = mysql_result($result,$i,"direccion");
		$foto_generica = mysql_result($result,$i,"foto_generica");
		$Longitud  = mysql_result($result,$i,"lon");
		$Latitud  = mysql_result($result,$i,"lat");
		$codigo_evento  = mysql_result($result,$i,"codigo_evento");
		$codigo_calle  = CREAR_LINK_GET("gestionar+calles&amp;calle=".mysql_result($result,$i,"codigo_calle"), mysql_result($result,$i,"calle"), "Editar los datos de este pedido");
		$Eliminar = CREAR_LINK_GET("gestionar+mupis&amp;eliminar=".mysql_result($result,$i,"codigo_mupi"),"Eliminar", "Eliminar los datos de este "._NOMBRE_);
	echo "<tr><td>$codigo_mupi</td><td>$direccion</td><td>$foto_generica</td><td>$Longitud</td><td>$Latitud</td><td>$codigo_calle</td><td>$codigo_evento</td><td>$Eliminar</td></tr>";
	}
	echo "</table><br />";
}

function verMUPISregistro($usuario="",$mupi="") {
global $form, $database;
$BotonCancelar = '';
$NombreBotonAccion = '';
$foto_pantalla = '';
$OnChangePantalla = '';
$CampoConservarPantalla = '';
$CampoConservarPantalla2 = '';
if ($mupi) {
	$q = "SELECT * FROM ".TBL_MUPI." WHERE codigo_mupi='$mupi';";
	$result = $database->query($q);
	
	switch ( mysql_numrows($result) ) {
	case 1:
		$form->setValue("codigo", mysql_result($result,0,"codigo_mupi"));
		$form->setValue("direccion", mysql_result($result,0,"direccion"));
		$form->setValue("lon", mysql_result($result,0,"lon"));
		$form->setValue("lat", mysql_result($result,0,"lat"));
		$form->setValue("codigo_calle", mysql_result($result,0,"codigo_calle"));
		$foto_pantalla =  mysql_result($result,0,"foto_generica");
	if ( $foto_pantalla ) {
		$CampoConservarPantalla = '<tr><td>Conservar foto genérica con Id.'.$foto_pantalla.'</td></td><td><span id="CampoConservarPantalla"><input type="checkbox" name="ConservarPantalla" value="'.$foto_pantalla.'" checked="checked"></span></td></tr>';
		$CampoConservarPantalla2 = '<input type="hidden" name="ConservarPantalla2" value="'.$foto_pantalla.'">';	
		$OnChangePantalla = 'onchange="document.getElementById(\'CampoConservarPantalla\').innerHTML=\'Se reemplazará la imagen actual con la seleccionada\'"';
	}
	
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
<form action="./?'._ACC_.'=gestionar+mupis" enctype="multipart/form-data" method="POST">
<table>
'.$CampoCodigoMupi.'
<tr><td width="20%">Dirección específica:</td><td><input type="text" name="direccion" style="width: 100%;" maxlength="255" value="' . $form->value("direccion"). '"></tr>
'.$CampoConservarPantalla.'
'.$CampoConservarPantalla2.'
<tr><td>Foto genérica:</td><td><input type="file" name="foto_generica"></td></tr>
<tr><td>Longitud Decimal:</td><td><input type="text" name="lon" style="width: 100%;" maxlength="50" value="' . $form->value("lon"). '"></td></tr>
<tr><td>Latitud Decimal:</td><td><input type="text" name="lat" style="width: 100%;" maxlength="50" value="' . $form->value("lat"). '"></td></tr>
<tr><td>Código calle:</td><td>'. $database->Combobox_calle("codigo_calle", $form->value("codigo_calle")). '</td></tr>
</table>
<input type="submit" value="'.$NombreBotonAccion.'">
'.$BotonCancelar.'
<input type="hidden" name="registrar_mupi" value="1">
</form>';
}

function MUPI_REGISTRAR() {
global $database,$form;
if ( !isset($_POST['ConservarPantalla']) ) {
	/*
		Corroborar si ya tenia una imagen antes, para reutilizar la fila y a la vez
		que la imagen anterior no quede huerfana.
	*/
	$Pre_Id = isset($_POST['ConservarPantalla2']) ? $_POST['ConservarPantalla2'] : 0;
	$idImg = CargarImagenEnBD("foto_generica","MUPIS", $Pre_Id);
} else {
	$idImg = $_POST['ConservarPantalla'];
}
$form->setValue("codigo_mupi", $_POST['codigo_mupi']);
$form->setValue("direccion", $_POST['direccion']);
$form->setValue("lon", $_POST['lon']);
$form->setValue("lat", $_POST['lat']);
$form->setValue("codigo_calle", $_POST['codigo_calle']);
$q = "INSERT INTO ".TBL_MUPI." (codigo_mupi, direccion, foto_generica, lon, lat, codigo_calle) VALUES ('".$_POST['codigo_mupi'] . "', '" . $_POST['direccion'] . "'," . $idImg . ", '" . $_POST['lon'] . "', '" . $_POST['lat'] . "', '" . $_POST['codigo_calle'] . "') ON DUPLICATE KEY UPDATE codigo_mupi=VALUES(codigo_mupi), direccion=VALUES(direccion), foto_generica=VALUES(foto_generica), lon=VALUES(lon), lat=VALUES(lat), codigo_calle=VALUES(codigo_calle);";
DEPURAR ($q);	
$result = $database->query($q);
}
?>
