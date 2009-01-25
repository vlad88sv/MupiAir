<?php
function CONTENIDO_pedidos($usuario, $pedido) {
	global $session, $form, $database;
	echo '<h1>Gestión de pedidos de ' . _NOMBRE_ . '</h1>';
	if ( $usuario ) {
		if ( !$database->codigoTaken($usuario) ) {
			echo "<hr /><h2>No existe el Cliente o Usuario $usuario</h2>";
			return;
		}
	}
	echo '<hr /><h2>Sus Pedidos '._NOMBRE_.".</h2>";
	if ( $session->isAdmin() && isset($_POST['registrar_pedidos']) ) {
		//Nos toca registrar un MUPI
		Pedidos_REGISTRAR();
	}
	
	if ( !$session->isAdmin() ) { 
		//Solo puede ver sus propios pedidos.
		$usuario = $session->codigo;
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
      $codigo =  CREAR_LINK_GET("gestionar+pedidos:".mysql_result($result,$i,"codigo"), mysql_result($result,$i,"codigo"), "Ver los pedidos de este cliente");
      $catorcena  = AnularFechaNula(mysql_result($result,$i,"catorcena"));
      $foto_pantalla  = mysql_result($result,$i,"foto_pantalla");
      $Eliminar = CREAR_LINK_GET("gestionar+pedidos&amp;accion=eliminar&amp;pedido=".$codigo_pedido,"Eliminar", "Eliminar los datos de este pedido");
      $codigo_pedido  = CREAR_LINK_GET("gestionar+pedidos&amp;pedido=".$codigo_pedido,$codigo_pedido, "Editar los datos de este pedido");
      echo "<tr><td>$codigo_pedido</td><td>$codigo</td><td>$catorcena</td><td>$foto_pantalla</td><td>$Eliminar</tr>";
   }
   echo "</table><br>";
}
function verPedidosregistro($usuario="", $pedido="") {
global $form, $database;
$CampoCodigoPedido = '';
$CampoUsuario = '';
$CampoFechaHora = '';
$CampoPantalla = '';
$BotonCancelar = '';
$CampoFoto = '';
$catorcena = '';
$foto_pantalla = '';

if ($pedido) {
	$q = "SELECT * FROM ".TBL_MUPI_ORDERS." WHERE codigo_pedido='$pedido';";
	$result = $database->query($q);
	$num_rows = mysql_numrows($result);
	if ( $num_rows == 0 ) {
		echo "¡No hay Pedido "._NOMBRE_." con este código ingresado!<BR />";
		return;
	}
	$usuario = mysql_result($result,0,"codigo");
	$catorcena = mysql_result($result,0,"catorcena");
	$foto_pantalla = mysql_result($result,0,"foto_pantalla");
	
	$CampoCodigoPedido = '<input type="hidden" name="codigo_pedido" value="'.$pedido.'">';
	
	
	$NombreBotonAccion = "Editar";
	$BotonCancelar = '<input type="button" OnClick="window.location=\'./?'._ACC_.'=gestionar+pedidos\'" value="Cancelar">';
} else {
	$q = "SELECT LAST_INSERT_ID() FROM ".TBL_MUPI_ORDERS; 
	$pedido = mysql_num_rows($database->query($q)) + 1;
	
	$NombreBotonAccion = "Registrar";
}
	$CampoCodigoPedido2 = '<tr><td width="25%">Código de pedido</td><td><b>'. $pedido. '</b></td></tr>';
	$CampoUsuario = '<tr><td>Cliente:</td><td><input type="text" name="codigo" maxlength="255" value="' . $usuario . '"></td></tr>';
	$CampoFechaHora = '<tr><td>Fecha de inicio de alquiler:</td><td><input type="text" name="catorcena" maxlength="255" value="' . AnularFechaNula($catorcena,true). '"></td></tr>';
	$CampoPantalla = '<tr><td>Foto de pantalla:</td><td><input type="text" name="foto_pantalla" maxlength="255" value="' . $foto_pantalla . '"></td></tr>';

echo '
<form action="./?'._ACC_.'=gestionar+pedidos" method="POST">
<table>
'.$CampoCodigoPedido.'
'.$CampoCodigoPedido2.'
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

if ( isset($_POST['codigo_pedido'] ) ) {
	$extra1 = 'codigo_pedido, ';
	$extra2 = "'".$_POST['codigo_pedido']."', ";
} else {
	$extra1 = '';
	$extra2 = '';
}
$q = "INSERT INTO ".TBL_MUPI_ORDERS." ( ".$extra1." codigo, catorcena, foto_pantalla ) VALUES (".$extra2."'" . $_POST['codigo'] . "', '". strtotime($_POST['catorcena']). "', '". $_POST['foto_pantalla']."')  ON DUPLICATE KEY UPDATE codigo=VALUES(codigo), catorcena=VALUES(catorcena), foto_pantalla=VALUES(foto_pantalla);";
DEPURAR ($q);
if ( $database->query($q) == 1 ) {
	echo "<blockquote>Exito al registrar el pedido de ".  $_POST['codigo'].'</blockquote>';
} else {
	echo "<blockquote>Falló el registro el pedido de " . $_POST['codigo'].'</blockquote>';
}
}
?>
