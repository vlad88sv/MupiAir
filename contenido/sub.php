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
	/* Verificamos si es permitido navegar al recurso pedido sin ser admin*/
	// Admin salta esta prueba claro :D
	if ( !$session->isAdmin() ) {
		//Restricciones para usuarios que no se han loggeado
		if ( !$session->logged_in ) {
			switch ( $accion ) {
				case "ayuda contacto": break;
				case "rpr clave": break;
				default:
				$accion= "ingresar";
			}
		}
	}
	$usuario = isset( $ACC[1] ) ? $ACC[1] : "";
	switch ( $accion ) {
	case "ingresar":
		CONTENIDO_usuario_ingresar();
		break;

	case "salir":
		$session->logout();
		header("Location: ./");
		break;

	case "ayuda contacto":
		CONTENIDO_ayuda_contacto() ;
		break;

	case "rpr clave":
		CONTENIDO_recuperar_clave();
		break;

	case "ver reportes":
		ADMIN_reportes();
		break;

	case "ver ubicaciones":
		CONTENIDO_mupis_ubicaciones($usuario);
		break;

	case "ver estadisticas":
		CONTENIDO_global_estadisticas($usuario);
		break;

	case "gestionar eventos":
	case "ver eventos":
			$evento = isset( $_GET['evento'] ) ? $_GET['evento'] : "";
			CONTENIDO_mupis_eventos($usuario, $evento);
			break;

/******************** Hasta aqui puede llegar un NO administrador ***************************/

	case "ver cliente":
		if($session->isAdmin()){
			CONTENIDO_usuario_info( $usuario );
			break;
		}

	case "listas":
		if($session->isAdmin()){
			$tipoDeLista= isset( $_GET['tipo'] ) ? $_GET['tipo'] : "";
			CONTENIDO_listas( $usuario, $tipoDeLista );
			break;
		}

	case "editar usuario":
		if($session->isAdmin()){
			CONTENIDO_usuario_editar( $usuario );
			break;
		}

	case "ver clientes":
	case "gestionar clientes":
		$NivelesPermitidos = array(ADMIN_LEVEL, SALESMAN_LEVEL);
		if (in_array($session->userlevel, $NivelesPermitidos)) {
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
		if($session->isAdmin()){
			$pedido = isset( $_GET['pedido'] ) ? $_GET['pedido'] : "";
			CONTENIDO_pedidos($usuario,$pedido);
			break;
		}

	case "registro":
		if($session->isAdmin()){
			CONTENIDO_usuario_registrar();
			break;
		}

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

	case "gestionar comentarios":
		if($session->isAdmin()){
			$id_comentario = isset( $_GET['comentario'] ) ? $_GET['comentario'] : "";
			CONTENIDO_comentarios($usuario, $id_comentario);
			break;
		}

	case "gestionar referencias":
		if($session->isAdmin()){
			$id_referencia = isset( $_GET['referencia'] ) ? $_GET['referencia'] : "";
			CONTENIDO_referencias($usuario, $id_referencia);
			break;
		}

	case "ver":
		if($session->isAdmin()){
			$id = isset( $ACC[1] ) ? $ACC[1] : "";
			echo '<h1>Mostrando imagen con Id. '.$id.'</h1>';
			echo '<center>'.CargarImagenDesdeBD($id).'</center>';
			break;
		}

	case "cargar pantallas":
		if($session->isAdmin()){
			CONTENIDO_cargar_pantallas();
			break;
		}

	default:
		CONTENIDO_global_404();
	}
}
function IMAGEN ( $ruta, $alt="", $width="", $height="" ) {
return '<img src="'. $ruta . '" style="max-width:'.$width.'; max-height:'.$height.'" alt="'. $alt .'" />';
}

function CONTENIDO_mostrar_logo_cliente() {
	global $session, $database;
	$NivelesPermitidos = array(ADMIN_LEVEL, SALESMAN_LEVEL, DEMO_LEVEL);
	if ( !in_array($session->userlevel, $NivelesPermitidos) && $session->logged_in ) {
		$q = "SELECT logotipo FROM ". TBL_USERS . " WHERE codigo='".$session->codigo."';";
		$result = $database->query($q);
		echo '<center>' . '<img src="include/ver.php?id='.mysql_result($result,0,"logotipo").'" />'. '</center><hr />';
	}
}

function INICIAR_MENUES () {
	global $session;
	switch ($session->userlevel) {
	case ADMIN_LEVEL:
	$s =
	'<div class="chromestyle" id="chromemenu" style="font-size:0.8em">
	<ul>'
	.'<li><a href="./">Inicio</a></li>'
	.'<li><a href="#" rel="menu_herramientas">Acciones</a></li>'
	.'<li>'.  CREAR_LINK_GET("registro","Registrar cliente", "Agregar un nuevo cliente al sistema") .'</li>'
	.'<li>'.  CREAR_LINK_GET("ver+ubicaciones","Ubicaciones", "Ver mapa de MUPIS") .'</li>'
	.'<li>'.  CREAR_LINK_GET("gestionar+pantallas","Gestionar pantallas", "Eliminar o modificar pantallas") .'</li>'
	.'<li>'.  CREAR_LINK_GET("gestionar+pedidos","Gestionar pedidos", "Eliminar o modificar pedidos") .'</li>'
	.'<li>'.  CREAR_LINK_GET("ver+reportes", "Reportes", "Generar reportes") .'</li>'
	.'<li><a href="./?accion=salir">Cerrar sesión</a></li>
	<li>&nbsp;&nbsp;&nbsp;&nbsp;</li>
	<li>&nbsp;&nbsp;&nbsp;</li>
	<li>&nbsp;&nbsp;</li>
	<li>&nbsp;</li>
	</ul>
	</div>
	';
	//Herramientas
	$s .= '
	<div id="menu_herramientas" class="dropmenudiv">'
	. CREAR_LINK_GET("cargar+pantallas", "Cargar Pantallas", "Cargar fotos enumeradas de pantallas")
	. CREAR_LINK_GET("gestionar+clientes","Editar Clientes", "Gestionar clientes")
	. CREAR_LINK_GET("gestionar+mupis","Editar Ubicaciones", "Eliminar o modificar MUPIS")
	. CREAR_LINK_GET("gestionar+calles","Editar Calles", "Eliminar o modificar calles")
	. CREAR_LINK_GET("gestionar+eventos","Editar Eventos", "Agregar, Eliminar o modificar eventos")
	. CREAR_LINK_GET("gestionar+referencias", "Editar Referencias", "Eliminar o modificar referencias de calle")
	. CREAR_LINK_GET("gestionar+comentarios", "Editar Comentarios", "Eliminar o modificar comentarios")
	.'</div>';
	break;

	case SALESMAN_LEVEL:
	$s =
	'
	<div class="chromestyle" id="chromemenu">
	<ul>
	<li><a href="./">Inicio</a></li>'
	.'<li>'.  CREAR_LINK_GET("ver+ubicaciones","Ubicaciones", "Ver mapa de MUPIS") .'</li>'
	.'<li>'.  CREAR_LINK_GET("ver+clientes","Clientes", "Ver lista de clientes") .'</li>'
	.'<li><a href="./?accion=salir">Cerrar sesión</a></li>
	<li>&nbsp;&nbsp;&nbsp;&nbsp;</li>
	<li>&nbsp;&nbsp;&nbsp;</li>
	<li>&nbsp;&nbsp;</li>
	<li>&nbsp;</li>
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
	<li>&nbsp;&nbsp;&nbsp;&nbsp;</li>
	<li>&nbsp;&nbsp;&nbsp;</li>
	<li>&nbsp;&nbsp;</li>
	<li>&nbsp;</li>
	</ul>
	</div>
	';
	break;

	case DEMO_LEVEL:
	$s =
	'
	<div class="chromestyle" id="chromemenu">
	<ul>
	<li><a href="./">Inicio</a></li>'
	.'<li>'.  CREAR_LINK_GET("ver+ubicaciones","Ubicaciones", "Ver mapa de MUPIS") .'</li>'
	.'<li><a href="./?accion=salir">Cerrar sesión</a></li>
	<li>&nbsp;&nbsp;&nbsp;&nbsp;</li>
	<li>&nbsp;&nbsp;&nbsp;</li>
	<li>&nbsp;&nbsp;</li>
	<li>&nbsp;</li>
	</ul>
	</div>
	';
	break;
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
	<li>&nbsp;&nbsp;&nbsp;&nbsp;</li>
	<li>&nbsp;&nbsp;&nbsp;</li>
	<li>&nbsp;&nbsp;</li>
	<li>&nbsp;</li>
	</ul>
	</div>
	';
	break;

	default:
	$s =
	'
	<div class="chromestyle" id="chromemenu">
	<ul>
	<li><a href="./?accion=salir">Cerrar sesión</a></li>
	</ul>
	</div>
	<p>
	Woops!, parece que Ud. esta inscrito en el sistema, pero su nivel de acceso es <b>'.$session->userlevel.'</b>.<br />
	Sin embargo no se alarme, el sistema solo a impedido su acceso para evitar riesgos de seguridad, todos los datos que haya tenido en su cuenta permenacerán intactos.<br />
	Por favor contacte con el administrador e indiquele que revise el <b>nivel de acceso</b> de su cuenta. Gracias.
	</p>
	';
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
	//echo $ParsedIMG;
	$q = "INSERT INTO ".TBL_IMG." (id_imagen, categoria, mime) VALUES(".$Id_Imagen.", '".$Categoria."', '".$_FILES[$NombreCampo]['type']."') ON DUPLICATE KEY UPDATE categoria=VALUES(categoria), mime=VALUES(mime);";
	$database->query($q);
	$insert_id = mysql_insert_id($database->connection);
	move_uploaded_file($_FILES[$NombreCampo]['tmp_name'],"img/$insert_id");
	return $insert_id;
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

function CargarImagenDesdeBD2 ($id, $height='100%') {
	return "<img style='height:".$height.";' src='include/ver.php?id=".$id."' />";
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
		return 'Error: no se definió el $tipo de mensaje';
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

function EnNulidad($string, $reemplazo){
	if (!$string) {
		return $reemplazo;
	}
	return $string;
}

function CONTENIDO_listas( $usuario, $tipoDeLista ){
	global $database;
	$q = "SELECT @codigo_mupi := (SELECT id_mupi FROM ".TBL_MUPI." as b WHERE a.codigo_mupi=b.id_mupi) as codigo_mupi, @codigo_mupi_traducido := (SELECT CONCAT((SELECT @ubicacion := b.ubicacion FROM emupi_calles AS b WHERE c.codigo_calle=b.codigo_calle), '. ', direccion , ' | ' , c.codigo_calle, '.' , @codigo_mupi_parcial := c.codigo_mupi ) FROM emupi_mupis as c WHERE c.id_mupi= @codigo_mupi) AS ubicacion, tipo_pantalla, id_pantalla FROM ".TBL_MUPI_FACES. " AS a WHERE catorcena = '".Obtener_catorcena_cercana()."' ORDER BY ubicacion, @codigo_mupi_parcial, tipo_pantalla";
	$result = $database->query($q);
	echo Query2Table($result);
}

function JS_($script){
    return "<script type='text/javascript'>".$script."</script>";
}

function JS_onload($script){
    return "<script type='text/javascript'>$(document).ready(function(){".$script."});</script>";
}
?>
