<?php
function CONTENIDO_pantallas($usuario, $pantalla) {
	global $session, $form;
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
   $num_rows = "";
   if ($usuario) {
    $WHERE = " WHERE codigo='".$usuario."'";
    }
   $q = "SELECT * FROM ".TBL_MUPI_FACES."$WHERE;";
   $result = $database->query($q);
   if ( !$result ) {
      echo "Error mostrando la información";
      return;
   }
   $num_rows = mysql_numrows($result);
   if($num_rows == 0){
      echo "¡No hay Pantallas "._NOMBRE_." ingresadas!<BR />";
      return;
   }
echo '<table>';
echo "<tr><th>Código Pantalla "._NOMBRE_."</th><th>Código "._NOMBRE_."</th><th>Código pedido</th><th>Foto real</th><th>Evento</th><th>Acción</th></tr>";
   for($i=0; $i<$num_rows; $i++){
      $codigo_pantalla_mupi  = mysql_result($result,$i,"codigo_pantalla_mupi");
      $codigo_mupi = CREAR_LINK_GET("gestionar+mupis:".mysql_result($result,$i,"codigo_mupi"), mysql_result($result,$i,"codigo_mupi"), "Ver y/o editar los datos de este "._NOMBRE_);
      $codigo_pedido = mysql_result($result,$i,"codigo_pedido");
      $codigo_evento  = mysql_result($result,$i,"codigo_evento");
      $foto_real  = mysql_result($result,$i,"foto_real");
      $Eliminar = CREAR_LINK_GET("gestionar+pantallas&amp;accion=eliminar&amp;pantalla=".$codigo_pantalla_mupi,"Eliminar", "Eliminar los datos de esta pantalla");
      $codigo_pantalla_mupi  = CREAR_LINK_GET("gestionar+pantallas&amp;pantalla=".$codigo_pantalla_mupi,$codigo_pantalla_mupi, "Editar los datos de esta pantalla");
      echo "<tr><td>$codigo_pantalla_mupi</td><td>$codigo_mupi</td><td>$codigo_pedido</td><td>$foto_real</td><td>$codigo_evento</td><td>$Eliminar</td></tr>";
   }
   echo "</table><br>";
}
function verPantallasregistro($usuario="", $pantalla="") {
global $database;
$BotonCancelar = '';
$CampoCodigoMUPI = '';
$CampoPantalla = '';

$codigo_mupi ='';
$codigo_pedido = '';
$foto_real = '';

if ($pantalla) {
	$q = "SELECT * FROM ".TBL_MUPI_FACES." WHERE codigo_pantalla_mupi='$pantalla';";
	$result = $database->query($q);
	
	$CampoPantalla = '<input type="hidden" name="codigo_pantalla_mupi" value="'.$pantalla.'">';	
	$codigo_mupi =  mysql_result($result,0,"codigo_mupi") ;
	$codigo_pedido = mysql_result($result,0,"codigo_pedido");
	$foto_real = mysql_result($result,0,"foto_real");
	
	$NombreBotonAccion = "Editar";
	$BotonCancelar = '<input type="button" OnClick="window.location=\'./?'._ACC_.'=gestionar+pantallas\'" value="Cancelar">';
} else {	
	$CampoPantalla = '<tr><td>Código de Pantalla '._NOMBRE_.'</td><td><input type="text" name="codigo_pantalla_mupi" style="width: 100%;" maxlength="255" value=""></td></tr>';

	$NombreBotonAccion = "Registrar";
}
	$CampoCodigoMUPI = '<tr><td>Enlazar al '._NOMBRE_.' código</td><td><input type="text" name="codigo_mupi" style="width: 100%;" maxlength="255" value="' . $codigo_mupi	. '"></td></tr>';
	$CampoCodigoPedido = '<tr><td>Enlazar al pedido '._NOMBRE_.' código</td><td><input type="text" name="codigo_pedido" style="width: 100%;" maxlength="255" value="' . $codigo_pedido . '"></td></tr>';
	$CampoFotoReal = '<tr><td>Agregar Foto real </td><td><input type="text" name="foto_real" style="width: 100%;" maxlength="255" value="' . $foto_real . '"></td></tr>';

/*
if ($usuario) {
	$CampoUsuario  = '<input type="hidden" name="CampoUsuario" value="'.$usuario.'">';
}
*/

echo '
<form action="./?'._ACC_.'=gestionar+pantallas" method="POST">
<table>
'.$CampoPantalla.'
'.$CampoCodigoMUPI.'
'.$CampoCodigoPedido.'
'.$CampoFotoReal.'
</table>
<input type="submit" value="'.$NombreBotonAccion.'">
'.$BotonCancelar.'
<input type="hidden" name="registrar_mupi" value="1">
</form>';
}

function Pantalla_REGISTRAR() {
global $database;
$q = "INSERT INTO ".TBL_MUPI_FACES." (codigo_pantalla_mupi, codigo_mupi, codigo_pedido, foto_real) VALUES ('" . $_POST['codigo_pantalla_mupi'] . "', '" . $_POST['codigo_mupi']  . "', '" . $_POST['codigo_pedido']  . "', '" . $_POST['foto_real']  .  "')  ON DUPLICATE KEY UPDATE codigo_pantalla_mupi=VALUES(codigo_pantalla_mupi), codigo_mupi=VALUES(codigo_mupi), codigo_pedido=VALUES(codigo_pedido), foto_real=VALUES(foto_real);";
DEPURAR ($q);
if ( $database->query($q) == 1 ) {
	echo "<blockquote>Exito al registrar ".  $_POST['codigo_pantalla_mupi'].'</blockquote>';
} else {
	echo "<blockquote>Falló el registro de " . $_POST['codigo_pantalla_mupi'].'</blockquote>';
}
}
?>
