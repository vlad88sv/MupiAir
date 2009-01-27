<?php
$Catorcena = NULL;
function CONTENIDO_pantallas($usuario, $pantalla) {
	global $session, $form, $Catorcena;
	echo '<h1>Gestión de pantallas de ' . _NOMBRE_ . '</h1>';
	if ( $session->isAdmin() && isset($_POST['registrar_mupi']) ) {
	//Nos toca registrar un MUPI
	Pantalla_REGISTRAR();
	}
	if ( isset($_POST['ver_catorcena']) ) {
		$BotonCancelar = '<input type="button" OnClick="window.location=\'./?'._ACC_.'=gestionar+pantallas\'" value="Volver a catorcena actual">';
		$Catorcena = $_POST['ver_catorcena'];
	} else {
		$BotonCancelar = '';
		$Catorcena = Obtener_catorcena_cercana();
	}
	echo '<hr /><h2>Pantallas '._NOMBRE_." en la catorcena de ".date("d/m/Y",$Catorcena)."</h2>";
	echo '<form action="./?'._ACC_.'=gestionar+pantallas" method="POST">';
	echo "Viendo pantallas "._NOMBRE_." de la catorcena " . Combobox_catorcenas("ver_catorcena", $Catorcena) ;
	echo '<input type="submit" value="Cambiar">';
	echo $BotonCancelar;
	echo '</form>';
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
   global $database, $Catorcena;
   
   $WHERE = "";
   $num_rows = "";
   if ($usuario) {
    $WHERE = " WHERE codigo='".$usuario."'";
    }
   $q="SELECT * FROM ".TBL_MUPI_FACES." WHERE catorcena = $Catorcena;";

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
      $codigo_mupi = CREAR_LINK_GET("gestionar+mupis&amp;mupi=".mysql_result($result,$i,"codigo_mupi"), mysql_result($result,$i,"codigo_mupi"), "Ver y/o editar los datos de este "._NOMBRE_);
      $codigo_pedido = CREAR_LINK_GET("gestionar+pedidos&amp;pedido=" . mysql_result($result,$i,"codigo_pedido"), mysql_result($result,$i,"codigo_pedido"), "Ver a quien pertenece este pedido");
      $codigo_evento  = mysql_result($result,$i,"codigo_evento");
      $foto_real  = mysql_result($result,$i,"foto_real");
      $Eliminar = CREAR_LINK_GET("gestionar+pantallas&amp;accion=eliminar&amp;pantalla=".$codigo_pantalla_mupi,"Eliminar", "Eliminar los datos de esta pantalla");
      $codigo_pantalla_mupi  = CREAR_LINK_GET("gestionar+pantallas&amp;id=".mysql_result($result,$i,"Id"),$codigo_pantalla_mupi, "Editar los datos de esta pantalla");
      echo "<tr><td>$codigo_pantalla_mupi</td><td>$codigo_mupi</td><td>$codigo_pedido</td><td>$foto_real</td><td>$codigo_evento</td><td>$Eliminar</td></tr>";
   }
   echo "</table><br>";
}
function verPantallasregistro($usuario="", $id="") {
global $database, $Catorcena;
$BotonCancelar = '';
$CampoCodigoMUPI = '';
$Pantalla = '';
$codigo_mupi ='';
$codigo_pedido = '';
$foto_real = '';
$CampoId = '';
$CampoCatorcena = '';

if ($id) {
	$q = "SELECT * FROM ".TBL_MUPI_FACES." WHERE Id='$id';";
	$result = $database->query($q);
	
	$CampoId =  '<input type="hidden" name="Id" value="'.$id.'">';
	$Pantalla = mysql_result($result,0,"codigo_pantalla_mupi") ;
	$codigo_mupi =  mysql_result($result,0,"codigo_mupi") ;
	$codigo_pedido = mysql_result($result,0,"codigo_pedido");
	$foto_real = mysql_result($result,0,"foto_real");
	$NombreBotonAccion = "Editar";
	$BotonCancelar = '<input type="button" OnClick="window.location=\'./?'._ACC_.'=gestionar+pantallas\'" value="Cancelar">';
} else {
	$q = "SELECT LAST_INSERT_ID() FROM ".TBL_MUPI_FACES; 
	$id = mysql_num_rows($database->query($q)) + 1;
	$NombreBotonAccion = "Registrar";
}
	$CampoCatorcena =  '<input type="hidden" name="catorcena" value="'.$Catorcena.'">';
	$CampoId2 = '<tr><td width="25%">Identificador</td><td><b>'. $id. '</b></td></tr>';
	$CampoPantalla = '<tr><td width="25%">Código de Pantalla '._NOMBRE_.'</td><td><input type="text" name="codigo_pantalla_mupi" style="width: 100%;" maxlength="255" value="'.$Pantalla.'"></td></tr>';
	$CampoCodigoMUPI = '<tr><td>Enlazar al '._NOMBRE_.' código</td><td>'. $database->Combobox_mupi("codigo_mupi", $codigo_mupi) .'</td></tr>';
	$CampoCodigoPedido = '<tr><td>Enlazar al pedido '._NOMBRE_.' código</td><td>'. $database->Combobox_pedido("codigo_pedido", $codigo_pedido, $Catorcena, Fin_de_catorcena($Catorcena)) . '</td></tr>';
	$CampoFotoReal = '<tr><td>Agregar Foto real </td><td><input type="text" name="foto_real" style="width: 100%;" maxlength="255" value="' . $foto_real . '"></td></tr>';

/*
if ($usuario) {
	$CampoUsuario  = '<input type="hidden" name="CampoUsuario" value="'.$usuario.'">';
}
*/

echo '
<form action="./?'._ACC_.'=gestionar+pantallas" method="POST">
<table>
'.$CampoCatorcena.'
'.$CampoId.'
'.$CampoId2.'
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
if ( isset($_POST['Id'] ) ) {
	$extra1 = 'Id, ';
	$extra2 = "'".$_POST['Id']."', ";
} else {
	$extra1 = '';
	$extra2 = '';
}
$q = "INSERT INTO ".TBL_MUPI_FACES." (".$extra1."codigo_pantalla_mupi, codigo_mupi, codigo_pedido, foto_real, catorcena) VALUES (".$extra2."'" . $_POST['codigo_pantalla_mupi'] . "', '" . $_POST['codigo_mupi']  . "', '" . $_POST['codigo_pedido']  . "', '" . $_POST['foto_real']  .  "', '" . $_POST['catorcena']  .  "')  ON DUPLICATE KEY UPDATE codigo_pantalla_mupi=VALUES(codigo_pantalla_mupi), codigo_mupi=VALUES(codigo_mupi), codigo_pedido=VALUES(codigo_pedido), foto_real=VALUES(foto_real);";
DEPURAR ($q);
if ( $database->query($q) == 1 ) {
	echo "<blockquote>Exito al registrar ".  $_POST['codigo_pantalla_mupi'].'</blockquote>';
} else {
	echo "<blockquote>Falló el registro de " . $_POST['codigo_pantalla_mupi'].'</blockquote>';
}
}
?>
