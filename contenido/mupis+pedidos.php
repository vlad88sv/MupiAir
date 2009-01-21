<?php
function CONTENIDO_pedidos($usuario, $pedido) {
	global $session, $form;
	echo '<h1>Gestión de pedidos de ' . _NOMBRE_ . '</h1>';
	if ( $session->isAdmin() && isset($_POST['registrar_pedidos']) ) {
	//Nos toca registrar un MUPI
	Pedidos_REGISTRAR();
	}
	echo '<hr /><h2>Sus Pedidos '._NOMBRE_.".</h2>";
	verPedidos($usuario);
	if ( $session->isAdmin() ) {
	$paraUsuario = "";
	if ($usuario) {
		$paraUsuario = " para $usuario";
	}
	if ($pedido) {
		$edicionOregistro = 'Edición del Pedido ' . $pedido;
	} else {
		$edicionOregistro = 'Registrar Pedido';
	}
	echo '<hr /><h2>'.$edicionOregistro.$paraUsuario.'</h2>';
	verPedidosregistro($usuario, $pedido);
	}
}
function verPedidos($usuario="", $pedido=""){
   global $database;
   
   $WHERE = "";
   $num_rows = "";
   if ($usuario) {
    $WHERE = " WHERE codigo='".$usuario."'";
    }
   $q = "SELECT * FROM ".TBL_MUPI_ORDERS."$WHERE;";
   $result = $database->query($q);
   if ( !$result ) {
      echo "Error mostrando la información";
      return;
   }
   $num_rows = mysql_numrows($result);
   if($num_rows == 0){
      echo "¡No hay Pedidos "._NOMBRE_." ingresados!<BR />";
      return;
   }
echo '<table>';
echo "<tr><th>Código Pantalla "._NOMBRE_."</th><th>Código "._NOMBRE_."</th><th>Código propietario</th><th>Alquilado desde</th><th>Evento</th><th>Accion</th></tr>";
   for($i=0; $i<$num_rows; $i++){
      $codigo_cara_mupi  = mysql_result($result,$i,"codigo_cara_mupi");
      $codigo_mupi = mysql_result($result,$i,"codigo_mupi");
      $codigo = mysql_result($result,$i,"codigo");
      $alquilado_desde  = AnularFechaNula(mysql_result($result,$i,"alquilado_desde"));
      $codigo_evento  = mysql_result($result,$i,"codigo_evento");
      $Eliminar = CREAR_LINK_GET("gestionar+pantallas&amp;accion=eliminar&amp;pantalla=".$codigo_cara_mupi,"Eliminar", "Eliminar los datos de esta pantalla");
      $codigo_cara_mupi  = CREAR_LINK_GET("gestionar+pantallas&amp;pantalla=".$codigo_cara_mupi,$codigo_cara_mupi, "Editar los datos de esta pantalla");
      echo "<tr><td>$codigo_cara_mupi</td><td>$codigo_mupi</td><td>$codigo</td><td>$alquilado_desde</td><td>$codigo_evento</td><td>$Eliminar</td></tr>";
   }
   echo "</table><br>";
}
function verPedidosregistro($usuario="", $pedido="") {
global $form, $database;
$CampoFechaHora = '';
$CampoUsuario = '';
$BotonCancelar = '';
$CampoCodigoMUPI = '';
$CampoFoto = '';
if ($pedido) {
	$q = "SELECT * FROM ".TBL_MUPI_FACES." WHERE codigo_cara_mupi='$pedido';";
	$result = $database->query($q);
	$form->setValue("codigo", mysql_result($result,0,"codigo_cara_mupi"));
	$form->setValue("foto", mysql_result($result,0,"foto"));
	$NombreBotonAccion = "Editar";
	$BotonCancelar = '<input type="button" OnClick="window.location=\'./?'._ACC_.'=gestionar+pedidos\'" value="Cancelar">';
	$CampoCodigoMUPI = '<tr><td>Asignar al '._NOMBRE_.' código</td><td><input type="text" name="CampoCodigoMUPI" style="width: 100%;" maxlength="255" value="' . mysql_result($result,0,"codigo_mupi") . '"></td></tr>';
	$CampoPantalla = '<input type="hidden" name="codigo_pantalla_mupi" value="'.$pedido.'">';
	$CampoUsuario = '<tr><td>Cliente:</td><td><input type="text" name="CampoUsuario" style="width: 100%;" maxlength="255" value="' . mysql_result($result,0,"codigo") . '"></td></tr>';
	$CampoFechaHora = '<tr><td>Fecha de registro:</td><td><input type="text" name="hora" style="width: 100%;" maxlength="255" value="' . AnularFechaNula(mysql_result($result,0,"alquilado_desde"), true). '"></td></tr>';
	$CampoFoto = '<tr><td>Foto:</td><td><input type="text" name="foto" style="width: 100%;" maxlength="255" value="' . $form->value("foto"). '"></td></tr>';
} else {
	$NombreBotonAccion = "Registrar";
	$CampoPantalla = '<tr><td>Código  Pantalla '._NOMBRE_.':</td><td><input type="text" name="codigo_pantalla_mupi" maxlength="100" style="width: 100%;" value="' . $form->value("codigo"). '"></td></tr>';
}

if ($usuario) {
	$CampoFechaHora = '<tr><td>Fecha de registro:</td><td><input type="text" name="hora" style="width: 100%;" maxlength="255" value="' . date("d-m-Y", time()) . '"></td></tr>';
	$CampoUsuario  = '<input type="hidden" name="CampoUsuario" value="'.$usuario.'">';
}

echo '
<form action="./?'._ACC_.'=gestionar+pedidos" method="POST">
<table>
'.$CampoPantalla.'
'.$CampoCodigoMUPI.'
'.$CampoUsuario.'
'.$CampoFoto.'
'.$CampoFechaHora.'
</table>
<input type="submit" value="'.$NombreBotonAccion.'">
'.$BotonCancelar.'
<input type="hidden" name="registrar_pedidos" value="1">
</form>';
}

function Pedidos_REGISTRAR() {
global $database,$form;
$form->setValue("codigo_pantalla_mupi", $_POST['codigo_pantalla_mupi']);

if ( isset($_POST['CampoUsuario'] ) ) {
	$CampoCodigoMUPI2 = '';
	$form->setValue("foto", $_POST['foto']);
	if ( isset($_POST['CampoCodigoMUPI']) ) {
		$CampoCodigoMUPI2= $_POST['CampoCodigoMUPI'];
	}
	$q = "INSERT INTO ".TBL_MUPI_FACES." (codigo_cara_mupi, codigo, foto, alquilado_desde,codigo_mupi) VALUES ('".$_POST['codigo_pantalla_mupi'] . "', '".$_POST['CampoUsuario'] . "', '" . $_POST['foto'] ."', '" . strtotime($_POST['hora']) ."', '".$CampoCodigoMUPI2."')  ON DUPLICATE KEY UPDATE codigo_cara_mupi=VALUES(codigo_cara_mupi), codigo=VALUES(codigo), foto=VALUES(foto), alquilado_desde=VALUES(alquilado_desde), codigo_mupi=VALUES(codigo_mupi);";
} else {	
	$q = "INSERT INTO ".TBL_MUPI_FACES." (codigo_cara_mupi) VALUES ('" . $_POST['codigo_pantalla_mupi'] . "')  ON DUPLICATE KEY UPDATE codigo_cara_mupi=VALUES(codigo_cara_mupi);";
}
DEPURAR ($q);
if ( $database->query($q) == 1 ) {
	echo "<blockquote>Exito al registrar ".  $_POST['codigo_pantalla_mupi'].'</blockquote>';
} else {
	echo "<blockquote>Falló el registro de " . $_POST['codigo_pantalla_mupi'].'</blockquote>';
}
}
?>
