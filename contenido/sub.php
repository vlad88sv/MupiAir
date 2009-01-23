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

	case "gestionar pantallas":
		if($session->isAdmin()){
			$usuario = isset( $ACC[1] ) ? $ACC[1] : "";
			$pantalla= isset( $_GET['pantalla'] ) ? $_GET['pantalla'] : "";
			CONTENIDO_pantallas($usuario,$pantalla);
			break;
		}

	case "ver pedidos":
	case "gestionar pedidos":
			$usuario = isset( $ACC[1] ) ? $ACC[1] : "";
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
			$usuario = isset( $ACC[1] ) ? $ACC[1] : "";
			$mupi = isset( $_GET['mupi'] ) ? $_GET['mupi'] : "";
			CONTENIDO_mupis($usuario,$mupi);
			break;
		}

	case "ver reportes":
		ADMIN_reportes();
		break;
		
	case "ver ubicaciones":
		$usuario = isset( $ACC[1] ) ? $ACC[1] : "";
		CONTENIDO_mupis_ubicaciones($usuario);
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

function CONTENIDO_mostrar_logo() {
	//echo '<center>' . IMAGEN("./logo.gif") . '</center>';
	echo IMAGEN("./logo.gif");
}

function CONTENIDO_mostrar_logo_cliente() {
	global $session;
	if ( $session->isAdmin() && $session->logged_in ) {
		echo '<center>' . IMAGEN("./logo_generico.gif") . '</center>';
	}
}

function INICIAR_MENUES () {
	global $session;
	if ( $session->isAdmin() ) {
	$s =
	'
	<div class="chromestyle" id="chromemenu">
	<ul>
	<li><a href="./">Inicio</a></li>
	<li><a href="#" rel="menu_herramientas">Herramientas</a></li>
	<li><a href="./?accion=salir">Cerrar sesión administrativa</a></li>	
	</ul>
	</div>
	';
	//Herramientas
	$s .= '
	<div id="menu_herramientas" class="dropmenudiv" style="width: 150px;">'
	. CREAR_LINK_GET("registro","Registrar cliente", "Agregar un nuevo cliente al sistema")
	. CREAR_LINK_GET("gestionar+clientes","Gestionar clientes", "Gestionar clientes")
	. CREAR_LINK_GET("gestionar+pedidos","Gestionar pedidos", "Eliminar o modificar pedidos")
	. CREAR_LINK_GET("gestionar+pantallas","Gestionar pantallas", "Eliminar o modificar pantallas")
	. CREAR_LINK_GET("gestionar+mupis","Gestionar MUPIS", "Eliminar o modificar MUPIS")
	. CREAR_LINK_GET("ver+ubicaciones","Gestionar ubicaciones", "Agregar, Eliminar o modificar ubicaciones")
	. CREAR_LINK_GET("ver+eventos","Gestionar eventos", "Agregar, Eliminar o modificar eventos")
	. CREAR_LINK_GET("ver+estadisticas", 'Estadísticas', "Ver estadísticas administrativas")
	.'</div>';
	} else {
	$s =
	'
	<div class="chromestyle" id="chromemenu">
	<ul>
	<li><a href="./?accion=ver+estadisticas">Estadísticas</a></li>
	<li><a href="./?accion=ver+ubicaciones">Mis Ubicaciones</a></li>
	<li><a href="./?accion=ver+eventos" >Eventos</a></li>	
	<li><a href="./?accion=ayuda+contacto">Comenta</a></li>	
	<li><a href="./?accion=ver+reportes" rel="menu_reportes">Reportes</a></li>	
	<li><a href="./?accion=salir">Cerrar sesión</a></li>	
	</ul>
	</div>
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
?>
