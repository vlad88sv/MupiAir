<?php
error_reporting(E_STRICT | E_ALL);
ob_start("ob_gzhandler"); 
date_default_timezone_set ('America/El_Salvador');
require_once('../include/const.php');
require_once('../include/sesion.php');
require_once('../include/fecha.php');
require_once('sub.php');
require_once('../include/maps/GoogleMapAPI.class.php');
$map = new GoogleMapAPI;

if ( isset( $_GET['accion'] ) ) {
	switch ( $_GET['accion'] ) {
	
	case "drag":
		if ( isset( $_GET['id'] ) && isset( $_GET['lat'] ) && isset( $_GET['lng'] )) {
		
			$parte = explode ('|',$_GET['id'] ); 
			if ( count($parte) == 3 ) {
				
				if ( $parte[0] == "REF" ) {
				//retornar ("Referencia?: " . "REF". ", Catorcena: ". $parte[1]. ", id_referencia:".$parte[2]);
				retornar ( actualizarReferencia ($parte[2], $_GET['lat'], $_GET['lng']));
				} else {
				//retornar ("Mupi: " . $parte[0]. ", Catorcena: ". $parte[1]. ", Usuario:".$parte[2]);
				retornar ( actualizarCoords ($parte[0], $_GET['lat'], $_GET['lng']));
				}
			}
		} else {
			retornar ( "Ud. esta utilizando incorrectamente este script de soporte. [DRAG]" );
		}
	
	break;
	
	case "mupi":
		if ( isset( $_GET['MUPI'] ) ) {
		
			$parte = explode ('|',$_GET['MUPI'] ); 
			if ( $parte[0] == "REF" ) { retornar("Se ha seleccionado la referencia " .CREAR_LINK_GET("gestionar+referencias&referencia=". $parte[2], $parte[2], "Abre el dialogo de gestión para la referencia seleccionada")); } 
			if ( count($parte) == 3 ) {
				//retornar ("Mupi: " . $parte[0]. ", Catorcena: ". $parte[1]. ", Usuario:".$parte[2]);
				retornar ( Buscar ($parte[0], $parte[1], $parte[2] ) );
			}
		} else {
			retornar ( "Ud. esta utilizando incorrectamente este script de soporte. 1" );
		}
		break;
	case "calles":
		if ( isset( $_GET['catorcena'] ) && isset ( $_GET['usuario'] ) ) {
/*
			$script = SCRIPT('
			// $("#combo_calles").change(funcion_combo_calles());
			');
*/
			$Boton_combo_calles = '<input type="button" OnClick="funcion_combo_calles()" value="Mostrar Mapa">';;
			retornar ('<b>Ver Calle:</b><br />' . $database->Combobox_CallesConPresencia("combo_calles",$_GET['usuario'],$_GET['catorcena']).$Boton_combo_calles);
		} else {
			retornar ( "Ud. esta utilizando incorrectamente este script de soporte. 2" );
		}
		break;
	case "mapas":
		if ( isset( $_GET['catorcena'] ) && isset( $_GET['calle'] ) && isset ( $_GET['usuario'] ) ) {
			retornar (Mostrar_Mapa($_GET['catorcena'], $_GET['calle'], $_GET['usuario']));
		} else {
			retornar ( "Ud. esta utilizando incorrectamente este script de soporte. 3" );
		}	
		break;
	case "mupis":
		if ( isset( $_GET['catorcena'] ) && isset( $_GET['calle'] ) ) {
			retornar (Mostrar_Mapa($_GET['catorcena'], $_GET['calle'], ""));
		} else {
			retornar ( "Ud. esta utilizando incorrectamente este script de soporte. 3" );
		}	
	}
} else {
		retornar ( "Ud. esta utilizando incorrectamente este script de soporte. 0" );
}

function retornar($texto) {
	exit ('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />' . $texto . '<br />');
}

// Buscar () - Encuentra las caras que pertenecen a $codigo_mupi (realmente id_mupi)
function Buscar ($codigo_mupi, $catorcena, $usuario) {
   global $session;
   $link = @mysql_connect(DB_SERVER, DB_USER, DB_PASS) or die('Por favor revise sus datos, puesto que se produjo el siguiente error:<br /><pre>' . mysql_error() . '</pre>');
   mysql_select_db(DB_NAME, $link) or die('!->La base de datos seleccionada "'.$DB_base.'" no existe');
   if ( time() > $catorcena ) { $tCatorcena=$catorcena; } else { $tCatorcena=Obtener_catorcena_anterior($catorcena); }
   if ( ($session->isAdmin() || $session->userlevel == SALESMAN_LEVEL || $session->userlevel == DEMO_LEVEL || $session->userlevel == USER_LEVEL) && !$usuario) {
	$q = "select tipo_pantalla, foto_real, (SELECT foto_pantalla FROM emupi_mupis_pedidos as b where a.codigo_pedido=b.codigo_pedido) AS arte from emupi_mupis_caras as a where catorcena=$catorcena AND codigo_mupi = (SELECT id_mupi FROM emupi_mupis WHERE id_mupi=$codigo_mupi);";
   } else {
	$q = "select tipo_pantalla, foto_real, (SELECT foto_pantalla FROM emupi_mupis_pedidos as b where a.codigo_pedido=b.codigo_pedido) AS arte from emupi_mupis_caras as a where catorcena=$tCatorcena AND codigo_pedido IN (SELECT codigo_pedido FROM emupi_mupis_pedidos where codigo='$usuario') AND codigo_mupi = (SELECT id_mupi FROM emupi_mupis WHERE id_mupi=$codigo_mupi);";
   }
   $result = @mysql_query($q, $link) or retornar ('!->Ocurrió un error mientras se revisaba la disponibilidad del MUPI.');
   /* Error occurred, return given name by default */
   $num_rows = mysql_numrows($result);
   if(!$result || ($num_rows < 0)){
      retornar("Error mostrando la información");
   }
 
   if($num_rows == 0){
      retornar (Mensaje("¡No hay datos para ese código ($codigo_mupi)!",_M_ERROR));
   }
   // =====================Hasta acá la BD================================= //

   // ===================================================================== //
   // Empezamos a recorrer las caras encontradas
   $tipoPantalla = $datosLinksGlobo = '';
   for($i=0; $i<$num_rows; $i++){
	   
	$arte = mysql_result($result,$i,"arte");
	$tipo_pantalla  = mysql_result($result,$i,"tipo_pantalla");
	$foto_real = mysql_result($result,$i,"foto_real");
	// si es par es vehicular
	$tipoPantalla = ($tipo_pantalla % 2) == 0 ? 'vehicular' : 'peatonal';
	// Botón de cerrar para el BlockUI.
	// $datosUI .= '<a onclick=\'$.unblockUI()\'>Cerrar</a><hr />';
	// Admin, Vendedor y Demas al presionar sobre el mupi podrán ver
	// el logo de las compañias establecidas en ese punto y ademas
	// tendrán los enlaces de Ver imagen vehicular/peatonal en el pie del globo
	$NivelesPermitidos = array(ADMIN_LEVEL, SALESMAN_LEVEL, DEMO_LEVEL);

	// Si es catorcena futura y no es Administrador, ni Vendedor ni Demo.
	if ( time() < $catorcena && !in_array($session->userlevel, $NivelesPermitidos) ) {
		$datosUI[$tipoPantalla] .= "<span style='display:none'><center><strong>Imagen actual de cara ".$tipoPantalla.":</strong></center>".
		"<center>Viendo catorcena futura, la fotografía mostrada es ilustrativa y corresponde al mupi seleccionado en la catorcena presente.<br /><br />" . '<img src="include/ver.php?id='.$foto_real.'" />' . "</center>".
	    "<center><strong>Arte digital de campaña:</strong></center>".
		"<center>Viendo catorcena futura, Arte no disponible</center></span>";
	} else {
		$datosUI[$tipoPantalla] = "<center><strong>Imagen actual de cara ".$tipoPantalla.":</strong></center>".
		"<center>" . "<img src='include/ver.php?id=" . $foto_real . "' />" . "</center>".
		"<center><strong>Arte digital de campaña:</center>".
		"<center>" . "<img src='include/ver.php?id=".$arte."' />" . "</strong></center>";
	} // Fin de procesado de de $datosUI

	$datosCaja = "$.jGrowl('".addslashes($datosUI[$tipoPantalla])."',{ 
					theme: 'smoke',
					sticky: true,
					closer: false})";
	$datosLinksGlobo .= "<center><a onclick=\"$datosCaja\">Ver imagen de cara ".$tipoPantalla."</a></center>";

   } // Fin del recorrido de datos.
retornar($datosLinksGlobo);
}

function Mostrar_Mapa($catorcena, $calle, $usuario){
global $session, $map, $database;
$NivelesPermitidos = array(ADMIN_LEVEL, SALESMAN_LEVEL, DEMO_LEVEL, USER_LEVEL);
// setup database for geocode caching
$map->setDSN('mysql://'.DB_USER.':'.DB_PASS.'@'.DB_SERVER.'/'.DB_NAME);
//Google Map Key
$map->setAPIKey(GOOGLE_MAP_KEY);
// proporción de la ventana que tomará el mapa.
$map->setWidth('100%');
$map->referencias = false;
// Desactivar los controles que solo Admin puede tener.
if ( !$session->isAdmin() ) {
	$map->map_controls = false;
	$map->disable_map_drag = true;
	$map->disable_drag = true;
	$map->Mostrar_Contenido_Maximizado = false;
}
// Desactivarel globito que solo Admin, Vendedor, Demo y Usuario pueden ver.
if ( !in_array($session->userlevel,$NivelesPermitidos) ) {
	$map->disableInfoWindow();
}
// Cargar puntos mupis.
$WHERE_USER = "";
if ( $calle == "::T::") {
	$grupo_calle = "";
	$t_grupo_calle = "";
	$map->disable_map_drag = false;
} else {
if ( strpos($calle, "G:") !== false ) {
	$Explotado = @end(explode(":",$calle));
	$grupo_calle = "codigo_calle IN (SELECT codigo_calle FROM ".TBL_STREETS." WHERE grupo_calle='".$Explotado."')";
	$map->disable_map_drag = false;
} else {
	$grupo_calle = "codigo_calle='$calle'";
}
}

	if ( isset($_GET['sin_presencia']) ) {
		// Ver por Mupis
		if ($grupo_calle) $t_grupo_calle = " where $grupo_calle";
		$q = "select id_mupi, codigo_mupi, direccion, foto_generica, lon, lat, codigo_evento, codigo_calle FROM emupi_mupis AS a$t_grupo_calle;";
	} else {
		// Por Presencia y sin usuario
	if ( (($session->isAdmin() || $session->userlevel == SALESMAN_LEVEL || $session->userlevel == USER_LEVEL) && !$usuario) || $session->userlevel == DEMO_LEVEL) {
		// Siendo Admin, Vendedor, Usuario o Demo
		if ($grupo_calle) $t_grupo_calle = " and $grupo_calle";
		$q = "select id_mupi, codigo_mupi, direccion, foto_generica, lon, lat, codigo_evento, codigo_calle FROM emupi_mupis AS a WHERE id_mupi IN (select codigo_mupi FROM emupi_mupis_caras WHERE catorcena=$catorcena)$t_grupo_calle;";
	} else {
		// Siendo cualquier otro nivel o con usuario
		if ($grupo_calle) $t_grupo_calle = "$grupo_calle and ";
		$q = "select id_mupi, codigo_mupi, direccion, foto_generica, lon, lat, codigo_evento, codigo_calle, (SELECT logotipo from emupi_usuarios where codigo='$usuario') as logotipo from emupi_mupis where $t_grupo_calle id_mupi IN (select codigo_mupi FROM emupi_mupis_caras WHERE catorcena=$catorcena AND codigo_pedido IN (SELECT codigo_pedido FROM emupi_mupis_pedidos WHERE codigo='$usuario'));";
	}
	}
   DEPURAR($q,0);
   $result = $database->query($q);
   $n_mupis = $num_rows = mysql_numrows($result);
   if(!$result || ($num_rows < 0)){
      exit ( "Error mostrando la información<br />");
   }
   
   if($num_rows == 0){
      exit ("¡No hay "._NOMBRE_." ingresados!<br />");
   }
	//Imagen de los marcadores
	//Removido por petición. 06/02/09
	//if ( !$session->isAdmin() || $usuario ) $map->setMarkerIcon('http://'.$_SERVER['SERVER_ADDR'].'/mupi/include/ver.php?id='.mysql_result($result,0,"logotipo"),'',0,0,0,0);


   // Recorrer todos los mupis.
   $n_caras_p = $n_caras_v = $n_caras = 0;
   for($i=0; $i<$num_rows; $i++){
      $id_mupi  = mysql_result($result,$i,"id_mupi");
      $codigo_mupi  = mysql_result($result,$i,"codigo_calle") . "." .mysql_result($result,$i,"codigo_mupi");
      $direccion = truncate(mysql_result($result,$i,"direccion"));
      $foto_generica = mysql_result($result,$i,"foto_generica");
      $lon  = mysql_result($result,$i,"lon");
      $lat  = mysql_result($result,$i,"lat");
      $codigo_evento = mysql_result($result,$i,"codigo_evento");
		if ( ($session->isAdmin() || $session->userlevel == SALESMAN_LEVEL || $session->userlevel == USER_LEVEL) ) {
			$q = "SELECT DISTINCT logotipo FROM emupi_usuarios where codigo IN (SELECT codigo from emupi_mupis_pedidos where codigo_pedido IN (SELECT codigo_pedido FROM emupi_mupis_caras as b WHERE catorcena=$catorcena AND b.codigo_mupi=".mysql_result($result,$i,"id_mupi")."));";
			//echo $q."<br>";
			$result2 = $database->query($q);
			$num_rows2 = mysql_numrows($result2);
			$logotipo = "<br />";
			
			if($num_rows2 > 0){
				   for($ii=0; $ii<$num_rows2; $ii++){
					   $logotipo .= CargarImagenDesdeBD(mysql_result($result2,$ii,"logotipo"), "50px");
				   }
			}
		} else {
			$logotipo = ''; //"<br />".CargarImagenDesdeBD(mysql_result($result,$i,"logotipo"), "50px");
		}
      $logotipo = '<div style="width:400px; height:75px">'.$logotipo.'</div>';
      //$html = "<b>Dirección: </b>".$direccion."<br /><center>".$logotipo."</center>";
	  $_SCRIPT_ = '<script>$("#datos_mupis_en_globo").load("contenido/mupis+ubicaciones+dinamico.php?accion=mupi&MUPI='.$id_mupi . "|" . $catorcena . "|" . $usuario.'");</script>';
      $html = "<center><b>Cliente(s) actual(es)</b><hr />".$logotipo."</center><div id='datos_mupis_en_globo' style='width:400px; height:50px'></div>$_SCRIPT_";
	  $Contenido_maximizado = "";
	  if (in_array($session->userlevel,$NivelesPermitidos)) {

			$q = "SELECT id_pantalla, tipo_pantalla, codigo_pedido, (SELECT descripcion FROM ".TBL_MUPI_ORDERS." AS b WHERE b.codigo_pedido=a.codigo_pedido) AS descripcion FROM emupi_mupis_caras AS a WHERE codigo_mupi='$id_mupi' and catorcena='$catorcena'".";";
			//echo $q."<br>";
			$result2 = $database->query($q);
			$num_rows2 = mysql_numrows($result2);
			$logotipo = "<br />";
			$Valor_Peatonal = $Valor_Vehicular = $Pantalla_Vehicular = $Pantalla_Peatonal = NULL;
			$Valor_Vehicular_Desc = $Valor_Peatonal_Desc = 'Ninguno';
			
			$Boton_Vehicular = "<a href='./?"._ACC_."=gestionar+pantallas&crear=1&catorcena=$catorcena&tipo=0&id_mupi=$id_mupi' target='blank'>Crear esta cara...</a><br />";
			$Boton_Peatonal = "<a href='./?"._ACC_."=gestionar+pantallas&crear=1&catorcena=$catorcena&tipo=1&id_mupi=$id_mupi' target='blank'>Crear esta cara...</a><br />";

			// Si ese mupi tenia caras, entonces las recorremos.
			if($num_rows2 > 0){
				   for($ii=0; $ii<$num_rows2; $ii++){
					   if ( (mysql_result($result2,$ii,"tipo_pantalla") % 2) == 0 ) {
							$n_caras_v++;
							$n_caras++;
						    $Pantalla_Vehicular = mysql_result($result2,$ii,"id_pantalla");
							$Valor_Vehicular = mysql_result($result2,$ii,"codigo_pedido");
							$Valor_Vehicular_Desc = mysql_result($result2,$ii,"descripcion");
							$Boton_Vehicular = "<a href='./?"._ACC_."=gestionar+pantallas&actualizar=1&id=$Pantalla_Vehicular&catorcena=$catorcena' target='blank'>Editar esta cara...</a><br />";
						} else {
							$n_caras_p++;
							$n_caras++;
							$Pantalla_Peatonal = mysql_result($result2,$ii,"id_pantalla");
							$Valor_Peatonal = mysql_result($result2,$ii,"codigo_pedido");
							$Valor_Peatonal_Desc = mysql_result($result2,$ii,"descripcion");
							$Boton_Peatonal = "<a href='./?"._ACC_."=gestionar+pantallas&actualizar=1&id=$Pantalla_Peatonal&catorcena=$catorcena' target='blank'>Editar esta cara...</a><br />";
					   }
				   }
			
			}
			if ($session->isAdmin()) {
			$Contenido_maximizado =
			"<b>Catorcena a editar:</b> " . AnularFechaNula($catorcena). " - " . AnularFechaNula( Fin_de_catorcena($catorcena) ).
			"<br /><b>Mupi a Editar:</b> $codigo_mupi -> Id. $id_mupi" .
			"<hr />".
			"<table>".
			   "<tr>".
				   "<th>Cara vehicular</th><th>Cara peatonal</th>".
			   "</tr>".
			   "<tr>".
				   "<td width='50%' valign='top'>". // VEHICULAR
				   "<b>ID. Pantalla:</b> ".EnNulidad($Pantalla_Vehicular,"Ninguna")."<br />".
				   "<b>Código de pedido Actual:</b> $Valor_Vehicular | $Valor_Vehicular_Desc<br />".
				   $Boton_Vehicular.
				   "</td>".
				   "<td width='50%' valign='top'>". // PEATONAL
				   "<b>ID. Pantalla:</b> ". EnNulidad($Pantalla_Peatonal,"Ninguna")."<br />".
				   "<b>Código de pedido Actual:</b> $Valor_Peatonal | $Valor_Peatonal_Desc<br />".
				   $Boton_Peatonal.
				   "</td>".
			   "</tr>".
			"</table>"
			;
			}
		
      } else {
		  $q = "SELECT id_pantalla, tipo_pantalla FROM emupi_mupis_caras AS a WHERE codigo_mupi='$id_mupi' AND catorcena='$catorcena' AND codigo_pedido IN (SELECT codigo_pedido FROM ".TBL_MUPI_ORDERS." AS tmo WHERE tmo.codigo='$usuario')";
		  DEPURAR($q,0);
		  $result2 = $database->query($q);
		  $num_rows2 = mysql_numrows($result2);
		  if($num_rows2 > 0){
			for($ii=0; $ii<$num_rows2; $ii++){
			   if ( (mysql_result($result2,$ii,"tipo_pantalla") % 2) == 0 ) {
					$n_caras_v++;
					$n_caras++;
				} else {
					$n_caras_p++;
					$n_caras++;
			   }
			}
		  }
		  $map->Mostrar_Contenido_Maximizado = false;
	  }
      $map->addMarkerByCoords($lon, $lat, $codigo_mupi . ' | ' . $direccion, $html, $codigo_mupi, $id_mupi . "|" . $catorcena . "|" . $usuario, $Contenido_maximizado);
	  $map->addMarkerIcon(public_base_directory().'/punto.gif','',12,12,0,0);
   }
   
   // Mostrar referencias. 10/02/09
   if ($grupo_calle) $t_grupo_calle = " where $grupo_calle";
   $q = "SELECT * FROM emupi_referencias".$t_grupo_calle.";";
   DEPURAR($q,0);
   $result = $database->query($q);
   $num_rows = mysql_numrows($result);
   $map->referencias = true;
   for($i=0; $i<$num_rows; $i++){
      $lon  = mysql_result($result,$i,"lon");
      $lat  = mysql_result($result,$i,"lat");
	  $logotipo = "<br />".CargarImagenDesdeBD(mysql_result($result,$i,"imagen_referencia"), "200px");
	  $map->addMarkerByCoords($lon, $lat, "Referencia" , "Este es un punto de referencia<br />".$logotipo, '', "REF|$catorcena|".mysql_result($result,$i,"id_referencia"),"");
	  $map->addMarkerIcon(public_base_directory(). '/include/ver.php?id='.mysql_result($result,$i,"imagen_referencia"),'',0,0,50,50);
	  
   }
   
   
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
$datos = '';
$datos .= $map->getMapJS();
//$datos .= $map->getMap();
//$datos .= $map->getSidebar();
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
// Total de Eco Mupis mostrados
$datos .= "<hr />Total de Ecomupis en la calle seleccionada: <b>$n_mupis</b><br />";
// Total de caras encontradas
$datos .= "Total de espacios publicitarios en la calle seleccionada: <b>$n_caras</b><br />";
$datos .= "Número de caras publicitarias vehiculares en la calle seleccionada: <b>" . $n_caras_v ."</b><br />";
$datos .= "Número de caras publicitarias peatonales en la calle seleccionada: <b>" . $n_caras_p ."</b><br />";

$datos .= SCRIPT('onLoad();');
return $datos;
}

function actualizarCoords ($id, $lat, $lng) {
	global $database;
	$q = "UPDATE ".TBL_MUPI." SET lat='$lat', lon='$lng' WHERE id_mupi='$id';";
	$result = $database->query($q);
	$database->REGISTRAR ("pantallas_mover", "Se movió el Eco Mupis '$id' a ($lat,$lng)","SQL: $q");
} 

function actualizarReferencia ($id, $lat, $lng) {
	global $database;
	$q = "UPDATE ".TBL_REFS." SET lat='$lat', lon='$lng' WHERE id_referencia='$id';";
	$result = $database->query($q);
	$database->REGISTRAR ("referencias_mover", "Se movió la referencia '$id' a ($lat,$lng)","SQL: $q");
}
?>
