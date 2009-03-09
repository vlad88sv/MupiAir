<?php
function CONTENIDO_mupis($usuario="",$mupi="",$calle=NULL) {
	global $session;
	echo '<h1>Gestión de ' . _NOMBRE_ . '</h1>';
	if ( $session->isAdmin() ) {
		
		//Nos toca registrar un MUPI
		if ( isset($_POST['registrar_mupi']) ) {
		MUPI_REGISTRAR();
		}

		//Nos toca elimiinar un MUPI
		if ( isset($_GET['eliminar']) ) {
		global $database;
		// Eliminamos la pantalla
		$q = "DELETE FROM " . TBL_MUPI . " WHERE id_mupi='" . $_GET['eliminar'] . "';";
		DEPURAR ($q,0);
		$result = $database->query($q);
		if ( $result ) { echo Mensaje ("Eco Mupis eliminado",_M_INFO); } else { echo Mensaje ("Eco Mupis no pudo ser eliminado",_M_ERROR); }
		}
		
	}
	echo '<hr /><h2>'._NOMBRE_." disponibles</h2>";
	verMUPIS($calle);
	if ( $session->isAdmin() ) {
	verMUPISregistro($usuario,$mupi);
	}
}
function verMUPIS($calle=NULL){
   global $database;
   ob_start();
   	echo '
	<script type="text/javascript">
	$(document).ready(function() {
	$("#toggler").click(function() {
	$("#tabla_mupis").toggle();
	});
	});
	</script>
	';
   if ( $calle ) { $wCalle = "WHERE codigo_calle='$calle'"; $conservar_GET_calle="&amp;calle=$calle"; } else { $conservar_GET_calle = $wCalle = NULL; }
   $q = "SELECT id_mupi, codigo_mupi, direccion, foto_generica, lon, lat, codigo_evento, codigo_calle, (SELECT ubicacion FROM ".TBL_STREETS." AS b WHERE a.codigo_calle=b.codigo_calle) AS 'calle' FROM ".TBL_MUPI." as a $wCalle;";
   DEPURAR($q,0);
   $result = $database->query($q);
   /* Error occurred, return given name by default */
   $num_rows = @mysql_numrows($result);
   if(!$result || ($num_rows < 0)){
      echo "Error mostrando la información";
      return;
   }
   if($num_rows == 0){
      echo Mensaje ("¡No hay "._NOMBRE_." ingresados!<br/>", _M_NOTA);
      return;
   }
    $BotonFiltraVistaPorCalles = '<input type="button" OnClick="window.location=\'./?'._ACC_.'=gestionar+mupis&amp;calle=\'+document.getElementsByName(\'cmbCalles\')[0].value" value="Filtrar"><hr />';
    echo "<b>Filtrar vista a "._NOMBRE_." que se ubiquen en la calle</b> ". $database->Combobox_calle("cmbCalles");
	echo $BotonFiltraVistaPorCalles;
	echo '<a id="toggler">Mostrar/Ocultar lista de Eco Mupis</a>';
	echo '<div id="tabla_mupis" style="display:none"><table>';
	echo '<table border="0">';
	echo "<tr><th width=\"5%\">ID Mupi</th><th width=\"10%\">Código Mupi</th><th width=\"30%\">Dirección</th><th width=\"5%\">Foto</th><th width=\"5%\">Longitud</th><th width=\"5%\">Latitud</th><th width=\"%30\">Calle</th><th width=\"10%\">Acciones</th></tr>";
	for($i=0; $i<$num_rows; $i++){
		$id = CREAR_LINK_GET("gestionar+mupis".$conservar_GET_calle."&amp;mupi=".mysql_result($result,$i,"id_mupi"), mysql_result($result,$i,"id_mupi"), "Carga los datos del "._NOMBRE_. " seleccionado para editar");
		$codigo_mupi  = mysql_result($result,$i,"codigo_calle").".".mysql_result($result,$i,"codigo_mupi");
		$direccion = mysql_result($result,$i,"direccion");
		$foto_generica = mysql_result($result,$i,"foto_generica");
		$Longitud  = mysql_result($result,$i,"lon");
		$Latitud  = mysql_result($result,$i,"lat");
		$codigo_calle  = CREAR_LINK_GET("gestionar+calles&amp;calle=".mysql_result($result,$i,"codigo_calle"), mysql_result($result,$i,"calle"), "Editar los datos de este pedido");
		$Eliminar = CREAR_LINK_GET("gestionar+mupis$conservar_GET_calle&amp;eliminar=".mysql_result($result,$i,"id_mupi"),"Eliminar", "Eliminar los datos de este "._NOMBRE_);
	echo "<tr><td  width=\"5%\">$id</td><td  width=\"10%\">$codigo_mupi</td><td width=\"30%\">$direccion</td><td width=\"5%\">$foto_generica</td><td width=\"5%\">$Longitud</td><td width=\"5%\">$Latitud</td><td  width=\"30%\">$codigo_calle</td><td  width=\"10%\">$Eliminar</td></tr>";
	}
	echo "<tfoot>";
    echo "<td colspan='7'>Total</td><td>$num_rows</td>";
    echo "</tfoot>";
	echo "</table></div><br />";
	ob_flush();
	flush();
}

function verMUPISregistro($usuario="",$mupi="") {
global $form, $database;
$BotonCancelar = '';
$NombreBotonAccion = '';
$foto_pantalla = '';
$OnChangePantalla = '';
$CampoIdMupi = '';
$CampoConservarPantalla = '';
$CampoConservarPantalla2 = '';
//Nos toca crear un MUPI - 16/02/09 - Facilidad para combinar con mapas.
if ( isset($_GET['crear']) && isset($_GET['lat']) && isset($_GET['lng']) && isset($_GET['calle'])) {
	$form->setValue("lon", $_GET['lng']);
	$form->setValue("lat", $_GET['lat']);
	$form->setValue("codigo_calle", $_GET['calle']);
}
if ($mupi) {
	$q = "SELECT * FROM ".TBL_MUPI." WHERE id_mupi='$mupi';";
	$result = $database->query($q);
	
	switch ( mysql_numrows($result) ) {
	case 1:
		$form->setValue("codigo_mupi", mysql_result($result,0,"codigo_mupi"));
		$form->setValue("direccion", mysql_result($result,0,"direccion"));
		$form->setValue("lon", mysql_result($result,0,"lon"));
		$form->setValue("lat", mysql_result($result,0,"lat"));
		$form->setValue("codigo_calle", mysql_result($result,0,"codigo_calle"));
		$foto_pantalla =  mysql_result($result,0,"foto_generica");
	if ( $foto_pantalla ) {
		$CampoConservarPantalla = '<tr><td>Conservar foto genérica con Id.'.$foto_pantalla.'</td></td><td><span id="CampoConservarPantalla"><input type="checkbox" name="ConservarPantalla" value="'.$foto_pantalla.'" checked="checked"></span></td></tr>';
		$CampoConservarPantalla2 = '<input type="hidden" name="ConservarPantalla2" value="'.$foto_pantalla.'">';	
		$OnChangePantalla = 'onchange="document.getElementById(\'CampoConservarPantalla\').innerHTML=\'Se reemplazará la imagen actual con la seleccionada\'"';
	}
		$CampoIdMupi = '<input type="hidden" name="id_mupi" value="'.mysql_result($result,0,"id_mupi").'">';
		$NombreBotonAccion = "Editar";
		$BotonCancelar = '<input type="button" OnClick="window.location=\'./?'._ACC_.'=gestionar+mupis\'" value="Cancelar">';
		break;
	case 0:
		echo Mensaje ("No se encontró un Eco Mupis con este código",_M_ERROR);
		return;
		break;
	default:
		echo Mensaje("Error al búscar Eco Mupis solicitado",_M_ERROR);
		return;
		break;
	}
} else {
	$NombreBotonAccion = "Registrar";
}
	$mupiex ="";
	if ($mupi) $mupiex = " (".$mupi.")";
echo '<hr /><h2>'.$NombreBotonAccion.' '._NOMBRE_.$mupiex.'</h2>';
echo '
<form action="./?'._ACC_.'=gestionar+mupis" enctype="multipart/form-data" method="POST">
<table>
<tr><td>Código calle:</td><td>'. $database->Combobox_calle("codigo_calle", $form->value("codigo_calle")). '</td></tr>
<tr><td>Código '._NOMBRE_.':</td><td><input type="text" name="codigo_mupi" maxlength="100" style="width: 100%;" value="' . $form->value("codigo_mupi"). '"></td></tr>
<tr><td width="20%">Dirección específica:</td><td><input type="text" name="direccion" style="width: 100%;" maxlength="255" value="' . $form->value("direccion"). '"></tr>
'.$CampoIdMupi.'
'.$CampoConservarPantalla.'
'.$CampoConservarPantalla2.'
<tr><td>Foto genérica:</td><td><input type="file" name="foto_generica"></td></tr>
<tr><td>Longitud Decimal:</td><td><input type="text" name="lon" style="width: 100%;" maxlength="50" value="' . $form->value("lon"). '"></td></tr>
<tr><td>Latitud Decimal:</td><td><input type="text" name="lat" style="width: 100%;" maxlength="50" value="' . $form->value("lat"). '"></td></tr>
</table>
<input type="submit" value="'.$NombreBotonAccion.'">
'.$BotonCancelar.'
<input type="hidden" name="registrar_mupi" value="1">
</form>';
}

function MUPI_REGISTRAR() {
global $database,$form;
	//print_ar($_POST);
	//print_ar($_FILES);
if ( !$_FILES['foto_generica']['error'] ) {
	$Pre_Id = isset($_POST['ConservarPantalla2']) ? $_POST['ConservarPantalla2'] : 0;
	$idImg = CargarImagenEnBD("foto_generica","MUPIS", $Pre_Id);
} else {
	
	if ( isset ($_POST['ConservarPantalla']) ){
		 $idImg = $_POST['ConservarPantalla2'];
	 } else {
		 $idImg = 0;
	 }
}
$id_mupi= isset($_POST['id_mupi']) ? $_POST['id_mupi'] : '0';
$q = "INSERT INTO ".TBL_MUPI." (id_mupi, codigo_mupi, direccion, foto_generica, lon, lat, codigo_calle) VALUES (".$id_mupi.", '".$_POST['codigo_mupi'] . "', '" . $_POST['direccion'] . "','" . $idImg . "','" . $_POST['lon'] . "', '" . $_POST['lat'] . "', '" . $_POST['codigo_calle'] . "') ON DUPLICATE KEY UPDATE codigo_mupi=VALUES(codigo_mupi), direccion=VALUES(direccion), foto_generica=VALUES(foto_generica), lon=VALUES(lon), lat=VALUES(lat), codigo_calle=VALUES(codigo_calle);";
DEPURAR ($q);	
if ( $database->query($q) == 1 ) {
	echo Mensaje ("Exito al registrar el Eco Mupi con código ".  $_POST['codigo_mupi'], _M_INFO);
} else {
	echo Mensaje ("Falló al registrar el Eco Mupi con código " . $_POST['codigo_mupi'], _M_ERROR);
}
}
?>
