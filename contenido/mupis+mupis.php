<?php
function CONTENIDO_mupis() {
global $session;
	echo '<h1>Gestión de ' . _NOMBRE_ . '</h1>';
	if ( $session->isAdmin() && isset($_POST['registrar_mupi']) ) {
	//Nos toca registrar un MUPI
	MUPI_REGISTRAR();
	}
	echo '<hr /><h2>'._NOMBRE_." registrados para Ud.</h2>";
	verMUPIS();
	if ( $session->isAdmin() ) {
	echo '<hr /><h2>Registrar MUPIS</h2>';
	verMUPISregistro();
	}
}
function verMUPIS(){
   global $database;
   $q = "SELECT codigo_mupi 'Código "._NOMBRE_."', direccion 'Dirección', foto_generica 'Foto Genérica', lon 'Longitud', lat 'Latitud', codigo_evento 'Evento' FROM ".TBL_MUPI.";";
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
   echo Query2Table($result);
}
function verMUPISregistro() {
global $form;
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
