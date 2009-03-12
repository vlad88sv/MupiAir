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
		
		//Nos toca registrar un Pedido
		if ( isset($_POST['registrar_pedidos']) ) {
		Pedidos_REGISTRAR();
		}
		
		//Nos toca elimiinar un pedido
		if ( isset($_GET['eliminar']) && isset($_GET['imagen']) ) {
		// Eliminamos la pantalla
		$q = "DELETE FROM " . TBL_MUPI_ORDERS . " WHERE codigo_pedido=" . $_GET['eliminar'] . ";";
		$result = $database->query($q);
		if ( $result ) { echo Mensaje ("Pedido eliminado",_M_INFO); } else { echo Mensaje ("Pedido no pudo ser eliminado",_M_ERROR); }
		
		// Eliminamos cualquier imagen que estuviera asociada a esa pantalla
		if ($_GET['imagen']) {
		$q = "DELETE FROM " . TBL_IMG . " WHERE id_imagen=" . $_GET['imagen'] . ";";
		$result = $database->query($q);
		if ( $result ) { echo "Imagen asociada al pedido eliminada<br />"; } 
		}
		
		}
	echo "Mostrar solo pedidos que se encuentren en la catorcena: " . $database->Combobox_CatorcenasConPresencia("cmbFiltroCatorcena",$usuario) . "<input type='button' onclick='$(\"#tabla_pedidos\").load(\"contenido/mupis+pedidos+dinamico.php?usuario=$usuario&amp;catorcena=\"+$(\"#cmbFiltroCatorcena\").val())' value='filtrar' >". "<input type='button' onclick='$(\"#tabla_pedidos\").load(\"contenido/mupis+pedidos+dinamico.php?usuario=$usuario\")' value='Mostrar todos los pedidos' >";
	echo "<div id='tabla_pedidos'></div>";
	if ($usuario) { $paraUsuario = " para $usuario"; } else { $paraUsuario = ""; }
	
	if ($pedido) {
		$edicionOregistro = 'Edición del Pedido ' . $pedido;
	} else {
		$edicionOregistro = 'Registrar Pedido';
	}
	
	echo '<hr /><h2>'.$edicionOregistro.$paraUsuario.'</h2>';
	
	verPedidosregistro($usuario, $pedido);
}

function verPedidosregistro($usuario="", $pedido="") {
global $form, $database;
$CampoCodigoPedido = 0;
$CampoUsuario = '';
$Campocatorcena_inicio = '';
$Campocatorcena_fin = '';
$CampoPantalla = '';
$CampoConservarPantalla = '';
$BotonCancelar = '';
$CampoFoto = '';
$costo='';
$foto_pantalla = '';
$OnChangePantalla = '';
$CampoConservarPantalla2 = '';
$descripcion = '';
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
	if ( $foto_pantalla ) {
		$CampoConservarPantalla = '<tr><td>Conservar Arte Digital con Id.'.$foto_pantalla.'</td></td><td><span id="CampoConservarPantalla"><input type="checkbox" name="ConservarPantalla" value="'.$foto_pantalla.'" checked="checked"></span></td></tr>';
		$CampoConservarPantalla2 = '<input type="hidden" name="ConservarPantalla2" value="'.$foto_pantalla.'">';	
		$OnChangePantalla = 'onchange="document.getElementById(\'CampoConservarPantalla\').innerHTML=\'Se reemplazará la imagen actual con la seleccionada\'"';
	}
	$costo = mysql_result($result,0,"costo");
	$descripcion = mysql_result($result,0,"descripcion");
	$CampoCodigoPedido = '<input type="hidden" name="codigo_pedido" value="'.$pedido.'">';	
	$NombreBotonAccion = "Editar";
	$BotonCancelar = '<input type="button" OnClick="window.location=\'./?'._ACC_.'=gestionar+pedidos\'" value="Cancelar">';
} else {
	$q = "SELECT LAST_INSERT_ID() FROM ".TBL_MUPI_ORDERS; 
	$pedido = mysql_num_rows($database->query($q)) + 1;
	$catorcena_inicio = Obtener_catorcena_cercana();
	$catorcena_fin = $catorcena_inicio;
	$CampoCodigoPedido = '<input type="hidden" name="codigo_pedido" value="0">';
	$NombreBotonAccion = "Registrar";
}
	$CampoCodigoPedido2 = '<tr><td width="25%">Código de pedido</td><td><b>'. $pedido. '</b></td></tr>';
	$CampoUsuario = '<tr><td>Cliente:</td><td>'.$database->Combobox_usuarios("codigo",$usuario) . '</td></tr>';
	$Campocatorcena_inicio = '<tr><td>Inicio del contrato:</td><td>'. Combobox_catorcenas("catorcena_inicio", $catorcena_inicio, 26, _F_INICIOS). '</td></tr>';
	$Campocatorcena_fin = '<tr><td>Fin del contrato:</td><td>'. Combobox_catorcenas("catorcena_fin", $catorcena_fin, 26, _F_FINES). '</td></tr>';
	$CampoPantalla = '<tr><td>Arte digital:</td><td><input type="file" name="foto_pantalla" '.$OnChangePantalla.'></td></tr>';
	$CampoCosto ='<tr><td>Costo:</td><td><input type="text" name="costo" maxlength="100" value="' . $costo. '"></td></tr>';
	$CampoDescripcion ='<tr><td>Descripción:</td><td><input type="text" name="descripcion" maxlength="100" value="' . $descripcion. '"></td></tr>';
echo '
<form action="./?'._ACC_.'=gestionar+pedidos" enctype="multipart/form-data" method="POST">
<table>
'.$CampoCodigoPedido.'
'.$CampoCodigoPedido2.'
'.$CampoUsuario.'
'.$Campocatorcena_inicio.'
'.$Campocatorcena_fin.'
'.$CampoConservarPantalla.'
'.$CampoConservarPantalla2.'
'.$CampoPantalla.'
'.$CampoCosto.'
'.$CampoDescripcion.'
</table>
<input type="submit" value="'.$NombreBotonAccion.'">
'.$BotonCancelar.'
<input type="hidden" name="registrar_pedidos" value="1">
</form>';
}
 
function Pedidos_REGISTRAR() {
global $database,$form;
	//print_ar($_POST);
	//print_ar($_FILES);
if ( !$_FILES['foto_pantalla']['error'] ) {
	$Pre_Id = isset($_POST['ConservarPantalla2']) ? $_POST['ConservarPantalla2'] : 0;
	$idImg = CargarImagenEnBD("foto_pantalla","PEDIDOS", $Pre_Id);
} else {
	
	if ( isset ($_POST['ConservarPantalla']) ){
		 $idImg = $_POST['ConservarPantalla2'];
	 } else {
		 $idImg = 0;
	 }
}
$q = "INSERT INTO ".TBL_MUPI_ORDERS." ( codigo_pedido, codigo, catorcena_inicio, catorcena_fin,  foto_pantalla, costo, descripcion ) VALUES (" . $_POST['codigo_pedido'] . ", '" . $_POST['codigo'] . "', '". $_POST['catorcena_inicio']. "', '". $_POST['catorcena_fin']. "', '". $idImg."', '". $_POST['costo']."', '". $_POST['descripcion']."')  ON DUPLICATE KEY UPDATE codigo=VALUES(codigo), catorcena_inicio=VALUES(catorcena_inicio), catorcena_fin=VALUES(catorcena_fin), foto_pantalla=VALUES(foto_pantalla), costo=VALUES(costo), descripcion=VALUES(descripcion);";
DEPURAR ($q);
//print_ar($_POST);
if ( $database->query($q) == 1 ) {
	echo Mensaje ("Exito al registrar el pedido de ".  $_POST['codigo'], _M_INFO);
} else {
	echo Mensaje ("Falló el registro el pedido de " . $_POST['codigo'], _M_ERROR);
}
}
?>
