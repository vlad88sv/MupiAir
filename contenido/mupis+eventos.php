<?php
$timestamp = time();
function CONTENIDO_mupis_eventos($usuario,$id_evento='',$tipo_evento='',$afectado='') {
	global $session, $form, $database, $timestamp;
	echo '<h1>Eventos ' . _NOMBRE_ . '</h1>';
		if ( isset($_GET['sub']) ) {
		switch ( $_GET['sub'] ) {
			case 'adicionar':
			echo Mensaje ("Se solicitó la adición de un nuevo evento, el ticket para este evento será registrado para la hora ".date('h:i:s @ d/m/Y',$timestamp),_M_INFO); 
			if ( $_GET['tipo'] && $_GET['afectado'] ){
				$tipo_evento= $_GET['tipo'];
				$afectado= $_GET['afectado'];
			}
			break;
		}	
	}
	if ( $usuario ) {
		if ( !$database->codigoTaken($usuario) ) {
			echo "<hr /><h2>No existe el Cliente o Usuario $usuario</h2>";
			return;
		}
	}

	echo '<hr /><h2>Sus Eventos </h2>';
		//Nos toca registrar un Evento
	if ( isset($_POST['registrar_eventos']) ) {
		Eventos_REGISTRAR();
	}
	
		//Nos toca eliminar un Evento
		if ( isset($_GET['eliminar']) && isset($_GET['imagen']) ) {
		global $database;
		// Eliminamos el evento
		$q = "DELETE FROM " . TBL_EVENTS . " WHERE id_evento=" . $_GET['eliminar'] . ";";
		$result = $database->query($q);
		if ( $result ) { echo Mensaje ("Evento eliminado",_M_INFO); } else { echo Mensaje ("Evento no pudo ser eliminado",_M_ERROR); }
		
		// Eliminamos cualquier imagen que estuviera asociada a ese Evento
		if ($_GET['imagen']) {
		$q = "DELETE FROM " . TBL_IMG . " WHERE id_imagen=" . $_GET['imagen'] . ";";
		$result = $database->query($q);
		if ( $result ) { echo Mensaje("Imagen asociada al Evento eliminada",_M_INFO); } 
		}
		}
	
	verEventos($usuario);
	if ( $session->isAdmin() && $tipo_evento && $afectado || $id_evento ) {
	
	if ($usuario) { $paraUsuario = " para $usuario"; } else { $paraUsuario = ""; }
	
	if ($id_evento) {
		$edicionOregistro = 'Edición del Evento ' . $id_evento;
	} else {
		$edicionOregistro = 'Registrar Evento';
	}
	
	echo '<hr /><h2>'.$edicionOregistro.$paraUsuario.'</h2>';
	
	EventosRegistro ($id_evento, $tipo_evento, $afectado);
	}
}

function verEventos($usuario="", $evento=""){
   global $database;
   
   $WHERE = "";
   $num_rows = "";
   if ($usuario) { $WHERE = " WHERE codigo='".$usuario."'"; }
   
   $q = "SELECT id_evento, timestamp, categoria, afectado, descripcion_evento, foto_evento FROM ".TBL_EVENTS;
   $result = $database->query($q);
   
   if ( !$result ) {
      echo "Error mostrando la información";
      return;
   }
   
   $num_rows = mysql_numrows($result);
   if ( $num_rows == 0 ) {
      echo Mensaje ("¡No hay Eventos Ingresados!",_M_NOTA);
      return;
   }
   
echo '<table>';
echo "<tr><th>Código Evento "._NOMBRE_."</th><th>Fecha y Hora</th><th>Categoría</th><th>Objeto Afectado</th><th>Descripción</th><th>Foto</th><th>Acciones</th></tr>";
   for($i=0; $i<$num_rows; $i++){
      $id_evento  = mysql_result($result,$i,"id_evento");
      $timestamp  = date('h:i:s @ d/m/Y', mysql_result($result,$i,"timestamp"));
      $categoria  = mysql_result($result,$i,"categoria");
      $afectado  = mysql_result($result,$i,"afectado");
      $descripcion_evento  = mysql_result($result,$i,"descripcion_evento");
      $foto_evento  = mysql_result($result,$i,"foto_evento");
	  if ( $foto_evento ) { $foto_evento = "<span ".GenerarTooltip(CargarImagenDesdeBD(mysql_result($result,$i,"foto_evento"),'200px','200px'))." />". $foto_evento."</span>"; }
      $Eliminar = CREAR_LINK_GET("gestionar+eventos&amp;eliminar=".mysql_result($result,$i,"id_evento")."&amp;imagen=" . mysql_result($result,$i,"foto_evento") ,"Eliminar", "Eliminar los datos de este evento");
      $id_evento  = CREAR_LINK_GET("gestionar+eventos&amp;evento=".$id_evento,$id_evento, "Editar los datos de este evento");
      echo "<tr><td>$id_evento</td><td>$timestamp</td><td>$categoria</td><td>$afectado</td><td>$descripcion_evento</td><td>$foto_evento</td><td>$Eliminar</tr>";
   }
   echo "</table><br>";
}

function EventosRegistro($id_evento, $tipo_evento, $afectado) {
global $form, $database,  $timestamp;
$CampoCodigoEvento = 0;
$CampoUsuario = '';
$CampoFoto = '';
$CampoConservarFoto = '';
$BotonCancelar = '';
$CampoFoto = '';
$costo='';
$foto_evento = '';
$OnChangePantalla = '';
$CampoConservarFoto2 = '';
$descripcion = '';
if ($id_evento) {
	$q = "SELECT * FROM ".TBL_EVENTS." WHERE id_evento='$id_evento';";
	$result = $database->query($q);
	$num_rows = mysql_numrows($result);
	if ( $num_rows == 0 ) {
		echo "¡No hay Eventos con este código ingresado!<BR />";
		return;
	}
	$timestamp = mysql_result($result,0,"timestamp");
	$tipo_evento = mysql_result($result,0,"categoria");
	$afectado = mysql_result($result,0,"afectado");
	$foto_evento = mysql_result($result,0,"foto_evento");
	$descripcion = mysql_result($result,0,"descripcion_evento");
	if ( $foto_evento ) {
		$CampoConservarFoto = '<tr><td>Conservar foto con Id.'.$foto_evento.'</td></td><td><span id="CampoConservarFoto"><input type="checkbox" name="ConservarFoto" value="'.$foto_evento.'" checked="checked"></span></td></tr>';
		$CampoConservarFoto2 = '<input type="hidden" name="ConservarFoto2" value="'.$foto_evento.'">';	
		$OnChangePantalla = 'onchange="document.getElementById(\'CampoConservarFoto\').innerHTML=\'Se reemplazará la imagen actual con la seleccionada\'"';
	}
	$CampoCodigoEvento = '<input type="hidden" name="id_evento" value="'.$id_evento.'">';	
	$NombreBotonAccion = "Editar";
	$BotonCancelar = '<input type="button" OnClick="window.location=\'./?'._ACC_.'=gestionar+eventos\'" value="Cancelar">';
} else {
	$q = "SELECT LAST_INSERT_ID() FROM ".TBL_EVENTS; 
	$id_evento = mysql_num_rows($database->query($q)) + 1;
	$CampoCodigoEvento = '<input type="hidden" name="id_evento" value="0">';
	$NombreBotonAccion = "Registrar";
}
	$CampoTimestamp = '<input type="hidden" name="timestamp" value="'.$timestamp.'">';
	$CampoTipoEvento = '<input type="hidden" name="tipo_evento" value="'.$tipo_evento.'">';
	$CampoAfectado = '<input type="hidden" name="afectado" value="'.$afectado.'">';
	$CampoCodigoEvento2 = '<tr><td width="25%">Código de evento</td><td><b>'. $id_evento. '</b></td></tr>';
	$CampoTimestamp2 = '<tr><td width="25%">Hora y fecha a registrar </td><td><b>'. date('h:i:s @ d/m/Y',$timestamp). '</b></td></tr>';
	$CampoUsuario = '<tr><td>Objeto Afectado:</td><td><b>'.ucfirst(strtolower($tipo_evento)).':'.$afectado . '</b></td></tr>';
	$CampoFoto = '<tr><td>Foto del evento:</td><td><input type="file" name="foto_evento" '.$OnChangePantalla.'></td></tr>';
	$CampoDescripcion ='<tr><td>Descripción:</td><td><input type="text" name="descripcion" maxlength="100" value="' . $descripcion. '"></td></tr>';
echo '
<form action="./?'._ACC_.'=gestionar+eventos" enctype="multipart/form-data" method="POST">
<table>
'.$CampoTipoEvento.'
'.$CampoAfectado.'
'.$CampoCodigoEvento.'
'.$CampoCodigoEvento2.'
'.$CampoTimestamp.'
'.$CampoTimestamp2.'
'.$CampoUsuario.'
'.$CampoConservarFoto.'
'.$CampoConservarFoto2.'
'.$CampoFoto.'
'.$CampoDescripcion.'
</table>
<input type="submit" value="'.$NombreBotonAccion.'">
'.$BotonCancelar.'
<input type="hidden" name="registrar_eventos" value="1">
</form>';
}
 
function Eventos_REGISTRAR() {
global $database,$form;
print_r ($_POST);
if ( !isset($_POST['ConservarFoto']) ) {
	/*
		Corroborar si ya tenia una imagen antes, para reutilizar la fila y a la vez
		que la imagen anterior no quede huerfana.
	*/
	$Pre_Id = isset($_POST['ConservarFoto2']) ? $_POST['ConservarFoto2'] : 0;
	$idImg = CargarImagenEnBD("foto_evento","EVENTOS", $Pre_Id);
} else {
	$idImg = $_POST['ConservarFoto'];
}
$q = "INSERT INTO ".TBL_EVENTS." ( id_evento, timestamp, categoria, afectado, descripcion_evento, foto_evento ) VALUES (" . $_POST['id_evento'] . ", '" . $_POST['timestamp'] . "', '". $_POST['tipo_evento']. "', '". $_POST['afectado']. "', '". $_POST['descripcion']. "', '". $idImg."')  ON DUPLICATE KEY UPDATE id_evento=VALUES(id_evento), timestamp=VALUES(timestamp), categoria=VALUES(categoria), afectado=VALUES(afectado), descripcion_evento=VALUES(descripcion_evento), foto_evento=VALUES(foto_evento);";
DEPURAR ($q);
//print_ar($_POST);
if ( $database->query($q) == 1 ) {
	echo Mensaje ("Exito al registrar el evento", _M_INFO);
} else {
	echo Mensaje ("Falló el registro el evento", _M_ERROR);
}
}
?>
