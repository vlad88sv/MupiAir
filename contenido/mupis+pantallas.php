<?php
function CONTENIDO_pantallas() {
global $session;
	echo '<h1>Gestión de pantallas de ' . _NOMBRE_ . '</h1>';
	if ( $session->isAdmin() && isset($_POST['registrar_mupi']) ) {
	//Nos toca registrar un MUPI
	Pantalla_REGISTRAR();
	}
	echo '<hr /><h2>Pantallas '._NOMBRE_." registradas para Ud.</h2>";
	verPantallas();
	if ( $session->isAdmin() ) {
	echo '<hr /><h2>Registrar Pantallas</h2>';
	verPantallasregistro();
	}
}
function verPantallas(){
   global $database;
   $q = "SELECT codigo_cara_mupi 'Código Pantalla', codigo_mupi ' Código " . _NOMBRE_."', codigo 'Código propietario', alquilado_desde 'Alquilado desde', codigo_evento 'Evento', foto 'Foto' FROM ".TBL_MUPI_FACES.";";
   $result = $database->query($q);
   /* Error occurred, return given name by default */
   $num_rows = mysql_numrows($result);
   if(!$result || ($num_rows < 0)){
      echo "Error mostrando la información";
      return;
   }
   if($num_rows == 0){
      echo "¡No hay Pantallas "._NOMBRE_." ingresadas!<BR />";
      return;
   }
   echo Query2Table($result);
}
function verPantallasregistro() {
global $form;
echo '
<form action="./?'._ACC_.'=gestionar+pantallas" method="POST">
<table>
<tr><td>Código  Pantalla '._NOMBRE_.':</td><td><input type="text" name="codigo" maxlength="100" style="width: 100%;" value="' . $form->value("codigo"). '"></td></tr>
<tr><td>Foto:</td><td><input type="text" name="foto" style="width: 100%;" maxlength="255" value="' . $form->value("foto"). '"></td></tr>
</table>
<input type="submit" value="Registrar">
<input type="hidden" name="registrar_mupi" value="1">
</form>';
}

function Pantalla_REGISTRAR() {
global $database,$form;
$form->setValue("codigo", $_POST['codigo']);
$form->setValue("foto", $_POST['foto']);
echo "Registrado ".  $_POST['codigo'];
$q = "INSERT INTO ".TBL_MUPI_FACES." (codigo_cara_mupi, foto) VALUES ('".$_POST['codigo'] . "', '" . $_POST['foto'] ."');";
DEPURAR ($q);
$result = $database->query($q);
}
?>
