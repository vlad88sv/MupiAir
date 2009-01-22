<?php
function CONTENIDO_pedidos($usuario, $pedido) {
	global $session, $form;
	echo '<h1>Gestión de pedidos de ' . _NOMBRE_ . '</h1>';
	echo '<hr /><h2>Sus Pedidos '._NOMBRE_.".</h2>";
	if ( $session->isAdmin() && isset($_POST['registrar_pedidos']) ) {
		//Nos toca registrar un MUPI
		Pedidos_REGISTRAR();
	}
	verPedidos($usuario);
	if ( $session->isAdmin() ) {
	
	if ($usuario) { $paraUsuario = " para $usuario"; } else { $paraUsuario = ""; }
	
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
   if ($usuario) { $WHERE = " WHERE codigo='".$usuario."'"; }
   
   $q = "SELECT * FROM ".TBL_MUPI_ORDERS."$WHERE;";
   $result = $database->query($q);
   
   if ( !$result ) {
      echo "Error mostrando la información";
      return;
   }
   
   $num_rows = mysql_numrows($result);
   if ( $num_rows == 0 ) {
      echo "¡No hay Pedidos "._NOMBRE_." ingresados!<BR />";
      return;
   }
   
echo '<table>';
echo "<tr><th>Código Pedido "._NOMBRE_."</th><th>Código cliente</th><th>Fecha de inicio de alquiler</th><th>Foto Pantalla</th><th>Acciones</th></tr>";
   for($i=0; $i<$num_rows; $i++){
      $codigo_pedido  = mysql_result($result,$i,"codigo_pedido");
      $codigo = mysql_result($result,$i,"codigo");
      $alquilado_desde  = AnularFechaNula(mysql_result($result,$i,"alquilado_desde"));
      $foto_pantalla  = mysql_result($result,$i,"foto_pantalla");
      $Eliminar = CREAR_LINK_GET("gestionar+pedidos&amp;accion=eliminar&amp;pedido=".$codigo_pedido,"Eliminar", "Eliminar los datos de este pedido");
      $codigo_cara_mupi  = CREAR_LINK_GET("gestionar+pedidos&amp;pedido=".$codigo_pedido,$codigo_pedido, "Editar los datos de este pedido");
      echo "<tr><td>$codigo_pedido</td><td>$codigo</td><td>$alquilado_desde</td><td>$foto_pantalla</td><td>$Eliminar</tr>";
   }
   echo "</table><br>";
}
function verPedidosregistro($usuario="", $pedido="") {
global $form, $database;
$CampoUsuario = '';
$CampoFechaHora = '';
$CampoPantalla = '';
$BotonCancelar = '';
$CampoFoto = '';
$alquilado_desde = '';
$foto_pantalla = '';

if ($pedido) {
	$q = "SELECT * FROM ".TBL_MUPI_ORDERS." WHERE codigo_pedido='$pedido';";
	$result = $database->query($q);
	$usuario = mysql_result($result,0,"codigo");
	$alquilado_desde = mysql_result($result,0,"alquilado_desde");
	$foto_pantalla = mysql_result($result,0,"foto_pantalla");
	$NombreBotonAccion = "Editar";
	$BotonCancelar = '<input type="button" OnClick="window.location=\'./?'._ACC_.'=gestionar+pedidos\'" value="Cancelar">';
} else {
	$q = "SELECT LAST_INSERT_ID() FROM ".TBL_MUPI_ORDERS; 
	$pedido =mysql_num_rows($database->query($q)) + 1;
	$NombreBotonAccion = "Registrar";
}
	$CampoCodigoPedido = '<tr><td width="25%">Código de pedido</td><td><input disabled type="text" name="codigo_pedido" maxlength="255" value="' . $pedido . '"></td></tr>';
	$CampoUsuario = '<tr><td>Cliente:</td><td><input type="text" name="codigo" maxlength="255" value="' . $usuario . '"></td></tr>';
	$CampoFechaHora = '<tr><td>Fecha de inicio de alquiler:</td><td><input type="text" name="alquilado_desde" maxlength="255" value="' . AnularFechaNula($alquilado_desde,true). '"></td></tr>';
	$CampoPantalla = '<tr><td>Foto de pantalla:</td><td><input type="text" name="foto_pantalla" maxlength="255" value="' . $foto_pantalla . '"></td></tr>';

echo '
<form action="./?'._ACC_.'=gestionar+pedidos" method="POST">
<table>
'.$CampoCodigoPedido.'
'.$CampoUsuario.'
'.$CampoFechaHora.'
'.$CampoPantalla.'
</table>
<input type="submit" value="'.$NombreBotonAccion.'">
'.$BotonCancelar.'
<input type="hidden" name="registrar_pedidos" value="1">
</form>';
}
 
function Pedidos_REGISTRAR() {
global $database,$form;
$q = "INSERT INTO ".TBL_MUPI_ORDERS." ( codigo, alquilado_desde, foto_pantalla ) VALUES ('" . $_POST['codigo'] . "', '". strtotime($_POST['alquilado_desde']). "', '". $_POST['foto_pantalla']."')  ON DUPLICATE KEY UPDATE codigo=VALUES(codigo), alquilado_desde=VALUES(alquilado_desde), foto_pantalla=VALUES(foto_pantalla);";
DEPURAR ($q);
if ( $database->query($q) == 1 ) {
	echo "<blockquote>Exito al registrar el pedido de ".  $_POST['codigo'].'</blockquote>';
} else {
	echo "<blockquote>Falló el registro el pedido de " . $_POST['codigo'].'</blockquote>';
}
}
?>
