<?php
function CONTENIDO_pantallas($usuario, $pantalla) {
	global $session;
	echo '<h1>Gestión de pantallas de ' . _NOMBRE_ . '</h1>';
	if ( $session->isAdmin() && isset($_POST['registrar_mupi']) ) {
	//Nos toca registrar un MUPI
	Pantalla_REGISTRAR();
	}
	echo '<hr /><h2>Sus Pantallas '._NOMBRE_.".</h2>";
	verPantallas($usuario);
	if ( $session->isAdmin() ) {
	$paraUsuario = "";
	if ($usuario) {
		$paraUsuario = " para $usuario";
	}
	echo '<hr /><h2>Registrar Pantallas'.$paraUsuario.'</h2>';
	verPantallasregistro($pantalla);
	}
}
function verPantallas($usuario="", $pantalla=""){
   global $database;
   
   $WHERE = "";
   if ($usuario) {
    $WHERE = " WHERE codigo='".$usuario."'";
    }
    
   //$q = "SELECT codigo_cara_mupi 'Código Pantalla', codigo_mupi 'Código " . _NOMBRE_."', codigo 'Código propietario', alquilado_desde 'Alquilado desde', codigo_evento 'Evento', foto 'Foto' FROM ".TBL_MUPI_FACES."$WHERE;";
   $q = "SELECT * FROM ".TBL_MUPI_FACES."$WHERE;";
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
echo '<table>';
echo "<tr><th>Código Pantalla "._NOMBRE_."</th><th>Código "._NOMBRE_."</th><th>Código propietario</th><th>Alquilado desde'</th><th>Evento</th><th>Foto</th></tr>";
   for($i=0; $i<$num_rows; $i++){
      $codigo_cara_mupi  = CREAR_LINK_GET("gestionar+pantallas&amp;pantalla=".mysql_result($result,$i,"codigo_cara_mupi"),mysql_result($result,$i,"codigo_cara_mupi"), "Editar los datos de esta pantalla");
      $codigo_mupi = mysql_result($result,$i,"codigo_mupi");
      $codigo = mysql_result($result,$i,"codigo");
      $alquilado_desde  = mysql_result($result,$i,"alquilado_desde");
      $codigo_evento  = mysql_result($result,$i,"codigo_evento");
      $Foto = mysql_result($result,$i,"Foto");
      echo "<tr><td>$codigo_cara_mupi</td><td>$codigo_mupi</td><td>$codigo</td><td>$alquilado_desde</td><td>$codigo_evento</td><td>$Foto</td></tr>";
   }
   echo "</table><br>";
}
function verPantallasregistro($pantalla="") {
global $form, $database;
if ($pantalla) {
	$q = "SELECT * FROM ".TBL_MUPI_FACES." WHERE codigo_cara_mupi='$pantalla';";
	$result = $database->query($q);
	$form->setValue("codigo", mysql_result($result,0,"codigo_cara_mupi"));
	$form->setValue("foto", mysql_result($result,0,"foto"));
}
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
$q = "INSERT INTO ".TBL_MUPI_FACES." (codigo_cara_mupi, foto) VALUES ('".$_POST['codigo'] . "', '" . $_POST['foto'] ."')  ON DUPLICATE KEY UPDATE codigo_cara_mupi=VALUES(codigo_cara_mupi), foto=VALUES(foto);";
DEPURAR ($q);
$result = $database->query($q);
}
?>
