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
			$Boton_combo_calles = '<input type="button" OnClick="funcion_combo_calles()" value="Ver">';;
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

function Buscar ($codigo_mupi, $catorcena, $usuario) {
   global $session;
   /* La logica aqui es que si el usuario que solicitó la búsqueda es administrador, entonces se le muestran todos los MUPIS, si no solo se le muestran los suyos */
   $datos ="";
   $link = @mysql_connect(DB_SERVER, DB_USER, DB_PASS) or die('Por favor revise sus datos, puesto que se produjo el siguiente error:<br /><pre>' . mysql_error() . '</pre>');
   mysql_select_db(DB_NAME, $link) or die('!->La base de datos seleccionada "'.$DB_base.'" no existe');
   if ( $session->isAdmin() || $session->userlevel == SALESMAN_LEVEL) {
	$q = "select tipo_pantalla, foto_real, (SELECT foto_pantalla FROM emupi_mupis_pedidos as b where a.codigo_pedido=b.codigo_pedido) AS arte from emupi_mupis_caras as a where catorcena=$catorcena AND codigo_mupi = (SELECT id_mupi FROM emupi_mupis WHERE id_mupi=$codigo_mupi);";
   } else {
	$q = "select tipo_pantalla, foto_real, (SELECT foto_pantalla FROM emupi_mupis_pedidos as b where a.codigo_pedido=b.codigo_pedido) AS arte from emupi_mupis_caras as a where catorcena=$catorcena AND codigo_pedido IN (SELECT codigo_pedido FROM emupi_mupis_pedidos where codigo='$usuario') AND codigo_mupi = (SELECT id_mupi FROM emupi_mupis WHERE id_mupi=$codigo_mupi);";
   }
   //echo $q.'<br />';
   $result = @mysql_query($q, $link) or retornar ('!->Ocurrió un error mientras se revisaba la disponibilidad del MUPI.');
   /* Error occurred, return given name by default */
   $num_rows = mysql_numrows($result);
   if(!$result || ($num_rows < 0)){
      retornar("Error mostrando la información");
   }
 
 if($num_rows == 0){
      retornar (Mensaje("¡No hay datos para ese código ($codigo_mupi)!",_M_ERROR));
   }
   $datos .= '<h2>Datos del MUPI seleccionado</h2>';
   $datos .= '<table>';
   $tipoPantalla = ''; //Par
   for($i=0; $i<$num_rows; $i++){
	  $arte = mysql_result($result,$i,"arte");
      $tipo_pantalla  = mysql_result($result,$i,"tipo_pantalla");
      $foto_real = mysql_result($result,$i,"foto_real");
      // si es par es vehicular
      if ( ($tipo_pantalla % 2) == 0 ) {
		$tipoPantalla = 'vehicular';
		   
      }else{
		$tipoPantalla = 'peatonal';
      }
	
	$datos .= "<tr><th><center>Imagen actual de su pantalla ".$tipoPantalla.":</center></th></tr>";
	$datos .= "<tr><td><center>" . CargarImagenDesdeBD($foto_real,"300px") . "</center></td>";
	$datos .= "<tr><th><center>Arte digital de su pantalla:</center></th></tr>";
	$datos .= "<tr><td><center>" . CargarImagenDesdeBD($arte,"300px") . "</center></td></tr>";	
   }
   $datos .= '</table>';
retornar($datos);
}

function Mostrar_Mapa($catorcena, $calle, $usuario){
global $session, $map, $database;
$NivelesPermitidos = array(ADMIN_LEVEL, SALESMAN_LEVEL);
// setup database for geocode caching
$map->setDSN('mysql://'.DB_USER.':'.DB_PASS.'@'.DB_SERVER.'/'.DB_NAME);
//Google Map Key
$map->setAPIKey(GOOGLE_MAP_KEY);
// proporción de la ventana que tomará el mapa.
$map->setWidth('100%');
$map->referencias = false;
// Desactivar controles de Zoom y movimiento para cliente.
if ( !$session->isAdmin() && $session->userlevel != SALESMAN_LEVEL ) {
	$map->map_controls = false;
	$map->disable_drag = true;
	$map->disableInfoWindow();
}
// Cargar puntos mupis.
$WHERE_USER = "";
	if ( isset($_GET['sin_presencia']) ) {
		$q = "select id_mupi, codigo_mupi, direccion, foto_generica, lon, lat, codigo_evento, codigo_calle from emupi_mupis AS a where codigo_calle='$calle';";
	} else {
	if ( ($session->isAdmin() && !$usuario) || $session->userlevel == SALESMAN_LEVEL ) {
		$q = "select id_mupi, codigo_mupi, direccion, foto_generica, lon, lat, codigo_evento, codigo_calle from emupi_mupis AS a where codigo_calle='$calle' and id_mupi IN (select codigo_mupi FROM emupi_mupis_caras WHERE catorcena=$catorcena);";
	} else {
		$q = "select id_mupi, codigo_mupi, direccion, foto_generica, lon, lat, codigo_evento, codigo_calle, (SELECT logotipo from emupi_usuarios where codigo='$usuario') as logotipo from emupi_mupis where codigo_calle='$calle' and id_mupi IN (select codigo_mupi FROM emupi_mupis_caras WHERE catorcena=$catorcena AND codigo_pedido IN (SELECT codigo_pedido FROM emupi_mupis_pedidos WHERE codigo='$usuario'));";
	}
	}
   //DEPURAR($q,1);
   $result = $database->query($q);
   $num_rows = mysql_numrows($result);
   if(!$result || ($num_rows < 0)){
      exit ( "Error mostrando la información<br />");
   }
   
   if($num_rows == 0){
      exit ("¡No hay "._NOMBRE_." ingresados!<br />");
   }
	//Imagen de los marcadores
	//Removido por petición. 06/02/09
	//if ( !$session->isAdmin() || $usuario ) $map->setMarkerIcon('http://'.$_SERVER['SERVER_ADDR'].'/mupi/include/ver.php?id='.mysql_result($result,0,"logotipo"),'',0,0,0,0);
   
   for($i=0; $i<$num_rows; $i++){
      $id_mupi  = mysql_result($result,$i,"id_mupi");
      $codigo_mupi  = mysql_result($result,$i,"codigo_calle") . "." .mysql_result($result,$i,"codigo_mupi");
      $direccion = truncate(mysql_result($result,$i,"direccion"));
      $foto_generica = mysql_result($result,$i,"foto_generica");
      $lon  = mysql_result($result,$i,"lon");
      $lat  = mysql_result($result,$i,"lat");
      $codigo_evento = mysql_result($result,$i,"codigo_evento");
		if ( ($session->isAdmin() && !$usuario) || $session->userlevel == SALESMAN_LEVEL ) {
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
      $logotipo = '<div style="width:400px; height:150px">'.$logotipo.'</div>';
      $html = "<b>Dirección: </b>".$direccion."<br /><center>".$logotipo."</center>";
	  
      if (in_array($session->userlevel, $NivelesPermitidos)) {

			$q = "SELECT id_pantalla, codigo_pedido, (SELECT descripcion FROM ".TBL_MUPI_ORDERS." AS b WHERE b.codigo_pedido=a.codigo_pedido) AS descripcion FROM emupi_mupis_caras AS a WHERE codigo_mupi='$id_mupi' and catorcena='$catorcena'".";";
			//echo $q."<br>";
			$result2 = $database->query($q);
			$num_rows2 = mysql_numrows($result2);
			$logotipo = "<br />";
			$Valor_Peatonal = $Valor_Vehicular = $Pantalla_Vehicular = $Pantalla_Peatonal = NULL;
			$Valor_Vehicular_Desc = $Valor_Peatonal_Desc = 'Ninguno';
			if($num_rows2 > 0){
				   for($ii=0; $ii<$num_rows2; $ii++){
					   if ( (mysql_result($result2,$ii,"id_pantalla") % 2) == 0 ) {
						    $Pantalla_Vehicular = mysql_result($result2,$ii,"id_pantalla");
							$Valor_Vehicular = mysql_result($result2,$ii,"codigo_pedido");
							$Valor_Vehicular_Desc = mysql_result($result2,$ii,"descripcion");
						} else {
							$Pantalla_Peatonal = mysql_result($result2,$ii,"id_pantalla");
							$Valor_Peatonal = mysql_result($result2,$ii,"codigo_pedido");
							$Valor_Peatonal_Desc = mysql_result($result2,$ii,"descripcion");
					   }
				   }
			}

          $Boton_pedido_peatonal = '<input type="button" OnClick="$(\'#pedido_peatonal\').load(\'contenido/mupis+ubicaciones+dinamico.php?cambiar_pantalla='.$Pantalla_Peatonal.'&pedido=\'+$(\'#Combobox_pedidos_peatonal\').val())" value="Establecer">';
          $Boton_pedido_vehicular = '<input type="button" OnClick="$(\'#pedido_vehicular\').load(\'contenido/mupis+ubicaciones+dinamico.php?cambiar_pantalla='.$Pantalla_Vehicular.'&pedido=\'+$(\'#Combobox_pedidos_vehicular\').val())" value="Establecer">';
		  $Contenido_maximizado =
		  "<b>Catorcena a editar:</b> " . AnularFechaNula($catorcena). " - " . AnularFechaNula( Fin_de_catorcena($catorcena) ).
		  "<br /><b>Mupi a Editar:</b> $codigo_mupi -> Id. $id_mupi" .
		  "<hr />".
		  "<table>".
			   "<tr>".
				   "<th>Cara peatonal</th><th>Cara vehicular</th>".
			   "</tr>".
			   "<tr>".
				   "<td width='50%'>".
				   "<b>ID. Pantalla:</b> ".EnNulidad($Pantalla_Peatonal,"Ninguna")."<br />".
				   "<b>Código de pedido Actual:</b> $Valor_Vehicular | $Valor_Vehicular_Desc<br />".
				   addslashes($database->Combobox_pedido("Combobox_pedidos_peatonal", $Valor_Vehicular)).
				   addslashes($Boton_pedido_peatonal).
				   "<hr /><div id='pedido_peatonal'>Sin cambios</div>".
				   "</td>".
				   "<td width='50%'>".
				   "<b>ID. Pantalla:</b> ". EnNulidad($Pantalla_Vehicular,"Ninguna")."<br />".
				   "<b>Código de pedido Actual:</b> $Valor_Peatonal | $Valor_Peatonal_Desc<br />".
				   addslashes($database->Combobox_pedido("Combobox_pedidos_vehicular", $Valor_Peatonal)).
				   addslashes($Boton_pedido_vehicular).
				   "<hr /><div id='pedido_vehicular'>Sin cambios</div>".
				   "</td>".
			   "</tr>".
		   "</table>"
		  ;
		  $Contenido_maximizado = $Contenido_maximizado;
      } else {
		  $Contenido_maximizado = "";
	  }
      $map->addMarkerByCoords($lon, $lat, $codigo_mupi . ' | ' . $direccion, $html, $codigo_mupi, $id_mupi . "|" . $catorcena . "|" . $usuario, $Contenido_maximizado);
	  $map->addMarkerIcon(public_base_directory().'/punto.gif','',12,12,0,0);
   }
   
   // Mostrar referencias. 10/02/09
   $q = "SELECT * FROM emupi_referencias WHERE codigo_calle='$calle'".";";
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
$datos .= $map->getMap();
$datos .= $map->getSidebar();
$datos .= SCRIPT('onLoad();');
return $datos;
}

function actualizarCoords ($id, $lat, $lng) {
	global $database;
	$q = "UPDATE ".TBL_MUPI." SET lat='$lat', lon='$lng' WHERE id_mupi='$id';";
	$result = $database->query($q);
} 

function actualizarReferencia ($id, $lat, $lng) {
	global $database;
	$q = "UPDATE ".TBL_REFS." SET lat='$lat', lon='$lng' WHERE id_referencia='$id';";
	$result = $database->query($q);
}
?>
