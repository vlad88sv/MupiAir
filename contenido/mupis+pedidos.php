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
	if ( $session->isAdmin() ) {
		
		//Nos toca registrar un Pedido
		if ( isset($_POST['registrar_pedidos']) ) {
		Pedidos_REGISTRAR();
		}
		
		//Nos toca elimiinar un pedido
		if ( isset($_GET['eliminar']) && isset($_GET['imagen']) ) {
		global $database;
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
		
	} else { 
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
   
   $q = "SELECT codigo_pedido, codigo, (SELECT nombre from ". TBL_USERS . " AS b WHERE a.codigo = b.codigo) as nombre, catorcena_inicio, catorcena_fin, foto_pantalla, costo , descripcion FROM ".TBL_MUPI_ORDERS." AS a$WHERE;";
   $result = $database->query($q);
   
   if ( !$result ) {
      echo "Error mostrando la información";
      return;
   }
   
   $num_rows = mysql_numrows($result);
   if ( $num_rows == 0 ) {
      echo Mensaje ("¡No hay Pedidos "._NOMBRE_." ingresados!",_M_NOTA);
      return;
   }
   
echo '<table>';
echo "<tr><th>Código Pedido "._NOMBRE_."</th><th>Nombre cliente</th><th>Intervalo de alquiler</th><th>Número de catorcenas</th><th>Foto Pantalla</th><th>Costo</th><th>Descripción</th><th>Acciones</th></tr>";
   for($i=0; $i<$num_rows; $i++){
      $codigo_pedido  = mysql_result($result,$i,"codigo_pedido");
      $codigo =  CREAR_LINK_GET("gestionar+pedidos:".mysql_result($result,$i,"codigo"), mysql_result($result,$i,"nombre"), "Ver los pedidos de este cliente");
      $catorcena_inicio  = AnularFechaNula(mysql_result($result,$i,"catorcena_inicio"));
      $catorcena_fin  = AnularFechaNula(mysql_result($result,$i,"catorcena_fin"));
      $NumeroDeCatorcenas = Contar_catorcenas(mysql_result($result,$i,"catorcena_inicio"), mysql_result($result,$i,"catorcena_fin"));
      $foto_pantalla  = mysql_result($result,$i,"foto_pantalla");
	  if ( $foto_pantalla ) { $foto_pantalla = "<span ".GenerarTooltip(CargarImagenDesdeBD(mysql_result($result,$i,"foto_pantalla"),'200px','200px'))." />". $foto_pantalla."</span>"; }
      $costo = "$". (int)(mysql_result($result,$i,"costo"));
	  $descripcion = (mysql_result($result,$i,"descripcion"));
      $Eliminar = CREAR_LINK_GET("gestionar+pedidos&amp;eliminar=".mysql_result($result,$i,"codigo_pedido")."&amp;imagen=" . mysql_result($result,$i,"foto_pantalla") ,"Eliminar", "Eliminar los datos de este pedido");
      $codigo_pedido  = CREAR_LINK_GET("gestionar+pedidos&amp;pedido=".$codigo_pedido,$codigo_pedido, "Editar los datos de este pedido");
      echo "<tr><td>$codigo_pedido</td><td>$codigo</td><td>$catorcena_inicio al $catorcena_fin</td><td>$NumeroDeCatorcenas</td><td>$foto_pantalla</td><td>$costo</td><td>$descripcion</td><td>$Eliminar</tr>";
   }
   echo "</table><br>";
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
if ( !isset($_POST['ConservarPantalla']) ) {
	/*
		Corroborar si ya tenia una imagen antes, para reutilizar la fila y a la vez
		que la imagen anterior no quede huerfana.
	*/
	$Pre_Id = isset($_POST['ConservarPantalla2']) ? $_POST['ConservarPantalla2'] : 0;
	$idImg = CargarImagenEnBD("foto_pantalla","PEDIDOS", $Pre_Id);
} else {
	$idImg = $_POST['ConservarPantalla'];
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
