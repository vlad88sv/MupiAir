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
	if ($pantalla) {
		$edicionOregistro = 'Edición de pantalla ' . $pantalla;
	} else {
		$edicionOregistro = 'Registrar Pantallas';
	}
	echo '<hr /><h2>'.$edicionOregistro.$paraUsuario.'</h2>';
	verPantallasregistro($usuario, $pantalla);
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
echo "<tr><th>Código Pantalla "._NOMBRE_."</th><th>Código "._NOMBRE_."</th><th>Código propietario</th><th>Alquilado desde</th><th>Evento</th><th>Foto</th><th>Accion</th></tr>";
   for($i=0; $i<$num_rows; $i++){
      $codigo_cara_mupi  = mysql_result($result,$i,"codigo_cara_mupi");
      $codigo_mupi = mysql_result($result,$i,"codigo_mupi");
      $codigo = mysql_result($result,$i,"codigo");
      $alquilado_desde  = AnularFechaNula(mysql_result($result,$i,"alquilado_desde"));
      $codigo_evento  = mysql_result($result,$i,"codigo_evento");
      $Foto = mysql_result($result,$i,"Foto");
      $Eliminar = CREAR_LINK_GET("gestionar+pantallas&amp;accion=eliminar&amp;pantalla=".$codigo_cara_mupi,"Eliminar", "Eliminar los datos de esta pantalla");
      $codigo_cara_mupi  = CREAR_LINK_GET("gestionar+pantallas&amp;pantalla=".$codigo_cara_mupi,$codigo_cara_mupi, "Editar los datos de esta pantalla");
      echo "<tr><td>$codigo_cara_mupi</td><td>$codigo_mupi</td><td>$codigo</td><td>$alquilado_desde</td><td>$codigo_evento</td><td>$Foto</td><td>$Eliminar</td></tr>";
   }
   echo "</table><br>";
}
function verPantallasregistro($usuario="", $pantalla="") {
global $form, $database;
$CampoFechaHora = '';
$CampoUsuario = '';
$BotonCancelar = '';
if ($pantalla) {
	$q = "SELECT * FROM ".TBL_MUPI_FACES." WHERE codigo_cara_mupi='$pantalla';";
	$result = $database->query($q);
	$form->setValue("codigo", mysql_result($result,0,"codigo_cara_mupi"));
	$form->setValue("foto", mysql_result($result,0,"foto"));
	$NombreBotonAccion = "Editar";
	$BotonCancelar = '<input type="button" OnClick="window.location=\'./?'._ACC_.'=gestionar+pantallas\'" value="Cancelar">';
	$CampoPantalla = '<input type="hidden" name="codigo" value="'.$pantalla.'">';
	$CampoUsuario = '<tr><td>Cliente:</td><td><input type="text" name="CampoUsuario" style="width: 100%;" maxlength="255" value="' . mysql_result($result,0,"codigo") . '"></td></tr>';
	$CampoFechaHora = '<tr><td>Fecha de registro:</td><td><input type="text" name="hora" style="width: 100%;" maxlength="255" value="' . AnularFechaNula(mysql_result($result,0,"alquilado_desde")). '"></td></tr>';
} else {
	$CampoPantalla = '<tr><td>Código  Pantalla '._NOMBRE_.':</td><td><input type="text" name="codigo" maxlength="100" style="width: 100%;" value="' . $form->value("codigo"). '"></td></tr>';
	$NombreBotonAccion = "Registrar";
}

if ($usuario) {
	$CampoFechaHora = '<tr><td>Fecha de registro:</td><td><input type="text" name="hora" style="width: 100%;" maxlength="255" value="' . date("d-m-Y", time()) . '"></td></tr>';
	$CampoUsuario  = '<input type="hidden" name="CampoUsuario" value="'.$usuario.'">';
}

echo '
<form action="./?'._ACC_.'=gestionar+pantallas" method="POST">
<table>
'.$CampoPantalla.'
'.$CampoUsuario.'
<tr><td>Foto:</td><td><input type="text" name="foto" style="width: 100%;" maxlength="255" value="' . $form->value("foto"). '"></td></tr>
'.$CampoFechaHora.'
</table>
<input type="submit" value="'.$NombreBotonAccion.'">
'.$BotonCancelar.'
<input type="hidden" name="registrar_mupi" value="1">
</form>';
}

function Pantalla_REGISTRAR() {
global $database,$form;
$form->setValue("codigo", $_POST['codigo']);
$form->setValue("foto", $_POST['foto']);
if ( isset($_POST['CampoUsuario'] ) ) {
	$q = "INSERT INTO ".TBL_MUPI_FACES." (codigo_cara_mupi, codigo, foto, alquilado_desde) VALUES ('".$_POST['codigo'] . "', '".$_POST['CampoUsuario'] . "', '" . $_POST['foto'] ."', '" . strtotime($_POST['hora']) ."')  ON DUPLICATE KEY UPDATE codigo_cara_mupi=VALUES(codigo_cara_mupi), codigo=VALUES(codigo), foto=VALUES(foto), alquilado_desde=VALUES(alquilado_desde);";
} else {
	$q = "INSERT INTO ".TBL_MUPI_FACES." (codigo_cara_mupi, foto) VALUES ('".$_POST['codigo'] . "', '" . $_POST['foto'] ."')  ON DUPLICATE KEY UPDATE codigo_cara_mupi=VALUES(codigo_cara_mupi), foto=VALUES(foto);";
}
echo "Registrado ".  $_POST['codigo'];
DEPURAR ($q);
$result = $database->query($q);
}
?>
