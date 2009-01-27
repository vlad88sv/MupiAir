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
   
   $q = "SELECT codigo_pedido, codigo, (SELECT nombre from ". TBL_USERS . " AS b WHERE a.codigo = b.codigo) as nombre, catorcena_inicio, catorcena_fin, foto_pantalla FROM ".TBL_MUPI_ORDERS." AS a$WHERE;";
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
echo "<tr><th>Código Pedido "._NOMBRE_."</th><th>Nombre cliente</th><th>Intervalo de alquiler</th><th>Número de catorcenas</th><th>Foto Pantalla</th><th>Acciones</th></tr>";
   for($i=0; $i<$num_rows; $i++){
      $codigo_pedido  = mysql_result($result,$i,"codigo_pedido");
      $codigo =  CREAR_LINK_GET("gestionar+pedidos:".mysql_result($result,$i,"codigo"), mysql_result($result,$i,"nombre"), "Ver los pedidos de este cliente");
      $catorcena_inicio  = AnularFechaNula(mysql_result($result,$i,"catorcena_inicio"));
      $catorcena_fin  = AnularFechaNula(mysql_result($result,$i,"catorcena_fin"));
      $NumeroDeCatorcenas = Contar_catorcenas(mysql_result($result,$i,"catorcena_inicio"), mysql_result($result,$i,"catorcena_fin"));
      $foto_pantalla  = mysql_result($result,$i,"foto_pantalla");
      $Eliminar = CREAR_LINK_GET("gestionar+pedidos&amp;accion=eliminar&amp;pedido=".$codigo_pedido,"Eliminar", "Eliminar los datos de este pedido");
      $codigo_pedido  = CREAR_LINK_GET("gestionar+pedidos&amp;pedido=".$codigo_pedido,$codigo_pedido, "Editar los datos de este pedido");
      echo "<tr><td>$codigo_pedido</td><td>$codigo</td><td>$catorcena_inicio al $catorcena_fin</td><td>$NumeroDeCatorcenas</td><td>$foto_pantalla</td><td>$Eliminar</tr>";
   }
   echo "</table><br>";
}
function verPedidosregistro($usuario="", $pedido="") {
global $form, $database;
$CampoCodigoPedido = '';
$CampoUsuario = '';
$Campocatorcena_inicio = '';
$Campocatorcena_fin = '';
$CampoPantalla = '';
$BotonCancelar = '';
$CampoFoto = '';

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
	$catorcena_inicio = mysql_result($result,0,"catorcena_inicio");
	$catorcena_fin = mysql_result($result,0,"catorcena_fin");
	$foto_pantalla = mysql_result($result,0,"foto_pantalla");
	
	$CampoCodigoPedido = '<input type="hidden" name="codigo_pedido" value="'.$pedido.'">';
	
	
	$NombreBotonAccion = "Editar";
	$BotonCancelar = '<input type="button" OnClick="window.location=\'./?'._ACC_.'=gestionar+pedidos\'" value="Cancelar">';
} else {
	$q = "SELECT LAST_INSERT_ID() FROM ".TBL_MUPI_ORDERS; 
	$pedido = mysql_num_rows($database->query($q)) + 1;
	$catorcena_inicio = Obtener_catorcena_cercana();
	$catorcena_fin = $catorcena_inicio;
	$NombreBotonAccion = "Registrar";
}
	$CampoCodigoPedido2 = '<tr><td width="25%">Código de pedido</td><td><b>'. $pedido. '</b></td></tr>';
	$CampoUsuario = '<tr><td>Cliente:</td><td>'.$database->Combobox_usuarios("codigo",$usuario) . '</td></tr>';
	$Campocatorcena_inicio = '<tr><td>Inicio del contrato:</td><td>'. Combobox_catorcenas("catorcena_inicio", $catorcena_inicio, 26, _F_INICIOS). '</td></tr>';
	$Campocatorcena_fin = '<tr><td>Fin del contrato:</td><td>'. Combobox_catorcenas("catorcena_fin", $catorcena_fin, 26, _F_FINES). '</td></tr>';
	$CampoPantalla = '<tr><td>Foto de pantalla:</td><td><input type="text" name="foto_pantalla" maxlength="255" value="' . $foto_pantalla . '"></td></tr>';

echo '
<form action="./?'._ACC_.'=gestionar+pedidos" method="POST">
<table>
'.$CampoCodigoPedido.'
'.$CampoCodigoPedido2.'
'.$CampoUsuario.'
'.$Campocatorcena_inicio.'
'.$Campocatorcena_fin.'
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
$q = "INSERT INTO ".TBL_MUPI_ORDERS." ( ".$extra1." codigo, catorcena_inicio, catorcena_fin,  foto_pantalla ) VALUES (".$extra2."'" . $_POST['codigo'] . "', '". $_POST['catorcena_inicio']. "', '". $_POST['catorcena_fin']. "', '". $_POST['foto_pantalla']."')  ON DUPLICATE KEY UPDATE codigo=VALUES(codigo), catorcena_inicio=VALUES(catorcena_inicio), catorcena_fin=VALUES(catorcena_fin), foto_pantalla=VALUES(foto_pantalla);";
DEPURAR ($q);
if ( $database->query($q) == 1 ) {
	echo "<blockquote>Exito al registrar el pedido de ".  $_POST['codigo'].'</blockquote>';
} else {
	echo "<blockquote>Falló el registro el pedido de " . $_POST['codigo'].'</blockquote>';
}
}
?>
