<?php
$Catorcena = NULL;
function CONTENIDO_pantallas($usuario, $pantalla , $catorcena_inicio) {
	global $session, $form, $Catorcena, $database;
	echo '<h1>Gestión de pantallas de ' . _NOMBRE_ . '</h1>';
	if ( $session->isAdmin() ) {
	
	if ( isset($_POST['registrar_mupi'])) {
		//Nos toca registrar un MUPI
		Pantalla_REGISTRAR();
	}
	
	if ( isset($_GET['sub']) && $catorcena_inicio ) {
		switch ( $_GET['sub'] ) {
			case 'clonar':
			$CatorcenaAnterior = Obtener_catorcena_anterior($catorcena_inicio);
			$q = "INSERT INTO emupi_mupis_caras (tipo_pantalla, codigo_mupi , codigo_pedido , foto_real , catorcena ) SELECT tipo_pantalla, codigo_mupi, codigo_pedido , foto_real , $catorcena_inicio FROM emupi_mupis_caras WHERE catorcena=$CatorcenaAnterior;";
			$result = $database->query($q);
			if ( $result ) { echo Mensaje ("Clonado completo.<br />Los datos de la catorcena ".date('d/m/Y',$CatorcenaAnterior)." ahora existen para la catorcena ".date('d/m/Y',$catorcena_inicio),_M_INFO); } else { echo Mensaje ("Falló la clonación.",_M_ERROR); }
			break;
			
			case 'eliminar_datos':
			$q = "DELETE FROM emupi_mupis_caras WHERE catorcena=$catorcena_inicio;";
			$result = $database->query($q);
			if ( $result ) { echo Mensaje ("Eliminado de datos completo.<br />Se eliminaron los datos de la catorcena ".date('d/m/Y',$catorcena_inicio),_M_INFO); } else { echo Mensaje ("Falló la eliminación de datos.",_M_ERROR); }
			break;
		}	
	}
	
	if ( isset($_GET['eliminar']) && isset($_GET['imagen']) ) {
			// Eliminamos la pantalla
			$q = "DELETE FROM " . TBL_MUPI_FACES . " WHERE id_pantalla='" . $_GET['eliminar'] . "';";
			$result = $database->query($q);
			if ( $result ) { echo Mensaje ("Pantalla eliminada",_M_INFO); } else { echo Mensaje ("Pantalla no pudo ser eliminada",_M_ERROR); }
			
			// Eliminamos cualquier imagen que estuviera asociada a esa pantalla
			if ($_GET['imagen']) {
			$q = "DELETE FROM " . TBL_IMG . " WHERE id_imagen=" . $_GET['imagen'] . ";";
			$result = $database->query($q);
			if ( $result ) { echo Mensaje ("Imagen asociada eliminada",_M_INFO); } else { echo Mensaje ("Imagen asociada no pudo ser eliminada",_M_ERROR); }
			}
	}
	
	}
	if ( !$catorcena_inicio ) {
		$BotonCancelar = '';
		$Catorcena = Obtener_catorcena_cercana();
	} else {
		$BotonCancelar = '<input type="button" OnClick="window.location=\'./?'._ACC_.'=gestionar+pantallas\'" value="Volver a catorcena actual">';
		$Catorcena = $catorcena_inicio;
	}

	echo '<hr /><h2>Pantallas '._NOMBRE_." en la catorcena de ".date("d/m/Y",$Catorcena)."</h2>";
	
	echo "Viendo pantallas "._NOMBRE_." de la catorcena " . Combobox_catorcenas("miSelect", $Catorcena) ;
	$BotonCambiar = '<input type="button" OnClick="window.location=\'./?'._ACC_.'=gestionar+pantallas&amp;catorcena=\'+document.getElementsByName(\'miSelect\')[0].value" value="Cambiar">';
	$BotonClonarCatorcenaAnterior = '<input type="button" OnClick="window.location=\'./?'._ACC_.'=gestionar+pantallas&amp;catorcena='.$Catorcena.'&amp;sub=clonar\'" value="Clonar anterior" '.GenerarTooltip('Clona los datos de los mupis de la catorcena inmediata anterior').'>';
	$BotonEliminarDatosCatorcena = '<input type="button" OnClick="window.location=\'./?'._ACC_.'=gestionar+pantallas&amp;catorcena='.$Catorcena.'&amp;sub=eliminar_datos\'" value="Eliminar Datos" '.GenerarTooltip('Elimina los datos mostrados para la catorcena actual').'>';	
	echo $BotonCambiar;
	echo $BotonCancelar;
	echo $BotonClonarCatorcenaAnterior;
	echo $BotonEliminarDatosCatorcena;
	echo "<hr />";
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
   $q = "SELECT id_pantalla, tipo_pantalla, codigo_mupi, (SELECT CONCAT(codigo_calle, '.' , codigo_mupi, ' | ', (SELECT ubicacion FROM emupi_calles AS b WHERE c.codigo_calle=@codigo_calle:=b.codigo_calle), ', ', direccion ) FROM emupi_mupis as c WHERE c.id_mupi=a.codigo_mupi) AS codigo_mupi_traducido, codigo_pedido, (SELECT CONCAT(codigo_pedido, '. ' , o.descripcion) FROM ".TBL_MUPI_ORDERS." as o WHERE o.codigo_pedido = a.codigo_pedido) as codigo_pedido_traducido, catorcena, foto_real, codigo_evento, @calle as codigo_calle2 FROM ".TBL_MUPI_FACES." as a WHERE catorcena = $Catorcena ORDER BY codigo_calle2, codigo_mupi, tipo_pantalla;";
   //echo $q;
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
echo "<tr><th>Código "._NOMBRE_."</th><th>Cara</th><th>Código pedido</th><th>Foto real</th><th>Evento</th><th>Acción</th></tr>";
   for($i=0; $i<$num_rows; $i++){
      $tipo_pantalla  = mysql_result($result,$i,"tipo_pantalla");
      $codigo_mupi = CREAR_LINK_GET("gestionar+mupis&amp;mupi=".mysql_result($result,$i,"codigo_mupi"), mysql_result($result,$i,"codigo_mupi_traducido"), "Ver y/o editar los datos de este "._NOMBRE_);
      $codigo_pedido = CREAR_LINK_GET("gestionar+pedidos&amp;pedido=" . mysql_result($result,$i,"codigo_pedido"), mysql_result($result,$i,"codigo_pedido_traducido"), "Ver a quien pertenece este pedido");
      $codigo_evento = ''; //Ejecutar la búsqueda de eventos para esta pantalla
	  $codigo_evento .= CREAR_LINK_GET("gestionar+eventos&amp;sub=adicionar&amp;tipo=PANTALLA&amp;afectado=".mysql_result($result,$i,"id_pantalla"),"Agregar","Agrega un evento");
      $foto_real  = mysql_result($result,$i,"foto_real");
	  if ( $foto_real ) { $foto_real = "<span ".GenerarTooltip(CargarImagenDesdeBD(mysql_result($result,$i,"foto_real"),'200px','200px'))." />". $foto_real."</span>"; }
      $Eliminar = CREAR_LINK_GET("gestionar+pantallas&amp;eliminar=".mysql_result($result,$i,"id_pantalla")."&amp;imagen=".mysql_result($result,$i,"foto_real")."&amp;catorcena=$Catorcena","Eliminar", "Eliminar los datos de esta pantalla");
      $tipo_pantalla  = CREAR_LINK_GET("gestionar+pantallas&amp;id=".mysql_result($result,$i,"id_pantalla")."&amp;catorcena=$Catorcena",($tipo_pantalla == 0 ? 'Vehicular' : 'Peatonal'), "Editar los datos de esta pantalla");
      echo "<tr><td>$codigo_mupi</td><td>$tipo_pantalla</td><td>$codigo_pedido</td><td>$foto_real</td><td>$codigo_evento</td><td>$Eliminar</td></tr>";
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
$foto_pantalla = '';
$OnChangePantalla = '';
$CampoConservarPantalla = '';
$CampoConservarPantalla2 = '';

if ($id) {
	$q = "SELECT * FROM ".TBL_MUPI_FACES." WHERE id_pantalla='$id';";
	$result = $database->query($q);
	
	$CampoId =  '<input type="hidden" name="id_pantalla" value="'.$id.'">';
	$Pantalla = mysql_result($result,0,"tipo_pantalla") ;
	$codigo_mupi =  mysql_result($result,0,"codigo_mupi") ;
	$codigo_pedido = mysql_result($result,0,"codigo_pedido");
	$Catorcena = mysql_result($result,0,"catorcena");
	$foto_real = mysql_result($result,0,"foto_real");
	if ( $foto_real ) {
		$CampoConservarPantalla = '<tr><td>Conservar foto con Id.'.$foto_real.'</td></td><td><span id="CampoConservarPantalla"><input type="checkbox" name="ConservarPantalla" value="'.$foto_pantalla.'" checked="checked"></span></td></tr>';
		$CampoConservarPantalla2 = '<input type="hidden" name="ConservarPantalla2" value="'.$foto_real.'">';	
		$OnChangePantalla = 'onchange="document.getElementById(\'CampoConservarPantalla\').innerHTML=\'Se reemplazará la imagen actual con la seleccionada\'"';
	}
	$NombreBotonAccion = "Editar";
	$BotonCancelar = '<input type="button" OnClick="window.location=\'./?'._ACC_.'=gestionar+pantallas\'" value="Cancelar">';
} else {
	$q = "SELECT LAST_INSERT_ID() FROM ".TBL_MUPI_FACES; 
	$id = mysql_num_rows($database->query($q)) + 1;
	$NombreBotonAccion = "Registrar";
}
	$CampoCatorcena =  '<input type="hidden" name="catorcena" value="'.$Catorcena.'">';
	$CampoId2 = '<tr><td width="25%">Identificador</td><td><b>'. $id. '</b></td></tr>';
	$CampoPantalla = '<tr><td width="25%">Código de Pantalla '._NOMBRE_.'</td><td>'.Combobox__TipoPantalla ($Pantalla).'</td></tr>';
	$CampoCodigoMUPI = '<tr><td>Enlazar al '._NOMBRE_.' código</td><td>'. $database->Combobox_mupi("codigo_mupi", $codigo_mupi) .'</td></tr>';
	$CampoCodigoPedido = '<tr><td>Enlazar al pedido '._NOMBRE_.' código</td><td>'. $database->Combobox_pedido("codigo_pedido", $codigo_pedido, $Catorcena, Fin_de_catorcena($Catorcena)) . '</td></tr>';
	$CampoFotoReal = '<tr><td>Agregar Foto real </td><td><input type="file" name="foto_real" '.$OnChangePantalla.'></td></tr>';

/*
if ($usuario) {
	$CampoUsuario  = '<input type="hidden" name="CampoUsuario" value="'.$usuario.'">';
}
*/

echo '
<form action="./?'._ACC_.'=gestionar+pantallas&amp;catorcena='.$Catorcena.'" enctype="multipart/form-data" method="POST">
<table>
'.$CampoCatorcena.'
'.$CampoId.'
'.$CampoCodigoMUPI.'
'.$CampoPantalla.'
'.$CampoCodigoPedido.'
'.$CampoConservarPantalla.'
'.$CampoConservarPantalla2.'
'.$CampoFotoReal.'
'.$CampoId2.'
</table>
<input type="submit" value="'.$NombreBotonAccion.'">
'.$BotonCancelar.'
<input type="hidden" name="registrar_mupi" value="1">
</form>';
}

function Pantalla_REGISTRAR() {
global $database;
if ( !isset($_POST['ConservarPantalla']) ) {
	/*
		Corroborar si ya tenia una imagen antes, para reutilizar la fila y a la vez
		que la imagen anterior no quede huerfana.
	*/
	$Pre_Id = isset($_POST['ConservarPantalla2']) ? $_POST['ConservarPantalla2'] : 0;
	$idImg = CargarImagenEnBD("foto_real","PANTALLAS", $Pre_Id);
} else {
	$idImg = $_POST['ConservarPantalla'];
}
if ( isset($_POST['id_pantalla'] ) ) {
	$extra1 = 'id_pantalla, ';
	$extra2 = "'".$_POST['id_pantalla']."', ";
} else {
	$extra1 = '';
	$extra2 = '';
}
$q = "INSERT INTO ".TBL_MUPI_FACES." (".$extra1."tipo_pantalla, codigo_mupi, codigo_pedido, foto_real, catorcena) VALUES (".$extra2."'" . $_POST['tipo_pantalla'] . "', '" . $_POST['codigo_mupi']  . "', '" . $_POST['codigo_pedido']  . "', '" . $idImg .  "', '" . $_POST['catorcena']  .  "')  ON DUPLICATE KEY UPDATE tipo_pantalla=VALUES(tipo_pantalla), codigo_mupi=VALUES(codigo_mupi), codigo_pedido=VALUES(codigo_pedido), foto_real=VALUES(foto_real);";
DEPURAR ($q);
if ( $database->query($q) == 1 ) {
	echo Mensaje ("Exito al registrar la pantalla", _M_INFO);
} else {
	echo Mensaje ("Falló al registrar la pantalla", _M_ERROR);
}
}

function Combobox__TipoPantalla($default=0){
	$datos = '<select name="tipo_pantalla">';
	$datos .= '<option value="0"'. ($default == 0 ? 'selected="selected"' : '') .'>Vehicular</option>';
	$datos .= '<option value="1"'. ($default == 1 ? 'selected="selected"' : '') .'>Peatonal</option>';
	$datos .= '</select>';
	return $datos;
}
?>
