<?php
/*Crear un link HTML*/
function CREAR_LINK($sAccion, $sTexto, $sTitulo) {
	return "<a href=\"$sAccion\" title=\"$sTitulo\">$sTexto</a>";
}

/*Crear un link apropiado para GET*/
function CREAR_LINK_GET($sAccion, $sTexto, $sTitulo) {
	return "<a href=\"?"._ACC_."=$sAccion\" title=\"$sTitulo\">$sTexto</a>";
}

function CONTENIDO_en_linea(){
	global $session, $database;
	echo ($database->num_active_users + $database->num_active_guests) . ' usuario(s) en línea: ';
	echo $database->num_active_users . ' Cliente(s) y ';
	echo $database->num_active_guests . ' Visitante(s)';
	echo "<ul>";
	$q = "SELECT codigo FROM " . TBL_ACTIVE_USERS . " ORDER BY timestamp DESC,codigo";
	//echo $q;
	$result = $database->query($q);
	$num_rows = mysql_numrows($result);	
	DEPURAR($num_rows);
	if($num_rows > 0){
	   for($i=0; $i<$num_rows; $i++){
	      $uname = mysql_result($result,$i,"codigo");
				echo CREAR_LINK_GET("ver+cliente:$uname","<li>" . $uname . "</li>" , "Ver la información de este cliente");
	   }
	}
	echo "</ul>";
}

function CONTENIDO_mostrar_principal() {
	global $session;
	
	CONTENIDO_mostrar_logo_cliente();
	
	if ( isset( $_GET[_ACC_]) ) {
		$ACC = explode(":",$_GET[_ACC_]);
		if ( isset( $ACC[0] ) ) { $accion = urldecode($ACC[0]); } 
	} else {
		$accion = "ingresar";
	}
	/* Verificamos si es permitido  ver el sitio sin estar registrado, si no forzamor a ir al registro*/
	if ( !$session->logged_in ) {
		switch ( $accion ) {
		case "ayuda contacto": break;
		case "rpr clave": break;
		case "info que": break;
		case "info precios": break;
		case "info servicios": break;
		case "info creativo": break;
		case "info detalles": break;
		case "info contacto": break;
		default: 
		$accion= "ingresar";
		}
	}
	$usuario = isset( $ACC[1] ) ? $ACC[1] : "";
	switch ( $accion ) {
	case "ver cliente":
		CONTENIDO_usuario_info( $usuario );
		break;
	
	case "editar usuario":
		CONTENIDO_usuario_editar( $usuario );
		break;
	case "rpr clave":
		CONTENIDO_recuperar_clave();
		break;
		
	case "gestionar clientes":
		if($session->isAdmin()){
			CONTENIDO_admin();
			break;
		}

	case "gestionar pantallas":
		if($session->isAdmin()){
			$pantalla= isset( $_GET['id'] ) ? $_GET['id'] : "";
			$catorcena= isset( $_GET['catorcena'] ) ? $_GET['catorcena'] : "";
			$calle= isset( $_GET['calle'] ) ? $_GET['calle'] : "";
			CONTENIDO_pantallas($usuario,$pantalla,$catorcena,$calle);
			break;
		}

	case "ver pedidos":
	case "gestionar pedidos":
			$pedido = isset( $_GET['pedido'] ) ? $_GET['pedido'] : "";
			CONTENIDO_pedidos($usuario,$pedido);
			break;

	case "registro":
		if($session->isAdmin()){
			CONTENIDO_usuario_registrar();
			break;
		}

	case "ingresar":
		CONTENIDO_usuario_ingresar();
		break;

	case "ayuda contacto":
		CONTENIDO_ayuda_contacto() ;
		break;
		
	case "gestionar mupis":
		if($session->isAdmin()){
			$mupi = isset( $_GET['mupi'] ) ? $_GET['mupi'] : "";
			$calle = isset( $_GET['calle'] ) ? $_GET['calle'] : NULL;
			CONTENIDO_mupis($usuario,$mupi,$calle);
			break;
		}

	case "gestionar calles":
		if($session->isAdmin()){
			$calle = isset( $_GET['calle'] ) ? $_GET['calle'] : "";
			CONTENIDO_calles($usuario,$calle);
			break;
		}

	case "ver reportes":
		ADMIN_reportes();
		break;
		
	case "ver ubicaciones":
		CONTENIDO_mupis_ubicaciones($usuario);
		break;
	
	case "gestionar eventos":	
	case "ver eventos":
		$evento = isset( $_GET['evento'] ) ? $_GET['evento'] : "";
		CONTENIDO_mupis_eventos($usuario, $evento);
		break;
		
	case "ver estadisticas":
		CONTENIDO_global_estadisticas();
		break;
		
	case "info contacto":
		CONTENIDO_mupis_contacto();
		break;
		
	case "info creativo":
		CONTENIDO_mupis_creativo();
		break;

	case "info que":
		CONTENIDO_mupis_info();
		break;

	case "info precios":
		CONTENIDO_mupis_precios();
		break;
		
	case "info servicios":
		CONTENIDO_mupis_servicios();
		break;
		
	case "info creativo":
		CONTENIDO_mupis_creativo();
		break;
		
	case "info detalles":
		CONTENIDO_mupis_detalle();
		break;
		
	case "info nosotros":
		CONTENIDO_global_info();
		break;
		
	case "gestionar comentarios":
		$id_comentario = isset( $_GET['comentario'] ) ? $_GET['comentario'] : "";
		CONTENIDO_comentarios($usuario, $id_comentario);
		break;
	
	case "gestionar referencias":
		$id_referencia = isset( $_GET['referencia'] ) ? $_GET['referencia'] : "";
		CONTENIDO_referencias($usuario, $id_referencia);
		break;
		
	case "salir":
		$session->logout();
		header("Location: ./");
		break;
	
	case "ver":
		$id = isset( $ACC[1] ) ? $ACC[1] : "";
		echo '<h1>Mostrando imagen con Id. '.$id.'</h1>';
		echo '<center>'.CargarImagenDesdeBD($id).'</center>';
		break;
		
	default:
		CONTENIDO_global_404();
	}
}
function IMAGEN ( $ruta, $alt="", $width="", $height="" ) {
return '<img src="'. $ruta . '" style="max-width:'.$width.'; max-height:'.$height.'" alt="'. $alt .'" />';
}

function CONTENIDO_mostrar_logo() {
	//echo '<center>' . IMAGEN("./logo.gif") . '</center>';
	echo IMAGEN("./logo.gif", "Logotipo Eco Mupis", "450px", "100px");
}

function CONTENIDO_mostrar_logo_cliente() {
	global $session, $database;
	if ( !$session->isAdmin() && $session->logged_in ) {
		$q = "SELECT logotipo FROM ". TBL_USERS . " WHERE codigo='".$session->codigo."';";
		$result = $database->query($q);		
		echo '<center>' . CargarImagenDesdeBD(mysql_result($result,0,"logotipo"),'50px') . '</center><hr />';
		//echo IMAGEN("./logo_generico.gif");
	}
}

function INICIAR_MENUES () {
	global $session;
	switch ($session->userlevel) {
	case ADMIN_LEVEL:
	$s =
	'
	<div class="chromestyle" id="chromemenu">
	<ul>
	<li><a href="./">Inicio</a></li>
	<li><a href="#" rel="menu_herramientas">Editar</a></li>'
	.'<li>'.  CREAR_LINK_GET("registro","Registrar cliente", "Agregar un nuevo cliente al sistema") .'</li>'
	.'<li>'.  CREAR_LINK_GET("ver+ubicaciones","Ubicaciones", "Ver mapa de MUPIS") .'</li>'
	.'<li>'.  CREAR_LINK_GET("gestionar+pantallas","Gestionar pantallas", "Eliminar o modificar pantallas") .'</li>'
	.'<li>'.  CREAR_LINK_GET("gestionar+pedidos","Gestionar pedidos", "Eliminar o modificar pedidos") .'</li>'
	.'<li>'.  CREAR_LINK_GET("ver+reportes", "Reportes", "Generar reportes") .'</li>'
	.'<li><a href="./?accion=salir">Cerrar sesión</a></li>	
	</ul>
	</div>
	';
	//Herramientas
	$s .= '
	<div id="menu_herramientas" class="dropmenudiv" style="width: 150px;">'
	. CREAR_LINK_GET("gestionar+clientes","Clientes", "Gestionar clientes")
	. CREAR_LINK_GET("gestionar+mupis","Eco Mupis", "Eliminar o modificar MUPIS")
	. CREAR_LINK_GET("gestionar+calles","Calles", "Eliminar o modificar calles")
	. CREAR_LINK_GET("gestionar+eventos","Eventos", "Agregar, Eliminar o modificar eventos")
	. CREAR_LINK_GET("gestionar+referencias", "Referencias", "Eliminar o modificar referencias de calle")
	. CREAR_LINK_GET("gestionar+comentarios", "Comentarios", "Eliminar o modificar comentarios")
	.'</div>';
	break;
	
	case SALESMAN_LEVEL:
	$s =
	'
	<div class="chromestyle" id="chromemenu">
	<ul>
	<li><a href="./">Inicio</a></li>'
	.'<li>'.  CREAR_LINK_GET("ver+ubicaciones","Ubicaciones", "Ver mapa de MUPIS") .'</li>'
	.'<li><a href="./?accion=salir">Cerrar sesión</a></li>	
	</ul>
	</div>
	';
	break;
	
	CASE CLIENT_LEVEL:
	$s =
	'
	<div class="chromestyle" id="chromemenu">
	<ul>
	<li><a href="./">Estadísticas</a></li>
	<li><a href="./?accion=ver+ubicaciones">Mis Ubicaciones</a></li>
	<li><a href="./?accion=ver+eventos" >Eventos</a></li>	
	<li><a href="./?accion=ayuda+contacto">Comenta</a></li>	
	<li><a href="./?accion=ver+reportes">Reportes</a></li>	
	<li><a href="./?accion=salir">Cerrar sesión</a></li>	
	</ul>
	</div>
	';
	break;
	
	case USER_LEVEL:
	$s =
	'
	<div class="chromestyle" id="chromemenu">
	<ul>
	<li><a href="./" onclick="return false">Estadísticas</a></li>
	<li><a href="./?accion=ver+ubicaciones">Mis Ubicaciones</a></li>
	<li><a href="./" onclick="return false">Eventos</a></li>	
	<li><a href="./?accion=ayuda+contacto">Comenta</a></li>	
	<li><a href="./" onclick="return false"">Reportes</a></li>	
	<li><a href="./?accion=salir">Cerrar sesión</a></li>	
	</ul>
	</div>
	';
	break;
	}
	
	// Finalmente iniciamos el script.
	$s .= '<script type="text/javascript">cssdropdown.startchrome("chromemenu")</script>';
	return $s;
}

function Query2Table($result, $tableFeatures="") {
 $table = "";
 $table .= "<table $tableFeatures>\n\n";
 $noFields = mysql_num_fields($result);
 $table .= "<tr>\n";
 for ($i = 0; $i < $noFields; $i++) {
 $field = mysql_field_name($result, $i);
 $table .= "\t<th>$field</th>\n";
 }
 while ($r = mysql_fetch_row($result)) {
 $table .= "<tr>\n";
 foreach ($r as $column) {
 $table .= "\t<td>$column</td>\n";
 }
 $table .= "</tr>\n";
 }
 $table .= "</table>\n\n";
 return $table;
 }
 
 function AnularFechaNula ($time,$EnVacioHoy=false) {
 if ( $EnVacioHoy ) { $vacio = date("d-m-Y"); } else { $vacio = ""; }
 if ( $time ) { return date("d-m-Y", $time); } else { return $vacio; }
 }
 function SCRIPT ($Script) {
       return '<script  type="text/javascript">$(document).ready(function (){'.$Script.'});</script>';
}
function CargarImagenEnBD ($NombreCampo, $Categoria, $Id_Imagen = 0) {
global $database;
/*
Verificamos que exista la superglobal $_FILES para el indice del supuesto campo INPUT=FILE para no trabajar de gusto...
*/
//print_ar($_FILES);
if ( !$_FILES[$NombreCampo]['error'] ) {
	$ParsedIMG = mysql_real_escape_string(file_get_contents($_FILES[$NombreCampo]['tmp_name']));
	//echo $ParsedIMG;
	$q = "INSERT INTO ".TBL_IMG." (id_imagen, data, categoria, mime) VALUES(".$Id_Imagen.", '".$ParsedIMG."', '".$Categoria."', '".$_FILES[$NombreCampo]['type']."') ON DUPLICATE KEY UPDATE data=VALUES(data), categoria=VALUES(categoria), mime=VALUES(mime);";
	$database->query($q);
	return mysql_insert_id($database->connection);
} else {
	/*
		Ok, si no esta establecida ninguna imagen y nos dieron y $Id_Imagen es porque quieren eliminarla.
		* Eliminamos los datos de esa fila para recuperar el espacio.
		* Retornamos NULL para denotar la nueva anti-referencia.
	*/
	if ( $Id_Imagen ) {
		$q = "DELETE FROM ".TBL_IMG." WHERE id_imagen=".$Id_Imagen.";";
		$database->query($q);
	}
}
return NULL;
}

function CargarImagenDesdeBD ($id, $height='100%') {
	return '<img style="height:'.$height.';" src="include/ver.php?id='.$id.'" />';
}

function GenerarTooltip ($texto) {
	return ' onMouseOver=\'toolTip("'.addslashes($texto).'")\' onMouseOut="toolTip()" ';
}

function Mensaje ($texto, $tipo=_M_INFO){
	switch ( $tipo ) {
		case _M_INFO:
		$id = "info";
		break;
		case _M_ERROR:
		$id = "error";
		break;
		case _M_NOTA:
		$id = "nota";
		break;
		default:
		echo 'Error: no se definió el $tipo de mensaje';
	}
	
	return "<div id=\"$id\">".$texto."</div>";
	
}

function truncate($string, $max = 120, $replacement = '...')
{
    if (strlen($string) <= $max)
    {
        return $string;
    }
    $leave = $max - strlen ($replacement);
    return substr_replace($string, $replacement, $leave);
}
?>
