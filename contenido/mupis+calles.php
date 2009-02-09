<?php
function CONTENIDO_calles($usuario, $calle) {
	global $session, $form, $database;
	if ( !$session->isAdmin() ) { exit ("Lo siento, Ud. no puede acceder a esta área del sistema"); }
	echo '<h1>Gestión de calles de ' . _NOMBRE_ . '</h1>';
	if ( $usuario ) {
		if ( !$database->codigoTaken($usuario) ) {
			echo "<hr /><h2>No existe el Cliente o Usuario $usuario</h2>";
			return;
		}
	}
	echo '<hr /><h2>Sus calles '._NOMBRE_.".</h2>";
	//Nos toca registrar un MUPI
	if ( isset($_POST['registrar_calles']) ) {
		calles_REGISTRAR();
	}

	//Nos toca eliminar un MUPI
	if ( isset($_GET['eliminar']) ) {
	global $database;
	// Eliminamos la pantalla
	$q = "DELETE FROM " . TBL_STREETS . " WHERE codigo_calle='" . $_GET['eliminar'] . "';";
	$result = $database->query($q);
	if ( $result ) { echo "Calle eliminada<br />"; }
	}

	vercalles($usuario);
	
	if ($calle) {
		$edicionOregistro = 'Edición del calle ' . $calle;
	} else {
		$edicionOregistro = 'Registrar calle';
	}
	
	echo '<hr /><h2>'.$edicionOregistro.'</h2>';
	
	vercallesregistro($usuario, $calle);
}

function vercalles($usuario="", $calle=""){
   global $database;
   
//  $WHERE = "";
   $num_rows = "";
//   if ($usuario) { $WHERE = " WHERE codigo='".$usuario."'"; }
   
   $q = "SELECT * FROM ".TBL_STREETS;
   $result = $database->query($q);
   
   if ( !$result ) {
      echo "Error mostrando la información";
      return;
   }
   
   $num_rows = mysql_numrows($result);
   if ( $num_rows == 0 ) {
      echo Mensaje ("¡No hay calles "._NOMBRE_." ingresadas", _M_NOTA);
      return;
   }
   
echo "<table>";
echo "<tr><th>Código calle "._NOMBRE_."</th><th>Ubicación</th><th>Impactos</th><th>Acciones</th></tr>";
   for($i=0; $i<$num_rows; $i++){
      $codigo_calle  = mysql_result($result,$i,"codigo_calle");
      $ubicacion =  mysql_result($result,$i,"ubicacion");
      $impactos =  mysql_result($result,$i,"impactos");
      $Eliminar = CREAR_LINK_GET("gestionar+calles&amp;eliminar=".mysql_result($result,$i,"codigo_calle"),"Eliminar", "Eliminar los datos de esta calle");
      $codigo_calle  = CREAR_LINK_GET("gestionar+calles&amp;calle=".$codigo_calle,$codigo_calle, "Editar los datos de esta calle");
      echo "<tr><td>$codigo_calle</td><td>$ubicacion</td><td>$impactos</td><td>$Eliminar</tr>";
   }
   echo "</table><br>";
}

function vercallesregistro($usuario="", $calle="") {
global $form, $database;
$CampoCodigocalle = '';
$BotonCancelar = '';
$codigo_calle = '';
$ubicacion = '';

if ($calle) {
	$q = "SELECT * FROM ".TBL_STREETS." WHERE codigo_calle='$calle';";
	$result = $database->query($q);
	$num_rows = mysql_numrows($result);
	if ( $num_rows == 0 ) {
		echo "¡No hay calles "._NOMBRE_." con ese código!<br />";
		return;
	}
	$codigo_calle = mysql_result($result,0,"codigo_calle");
	
	$CampoCodigocalle = '<input type="hidden" name="codigo_calle" value="'.$codigo_calle.'">';
	$ubicacion = mysql_result($result,0,"ubicacion");
	$impactos = mysql_result($result,0,"impactos");
	$NombreBotonAccion = "Editar";
	$BotonCancelar = '<input type="button" OnClick="window.location=\'./?'._ACC_.'=gestionar+calles\'" value="Cancelar">';
} else {
	$q = "SELECT LAST_INSERT_ID() FROM ".TBL_STREETS; 
	$codigo_calle = mysql_num_rows($database->query($q)) + 1;
	$CampoCodigocalle = '<input type="hidden" name="codigo_calle" value="0">';
	$impactos = 0;
	$NombreBotonAccion = "Registrar";
}
	$CampoCodigocalle2 = '<tr><td width="25%">Código de calle</td><td><b>'. $codigo_calle. '</b></td></tr>';
	$CampoUbicacion = '<tr><td>Ubicación:</td><td><input type="text" name="ubicacion" maxlength="255" value="' . $ubicacion . '"></td></tr>';
	$CampoImpactos = '<tr><td>Número de impactos:</td><td><input type="text" name="impactos" maxlength="255" value="' . $impactos . '"></td></tr>';

echo '
<form action="./?'._ACC_.'=gestionar+calles" method="POST">
<table>
'.$CampoCodigocalle.'
'.$CampoCodigocalle2.'
'.$CampoUbicacion.'
'.$CampoImpactos.'
</table>
<input type="submit" value="'.$NombreBotonAccion.'">
'.$BotonCancelar.'
<input type="hidden" name="registrar_calles" value="1">
</form>';
}
 
function calles_REGISTRAR() {
global $database,$form;

$q = "INSERT INTO ".TBL_STREETS." (codigo_calle, ubicacion, impactos ) VALUES ('".$_POST['codigo_calle']."', '" . $_POST['ubicacion']. "', '" . $_POST['impactos']. "')  ON DUPLICATE KEY UPDATE ubicacion=VALUES(ubicacion), impactos=VALUES(impactos);";
DEPURAR ($q);
if ( $database->query($q) == 1 ) {
	echo Mensaje("Exito al registrar calle de ".  $_POST['ubicacion'],_M_INFO);
} else {
	echo Mensaje("Falló el registro el calle de " . $_POST['ubicacion'], _M_ERROR);
}
}
?>

