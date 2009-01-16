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
	echo '<h1>'. ($database->num_active_users + $database->num_active_guests) . ' usuario(s) en línea </h1>';
	echo 'Clientes: ' . $database->num_active_users . "<br />";
	echo 'Visitantes: ' . $database->num_active_guests  . "<br />" . '<hr />';
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
	echo'</ul>';
}

function CONTENIDO_mostrar_principal() {
	global $session;
	if ( isset( $_GET[_ACC_]) ) {
		$ACC = explode(":",$_GET[_ACC_]);
		if ( isset( $ACC[0] ) ) { $accion = $ACC[0]; } 
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

	switch ( $accion ) {
	case "ver cliente":
		$usuario = isset( $ACC[1] ) ? $ACC[1] : "";
		CONTENIDO_usuario_info( $usuario );
		break;
	
	case "editar usuario":
		$usuario = isset( $ACC[1] ) ? $ACC[1] : "";
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
		CONTENIDO_mupis();
		break;

	case "ver reportes":
		ADMIN_reportes();
		break;
		
	case "ver ubicaciones":
		CONTENIDO_mupis_ubicaciones();
		break;
		
	case "ver eventos":
		CONTENIDO_mupis_eventos();
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
		
	default:
		CONTENIDO_global_404();
	}
}
function IMAGEN ($ruta, $alt="") {
return '<img src="'. $ruta . '" alt="'. $alt .'" />';
}

function CONTENIDO_mostrar_logo($cliente="") {
global $session;
if ( $session->logged_in && !$session->isAdmin() ) {
	echo IMAGEN("./logo_generico.gif");
} else {
	echo IMAGEN("./logo.gif");
}
}

function INICIAR_MENUES () {
	global $session;
	$s = "";
	$optEstado =  $session->isAdmin() ? '<li><a href="#" rel="menu_herramientas">Herramientas</a></li>' : "";
	$s =
	'
	<div class="chromestyle" id="chromemenu">
	<ul>
	<li><a href="./">Inicio</a></li>
	<li><a href="#" rel="menu_cliente">Cliente</a></li>
	<li><a href="#" rel="menu_informacion">Información</a></li>'
	. $optEstado
	. '<li><a href="#" rel="menu_ayuda">Ayuda</a></li>	
	</ul>
	</div>
	';
	//Menú cliente
	if ( $session->logged_in ) {
		$optEstado = CREAR_LINK_GET("ver+cliente", 'Mi Cuenta', "Ver los datos de su perfil")
		. CREAR_LINK_GET("ver+ubicaciones", 'Mis ubicaciones', "Ver las ubicaciones de sus MUPIS")
		. CREAR_LINK_GET("ver+estadisticas", 'Estadísticas', "Ver estadísticas de sus MUPIS")
		. CREAR_LINK_GET("ver+eventos", 'Eventos', "Ver los eventos de sus MUPIS")
		. CREAR_LINK_GET("ver+reportes", 'Reportes', "Generar reportes sobres sus MUPIS")
		. CREAR_LINK("include/x.php","Cerrar sesión", "Salir del sistema");
	} else {
		$optEstado = CREAR_LINK_GET("ingresar", "Iniciar sesión", "Puede ingresar al sistema si ya esta registrado como cliente o administrador");
	}
	$s .= '
	<div id="menu_cliente" class="dropmenudiv">'
	.$optEstado
	.'</div>';

	//Información
	$s .= '
	<div id="menu_informacion" class="dropmenudiv" style="width: 150px;">'
	.CREAR_LINK_GET("info+que", "¿Qué es?", "Que es " . _NOMBRE_)
	.CREAR_LINK_GET("info+precios", "Precios", "Precios de " . _NOMBRE_)
	.CREAR_LINK_GET("info+servicios", "Servicios", "Precios de " . _NOMBRE_)
	.CREAR_LINK_GET("info+creativo", "Creativo", "Servicios de " . _NOMBRE_)
	.CREAR_LINK_GET("info+detalles", "Detalles", "Detalles de " . _NOMBRE_)
	.CREAR_LINK_GET("info+contacto", "Contacto", "Contactar con " . _NOMBRE_)
	.'</div>';
	
	//Herramientas
	if ( $session->isAdmin() ) {
	$s .= '
	<div id="menu_herramientas" class="dropmenudiv" style="width: 150px;">'
	. CREAR_LINK_GET("registro","Registrar cliente", "Agregar un nuevo cliente al sistema")
	. CREAR_LINK_GET("gestionar+clientes","Gestionar clientes", "Gestionar clientes")
	. CREAR_LINK_GET("gestionar+mupis","Gestionar MUPIS", "Eliminar o modificar MUPIS")
	. CREAR_LINK_GET("ver+ubicaciones","Gestionar ubicaciones", "Agregar, Eliminar o modificar ubicaciones")
	. CREAR_LINK_GET("ver+eventos","Gestionar eventos", "Agregar, Eliminar o modificar eventos")
	. CREAR_LINK_GET("ver+estadisticas", 'Estadísticas', "Ver estadísticas administrativas")
	.'</div>';
	}
	
	// Ayuda
	$s .= '
	<div id="menu_ayuda" class="dropmenudiv" style="width: 150px;">'
	. CREAR_LINK_GET("ayuda+contacto", "Dudas y comentarios", "Si desea comentar algo o tiene dudas al respecto de nuestro servicio")
	. CREAR_LINK_GET("info+nosotros", "Acerca de...", "Acerca de Eco Mupis y CEPASA C.V.")
	.'</div>';
	
	// Finalmente iniciamos el script.
	$s .= '<script type="text/javascript">cssdropdown.startchrome("chromemenu")</script>';
	return $s;
}
?>
