<?php
function CONTENIDO_referencias($usuario,$id_referencia) {
	
	if ( isset($_POST['registrar_referencia']) ) {
		Registrar_Referencia();
	}
	
	if ( isset($_GET['eliminar']) ) {
		Eliminar_Referencia($_GET['eliminar']);
	}
	
	Ver_Lista_Referencias();
	Ver_Formulario_Registro_Referencia($id_referencia);
	
	return;
}

function Ver_Lista_Referencias(){
	global $database;
	$q = "SELECT id_referencia, lon, lat, imagen_referencia, codigo_calle FROM ".TBL_REFS.";";
	$result = $database->query($q);
	$num_rows = @mysql_numrows($result);
	if(!$result || ($num_rows < 0)){
		echo Mensaje ("Error mostrando la información", _M_ERROR);
		return;
	}
    if($num_rows == 0){
        echo Mensaje ("¡No hay referencias ingresadas!", _M_NOTA);
        return;
    }
	echo "<table border=\"0\">";
	echo "<tr><th>ID. Referencia</th><th>Longitud</th><th>Latitud</th><th>Imagen</th><th>Calle</th><th>Acciones</th></tr>";
	for($i=0; $i<$num_rows; $i++){
		$id_referencia = CREAR_LINK_GET("gestionar+referencias&amp;referencia=".mysql_result($result,$i,"id_referencia"), mysql_result($result,$i,"id_referencia"), "Carga los datos de la referencia seleccionada para editar");
		$lon = mysql_result($result,$i,"lon");
		$lat = mysql_result($result,$i,"lat");
		$imagen_referencia = mysql_result($result,$i,"imagen_referencia");
		$codigo_calle = mysql_result($result,$i,"codigo_calle");
		$Eliminar = CREAR_LINK_GET("gestionar+referencias&amp;eliminar=".mysql_result($result,$i,"id_referencia"),"Eliminar", "Eliminar los datos de esta referencia");
	echo "<tr><td>$id_referencia</td><td>$lon</td><td>$lat</td><td>$imagen_referencia</td><td>$codigo_calle</td><td>$Eliminar</td></tr>";
	}
	echo "</table><br />";
}

function Ver_Formulario_Registro_Referencia($id_referencia) {
	global $database;
	// Iniciar las variables
	$Campo_Referencia_id_referencia = $Campo_Referencia_imagen_referencia = $OnChangePantalla = $lon = $lat = $imagen_referencia = $codigo_calle = NULL;
	
	// Si nos pasaron un ID de Referencia, entonces procesarlo para edición.
	if ( $id_referencia ) {

	$q = "SELECT id_referencia, lon, lat, imagen_referencia, codigo_calle FROM ".TBL_REFS." WHERE id_referencia=$id_referencia" . ";";
	$result = $database->query($q);
	$num_rows = @mysql_numrows($result);
	if(!$result || ($num_rows < 0)){
		echo Mensaje ("Error mostrando la información", _M_ERROR);
		return;
	}
	if($num_rows == 0){
		echo Mensaje ("¡No hay referencias ingresadas con ese ID!", _M_NOTA);
		return;
	}

	$id_referencia2 = $id_referencia;
	$lon = mysql_result($result,0,"lon");
	$lat = mysql_result($result,0,"lat");
	$imagen_referencia = mysql_result($result,0,"imagen_referencia");
	$codigo_calle = mysql_result($result,0,"codigo_calle");
	
	// Si ya existia una imagen, entonces darle la posibilidad de conservarla o eliminarla.
	if ( $imagen_referencia ) {
		$Campo_Referencia_imagen_referencia = '<tr><td>Conservar foto genérica con Id.'.$imagen_referencia.'</td></td><td><span id="Campo_Referencia_span_conservar_imagen_referencia"><input type="checkbox" name="Campo_Referencia_conservar_imagen_referencia" value="'.$imagen_referencia.'" checked="checked"></span></td></tr>';
		$Campo_Referencia_imagen_referencia .= '<input type="hidden" name="Campo_Referencia_imagen_referencia_id" value="'.$imagen_referencia.'">';	
		$OnChangePantalla = 'onchange="document.getElementById(\'Campo_Referencia_span_conservar_imagen_referencia\').innerHTML=\'Se reemplazará la imagen actual con la seleccionada\'"';
	}
	
	$Campo_Referencia_id_referencia = '<tr><td width="25%">Identificador</td><td><b>'. $id_referencia2. '</b></td></tr>';
	
	} else {
		$q = "SELECT LAST_INSERT_ID() FROM ".TBL_REFS; 
		$id_referencia = NULL;
		$id_referencia2 = mysql_num_rows($database->query($q)) + 1;
	}

// Creamos los campos del formulario.
$Campo_Referencia_id_referencia_hidden = '<input type="hidden" name="id_referencia" value="'.$id_referencia.'">';
$Campo_Referencia_lon = '<tr><td>Longitud Decimal:</td><td><input type="text" name="lon" style="width: 100%;" maxlength="50" value="' . $lon. '"></td></tr>';
$Campo_Referencia_lat = '<tr><td>Latitud Decimal:</td><td><input type="text" name="lat" style="width: 100%;" maxlength="50" value="' . $lat. '"></td></tr>';
$Campo_Referencia_imagen_referencia2 = '<tr><td>Imagen de referencia:</td><td><input type="file" name="imagen_referencia"></td></tr>';
$Campo_Referencia_codigo_calle = '<tr><td>Código calle:</td><td>'. $database->Combobox_calle("codigo_calle", $codigo_calle). '</td></tr>';

// Botones de acción
$BotonCancelar = '<input type="button" OnClick="window.location=\'./?'._ACC_.'=gestionar+referencias\'" value="Cancelar">';

// Mostramos el formulario.
echo 
'<form action="./?'._ACC_.'=gestionar+referencias"  enctype="multipart/form-data" method="POST">'
.'<table>'
.$Campo_Referencia_id_referencia_hidden
.$Campo_Referencia_id_referencia
.$Campo_Referencia_lon
.$Campo_Referencia_lat
.$Campo_Referencia_imagen_referencia
.$Campo_Referencia_imagen_referencia2
.$Campo_Referencia_codigo_calle
.'</table><br />'
.'<input type="submit" value="Continuar">'
.$BotonCancelar
.'<input type="hidden" name="registrar_referencia" value="1">'
.'</form>';
}

function Registrar_Referencia(){
	global $database;
	//print_ar($_POST);
	//print_ar($_FILES);
	if ( !$_FILES['imagen_referencia']['error'] ) {
		$Pre_Id = isset($_POST['Campo_Referencia_imagen_referencia_id']) ? $_POST['Campo_Referencia_imagen_referencia_id'] : 0;
		$idImg = CargarImagenEnBD("imagen_referencia","REFERENCIAS", $Pre_Id);
	} else {
		
		if ( isset ($_POST['Campo_Referencia_conservar_imagen_referencia']) ){
			 $idImg = $_POST['Campo_Referencia_imagen_referencia_id'];
		 } else {
			 $idImg = 0;
		 }
	}
	$q = "INSERT INTO ".TBL_REFS." (id_referencia, lon, lat, imagen_referencia, codigo_calle) VALUES('".$_POST['id_referencia']."','".$_POST['lon']."','".$_POST['lat']."','".$idImg."','".$_POST['codigo_calle']."') ON DUPLICATE KEY UPDATE lon=VALUES(lon), lat=VALUES(lat), imagen_referencia=VALUES(imagen_referencia), codigo_calle=VALUES(codigo_calle)" . ";";
	DEPURAR ($q,0);

	if ( $database->query($q) == 1 ) {
	echo Mensaje ("Exito al registrar/editar referencia ".  $_POST['id_referencia'], _M_INFO);
	} else {
	echo Mensaje ("Falló el registro/edición de la referencia " . $_POST['id_referencia'], _M_ERROR);
	}
}

function Eliminar_Referencia($id_referencia){
	global $database;
	$q = "DELETE FROM " . TBL_REFS . " WHERE id_referencia='" . $id_referencia . "';";
	$result = $database->query($q);
	if ( $result ) { echo Mensaje ("Referencia eliminada",_M_INFO); } else { echo Mensaje ("Rerefencia no pudo ser eliminada",_M_ERROR); }
}
?>
