<?php
function CONTENIDO_comentarios($usuario,$id_comentario) {
	echo '<h1>Visor de Comentarios</h1>';
	if ( isset($_POST['registrar_comentario']) ) {
		Registrar_Comentario();
	}
	
	if ( isset($_GET['eliminar']) ) {
		Eliminar_Comentario($_GET['eliminar']);
	}
	
	Ver_Lista_Comentarios();
	Ver_Formulario_Registro_Comentario($id_comentario);
	
	return;
}

function Ver_Lista_Comentarios(){
	global $database;
	$q = "SELECT id_comentario, codigo, comentario, timestamp, tipo FROM ".TBL_COMMENTS." ORDER BY tipo, id_comentario";
	$result = $database->query($q);
	$num_rows = @mysql_numrows($result);
	if(!$result || ($num_rows < 0)){
		echo Mensaje ("Error mostrando la información", _M_ERROR);
		return;
	}
    if($num_rows == 0){
        echo Mensaje ("¡No hay comentarios ingresados!", _M_NOTA);
        return;
    }
	echo "<table border=\"0\">";
	echo "<tr><th>ID. Comentario</th><th>Código</th><th>Comentario</th><th>Hora y Fecha</th><th>Tipo</th><th>Acciones</th></tr>";
	for($i=0; $i<$num_rows; $i++){
		$id_comentario = CREAR_LINK_GET("gestionar+comentarios&amp;comentario=".mysql_result($result,$i,"id_comentario"), mysql_result($result,$i,"id_comentario"), "Carga los datos del comentario seleccionado para editar");
		$codigo = mysql_result($result,$i,"codigo");
		$comentario = mysql_result($result,$i,"comentario");
		$timestamp = date('h:i:s @ d/m/Y', mysql_result($result,$i,"timestamp"));
		$tipo = mysql_result($result,$i,"tipo") == 0 ? "Privado" : "Público";
		$Eliminar = CREAR_LINK_GET("gestionar+comentarios&amp;eliminar=".mysql_result($result,$i,"id_comentario"),"Eliminar", "Eliminar los datos de este comentario");
	echo "<tr><td>$id_comentario</td><td>$codigo</td><td>$comentario</td><td>$timestamp</td><td>$tipo</td><td>$Eliminar</td></tr>";
	}
   echo "<tfoot>";
   echo "<td colspan='5'>Total</td><td>$num_rows</td>";
   echo "</tfoot>";
	echo "</table><br />";
}

function Ver_Formulario_Registro_Comentario($id_comentario){
	global $database;
	if ( !$id_comentario ) return;
	$q = "SELECT id_comentario, codigo, comentario, timestamp, tipo FROM ".TBL_COMMENTS." WHERE id_comentario=$id_comentario" . ";";
	$result = $database->query($q);
	$num_rows = @mysql_numrows($result);
	if(!$result || ($num_rows < 0)){
		echo Mensaje ("Error mostrando la información", _M_ERROR);
		return;
	}
	if($num_rows == 0){
		echo Mensaje ("¡No hay comentarios ingresados!", _M_NOTA);
		return;
	}

	//$id_comentario
	$codigo = mysql_result($result,0,"codigo");
	$comentario = mysql_result($result,0,"comentario");
	$timestamp = mysql_result($result,0,"timestamp");
	$tipo = mysql_result($result,0,"tipo")  == 0 ? '' : 'checked="checked"';

// Creamos los campos del formulario.
$Campo_Comentario_id_comentario_hidden = '<input type="hidden" name="id_comentario" value="'.$id_comentario.'">';
$Campo_Comentario_id_comentario = '<tr><td width="25%">Identificador</td><td><b>'. $id_comentario. '</b></td></tr>';
$Campo_Comentario_codigo = '<tr><td>Cliente:</td><td>'.$database->Combobox_usuarios("codigo",$codigo) . '</td></tr>';
$Campo_Comentario_comentario = '<tr><td>Comentario:</td><td><input type="text" name="comentario" maxlength="100" value="' . $comentario. '"></td></tr>';
$Campo_Comentario_timestamp = '<tr><td>Hora y Fecha</td><td><b>'. date('h:i:s @ d/m/Y',$timestamp). '</b></td></tr>';
$Campo_Comentario_tipo = '<tr><td>¿Comentario público?:</td><td><input type="checkbox" name="tipo" '.$tipo.' value="publico" /></tr></td>';

// Botones de acción
$BotonCancelar = '<input type="button" OnClick="window.location=\'./?'._ACC_.'=gestionar+comentarios\'" value="Cancelar">';

// Mostramos el formulario.
echo 
'<form action="./?'._ACC_.'=gestionar+comentarios" method="POST">'
.'<table>'
.$Campo_Comentario_id_comentario_hidden
.$Campo_Comentario_id_comentario
.$Campo_Comentario_codigo
.$Campo_Comentario_comentario
.$Campo_Comentario_timestamp
.$Campo_Comentario_tipo
.'</table><br />'
.'<input type="submit" value="Editar">'
.$BotonCancelar
.'<input type="hidden" name="registrar_comentario" value="1">'
.'</form>';
}

function Registrar_Comentario(){
	global $database;
	$tipo = (int) isset($_POST['tipo']);
	$q = "UPDATE ".TBL_COMMENTS." SET codigo='".$_POST['codigo']."', comentario='".$_POST['comentario']."', tipo='".$tipo."' WHERE id_comentario='".$_POST['id_comentario']."'" . ";";
	DEPURAR ($q,0);
	//print_ar($_POST);
	if ( $database->query($q) == 1 ) {
	echo Mensaje ("Exito al editar comentario ".  $_POST['id_comentario'], _M_INFO);
	} else {
	echo Mensaje ("Falló la edición del comentario " . $_POST['id_comentario'], _M_ERROR);
	}
}

function Eliminar_Comentario($id_comentario){
	global $database;
	$q = "DELETE FROM " . TBL_COMMENTS . " WHERE id_comentario='" . $id_comentario . "';";
	$result = $database->query($q);
	if ( $result ) { echo Mensaje ("Comentario eliminado",_M_INFO); } else { echo Mensaje ("Comentario no pudo ser eliminado",_M_ERROR); }
}
?>
