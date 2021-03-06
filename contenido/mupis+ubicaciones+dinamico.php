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

    if ( !isset( $_GET['accion'] ) )
    {
	retornar ( "Ud. esta utilizando incorrectamente este script de soporte. 0" );
    }
    switch ( $_GET['accion'] )
    {
	// Se mueve un marcador [mupi|referencia]
	case "drag":

	    if ( !isset( $_GET['id'] ) && !isset( $_GET['lat'] ) && !isset( $_GET['lng'] ))
	    {
		retornar ( "Ud. esta utilizando incorrectamente este script de soporte. [DRAG]" );
	    }
	    $parte = explode ('|',$_GET['id'] );
	    //Si tiene las tres partes validas (id, nueva_latitud, nueva_longitud)
	    if ( count($parte) != 3 )
	    {
		retornar ( "Ud. esta utilizando incorrectamente este script de soporte. [DRAG_2]" );
	    }

	    if ( $parte[0] == "REF" )
	    {
		//retornar ("Referencia?: " . "REF". ", Catorcena: ". $parte[1]. ", id_referencia:".$parte[2]);
		retornar ( actualizarReferencia ($parte[2], $_GET['lat'], $_GET['lng']));
	    }
	    else
	    {
		//retornar ("Mupi: " . $parte[0]. ", Catorcena: ". $parte[1]. ", Usuario:".$parte[2]);
		retornar ( actualizarCoords ($parte[0], $_GET['lat'], $_GET['lng']));
	    }

	break;
	// Pide información sobre un marcador seleccionado
	case "mupi":

	    if ( !isset( $_GET['MUPI'] ) )
	    {
		retornar ( "Ud. esta utilizando incorrectamente este script de soporte. 1" );
	    }
	    $parte = explode ('|',$_GET['MUPI'] );
	    // Si seleccionó una referencia...
	    if ( $parte[0] == "REF" )
	    {
		retornar("Se ha seleccionado la referencia " .CREAR_LINK_GET("gestionar+referencias&referencia=". $parte[2], $parte[2], "Abre el dialogo de gestión para la referencia seleccionada"));
	    }


	    if ( count($parte) == 3 )
	    {
		//retornar ("Mupi: " . $parte[0]. ", Catorcena: ". $parte[1]. ", Usuario:".$parte[2]);
		retornar ( Buscar ($parte[0], $parte[1], $parte[2] ) );
	    }

	    break;
	/*
	 * Desea obtener un Combobox con las calles en la catorcena solicitada.
	 * Ademas se muestran solo las calles en las cuales el cliente tiene
	 * una cara contratada. En el caso de Administrador y Vendedor se muestran todas
	 * las calles que tengan ecomupis en ellas
	*/
	case "cmbcalles":

	    if ( !isset( $_GET['catorcena'] ) && !isset ( $_GET['usuario'] ) )
	    {
		retornar ( "Ud. esta utilizando incorrectamente este script de soporte. 2" );
	    }
	    $Boton_combo_calles = '<input type="button" OnClick="funcion_combo_calles()" value="Mostrar Mapa">';
	    retornar ('<b>Ver Calle:</b><br />' . $database->Combobox_CallesConPresencia("combo_calles",$_GET['usuario'],$_GET['catorcena']).$Boton_combo_calles);

	    break;
	/*
	 * Mostras el mapa solicitado.
	 * Nos tiene que proveer de la catorcena, calle y usuario del cual necesita el mapa.
	 * Solo administradores y rela. puede obtener mapas globales (ej. Todos los usuarios).
	*/
	case "vermapa":

	    if ( isset( $_GET['catorcena'] ) && isset( $_GET['calle'] ) && isset ( $_GET['usuario'] ) )
	    {
		retornar (Mostrar_Mapa($_GET['catorcena'], $_GET['calle'], $_GET['usuario']));
	    }
	    else
	    {
		retornar ( "Ud. esta utilizando incorrectamente este script de soporte. 3" );
	    }

	    break;
	/*
	 * Caso especial en el que se muestra los mupis sin necesidad de que dispongan de caras
	 * contradas. Muestra todos los mupis en existencia y si se pulsa sobre ellos *trata* de
	 * desplegar el arte y foto disponible en la catorcena actual para ese ecomupis.
	*/
	case "verpormupis":

	    if ( isset( $_GET['calle'] ) )
	    {
		retornar (Mostrar_Mapa(Obtener_catorcena_cercana(), $_GET['calle'], ""));
	    }
	    else
	    {
		retornar ( "Ud. esta utilizando incorrectamente este script de soporte. 3" );
	    }

	    break;
    }	//Switch


function retornar($texto)
{
    exit ('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />' . $texto . '<br />');
}

/*
 * Buscar() ~ Función encargada de devolver los links adecuados para poder
 * Visualizar el arte y fotos colocados en un mupi.
*/
function Buscar ($codigo_mupi, $catorcena, $usuario, $FLAG_salida_globo=false)
{
    global $session;
    $link = @mysql_connect(DB_SERVER, DB_USER, DB_PASS) or die('Por favor revise sus datos, puesto que se produjo el siguiente error:<br /><pre>' . mysql_error() . '</pre>');
    mysql_select_db(DB_NAME, $link) or die('!->La base de datos seleccionada "'.$DB_base.'" no existe');

    if ( time() > $catorcena )
    {
        $tCatorcena=$catorcena;
    }
    else
    {
        $tCatorcena=Obtener_catorcena_anterior($catorcena);
    }


    if ( !$usuario)
    {
        $q = "select tipo_pantalla, foto_real, (SELECT foto_pantalla FROM emupi_mupis_pedidos as b where a.codigo_pedido=b.codigo_pedido) AS arte from emupi_mupis_caras as a where catorcena=$catorcena AND codigo_mupi = (SELECT id_mupi FROM emupi_mupis WHERE id_mupi=$codigo_mupi);";
    }
    else
    {
        $q = "select tipo_pantalla, foto_real, (SELECT foto_pantalla FROM emupi_mupis_pedidos as b where a.codigo_pedido=b.codigo_pedido) AS arte from emupi_mupis_caras as a where catorcena=$tCatorcena AND codigo_pedido IN (SELECT codigo_pedido FROM emupi_mupis_pedidos where codigo='$usuario') AND codigo_mupi = (SELECT id_mupi FROM emupi_mupis WHERE id_mupi=$codigo_mupi);";
    }

    $result = @mysql_query($q, $link) or retornar ('!1->Ocurrió un error mientras se revisaba la disponibilidad del MUPI.<br />'.mysql_error($link));
    /* Error occurred, return given name by default */
    $num_rows = mysql_numrows($result);

    if(!$result || ($num_rows < 0))
    {
        retornar("Error mostrando la información");
    }


    if($num_rows == 0)
    {
        // Cargar una imagen de ecomupis =)
    }

    // =====================Hasta acá la BD================================= //
    // ===================================================================== //
    // Empezamos a recorrer las caras encontradas
    $tipoPantalla = $datosLinksGlobo = '';
    for($i=0; $i<$num_rows; $i++)
    {
        $arte = mysql_result($result,$i,"arte");
        $tipo_pantalla  = mysql_result($result,$i,"tipo_pantalla");
        $foto_real = mysql_result($result,$i,"foto_real");
        // si es par es vehicular
        $tipoPantalla = ($tipo_pantalla % 2) == 0 ? 'vehicular' : 'peatonal';
	$NivelesPermitidos = array(ADMIN_LEVEL, SALESMAN_LEVEL, DEMO_LEVEL);
	// Son links para Globito o para Growl normal?
	if ( $FLAG_salida_globo )
	{
	    // Si es catorcena futura y no es Administrador, ni Vendedor ni Demo.
	    if ( time() < $catorcena && !in_array($session->userlevel, $NivelesPermitidos) )
	    {
		$datosUI[$tipoPantalla] .= "<center><strong>Imagen actual de cara ".$tipoPantalla.":</strong></center>"."<center>Viendo catorcena futura, la fotografía mostrada es ilustrativa y corresponde al mupi seleccionado en la catorcena presente.<br /><br />" . "<img src=\\\'include/ver.php?id=".$foto_real."\\\' />" . "</center>"."<center><strong>Arte digital de campaña:</strong></center>"."<center>Viendo catorcena futura, Arte no disponible</center>";
	    }
	    else
	    {
		$datosUI[$tipoPantalla] = "<center><strong>Imagen actual de cara ".$tipoPantalla.":</strong></center>"."<center>" . "<img src=\\\'include/ver.php?id=". $foto_real . "\\\' />" . "</center>"."<center><strong>Arte digital de campaña:</center>"."<center>" . "<img src=\\\'include/ver.php?id=".$arte."\\\' />" . "</strong></center>";
	    }

	    $datosCaja = "$('div.close').trigger('click.jGrowl');$.jGrowl('".($datosUI[$tipoPantalla])."'".",{theme: 'smoke',sticky: true,closer: false})";
	    $datosLinksGlobo .= "<a onclick=\\\"$datosCaja\\\">Ver imagen de cara ".$tipoPantalla."</a><br />";
	}
	// Growl normal...
	else
	{
	    // Si es catorcena futura y no es Administrador, ni Vendedor ni Demo.
            if ( time() < $catorcena && !in_array($session->userlevel, $NivelesPermitidos) )
	    {
		$datosUI[$tipoPantalla] .= "<center><strong>Imagen actual de cara ".$tipoPantalla.":</strong></center>"."<center>Viendo catorcena futura, la fotografía mostrada es ilustrativa y corresponde al mupi seleccionado en la catorcena presente.<br /><br />" . "<img src='include/ver.php?id=".$foto_real."' />" . "</center>"."<center><strong>Arte digital de campaña:</strong></center>"."<center>Viendo catorcena futura, Arte no disponible</center>";
	    }
	    else
	    {
		$datosUI[$tipoPantalla] = "<center><strong>Imagen actual de cara ".$tipoPantalla.":</strong></center>"."<center>" . "<img src='include/ver.php?id=". $foto_real . "' />" . "</center>"."<center><strong>Arte digital de campaña:</center>"."<center>" . "<img src='include/ver.php?id=".$arte."' />" . "</strong></center>";
	    }

	    $datosCaja = "$.jGrowl('".addslashes($datosUI[$tipoPantalla])."',{theme: 'smoke', sticky: true, closer: false})";

	    if ($num_rows > 1)
	    {
		$datosLinksGlobo .= "<a onclick=\"$datosCaja\">Ver imagen de cara ".$tipoPantalla."</a><br />";
	    } else {
		$datosLinksGlobo .= JS_($datosCaja);
	    }
	}
    }
    $datosLinksGlobo = "<center>".$datosLinksGlobo."</center>";
    // Fin del recorrido de datos.
    return $datosLinksGlobo;
}

function Mostrar_Mapa($catorcena, $calle, $usuario)
{
    global $session, $map, $database;

    // =====================Inicio de mapas================================= //
    $map->setDSN('mysql://'.DB_USER.':'.DB_PASS.'@'.DB_SERVER.'/'.DB_NAME);
    $map->setAPIKey(GOOGLE_MAP_KEY);
    $map->setWidth('100%');
    $map->referencias = false;

    // Desactivar los controles que solo Admin puede tener.

    if ( !$session->isAdmin() )
    {
        // Controles de Mapa
        $map->map_controls = false;
        // Arrastre de mapa
        $map->disable_map_drag = true;
        // Arrastre de marcadores
        $map->disable_drag = true;
        // Controles extra para edición
        $map->Mostrar_Contenido_Maximizado = false;
    }

    // El globito solo Admin, Vendedor y Usuario pueden ver.
    if ( !in_array($session->userlevel,array(ADMIN_LEVEL, SALESMAN_LEVEL, USER_LEVEL)) )
    {
        $map->disableInfoWindow();
	$FLAG_globito = false;
    } else {
	$FLAG_globito = true;
    }
    // ===================================================================== //

    // =====================Cargar marcadores:mupis================================= //
    $WHERE_USER = "";
    $grupo_calle = "";
    $t_grupo_calle = "";

    //¿Quiere todas las calles?
    if ( $calle == "::T::")
    {
        $map->disable_map_drag = false;
    }
    //¿Quiere un grupo de calles?
    elseif ( strpos($calle, "G:") !== false )
    {
        $Explotado = @end(explode(":",$calle));
        $grupo_calle = "codigo_calle IN (SELECT codigo_calle FROM ".TBL_STREETS." WHERE grupo_calle='".$Explotado."')";
        $map->disable_map_drag = false;
    }
    //No, el quiere una calle en especifico.
    else
    {
        $grupo_calle = "codigo_calle='$calle'";
    }

    // Ver por Mupis
    if ( isset($_GET['sin_presencia']) )
    {
        if ($grupo_calle) $t_grupo_calle = " where $grupo_calle";
        $q = "select id_mupi, codigo_mupi, direccion, foto_generica, lon, lat, codigo_evento, codigo_calle FROM emupi_mupis AS a $t_grupo_calle;";
    }
    else
    {
        // Quiere ver mupis que tengan publicidad, sin restricción de usuario.

        if ( !$usuario )
        {
            // Siendo Admin, Vendedor, Usuario o Demo

            if ($grupo_calle) $t_grupo_calle = " and $grupo_calle";
            $q = "select id_mupi, codigo_mupi, direccion, foto_generica, lon, lat, codigo_evento, codigo_calle FROM emupi_mupis AS a WHERE id_mupi IN (select codigo_mupi FROM emupi_mupis_caras WHERE catorcena=$catorcena)$t_grupo_calle;";
        }
        else
        {
            // Siendo cliente o con usuario

            if ($grupo_calle) $t_grupo_calle = "$grupo_calle and ";
            $q = "select id_mupi, codigo_mupi, direccion, foto_generica, lon, lat, codigo_evento, codigo_calle, (SELECT logotipo from emupi_usuarios where codigo='$usuario') as logotipo from emupi_mupis where $t_grupo_calle id_mupi IN (select codigo_mupi FROM emupi_mupis_caras WHERE catorcena=$catorcena AND codigo_pedido IN (SELECT codigo_pedido FROM emupi_mupis_pedidos WHERE codigo='$usuario'));";
        }

    }

    DEPURAR($q,0);
    $result = $database->query($q);
    $n_mupis = $num_rows = mysql_numrows($result);

    if(!$result || ($num_rows < 0))
    {
        exit ( "Error mostrando la información<br />");
    }


    if($num_rows == 0)
    {
        exit ("¡No hay "._NOMBRE_." ingresados!<br />");
    }

    // Recorrer todos los mupis.
    $n_caras_p = $n_caras_v = $n_caras = 0; //Estadísticas individuales
    for($i=0; $i<$num_rows; $i++)
    {
        $id_mupi  = mysql_result($result,$i,"id_mupi");
        $codigo_mupi  = mysql_result($result,$i,"codigo_calle") . "." .mysql_result($result,$i,"codigo_mupi");
        $direccion = truncate(mysql_result($result,$i,"direccion"));
        $foto_generica = mysql_result($result,$i,"foto_generica");
        $lon  = mysql_result($result,$i,"lon");
        $lat  = mysql_result($result,$i,"lat");
        $codigo_evento = mysql_result($result,$i,"codigo_evento");

	// Si puede ver el Globito, entonces hay que cargar los logotipos de los usuarios en el mupi.
	$logotipo = "";
	if ( $FLAG_globito )
        {
            $q = "SELECT DISTINCT logotipo FROM emupi_usuarios where codigo IN (SELECT codigo from emupi_mupis_pedidos where codigo_pedido IN (SELECT codigo_pedido FROM emupi_mupis_caras as b WHERE catorcena=$catorcena AND b.codigo_mupi=".mysql_result($result,$i,"id_mupi")."))";
            $result2 = $database->query($q);
            $num_rows2 = mysql_numrows($result2);
            $logotipo = "<br />";

            if($num_rows2 > 0)
            {
                for($ii=0; $ii<$num_rows2; $ii++)
                {
                    $logotipo .= CargarImagenDesdeBD2(mysql_result($result2,$ii,"logotipo"), "50px");
                }

            }
	    $logotipo = "<div style='width:400px; height:75px'>".$logotipo."</div>";
        }

        $DATOS = Buscar($id_mupi, $catorcena, $usuario,true);
        $html = "<div style='position:static;height:200px;width:400px'><center><b>Cliente(s) actual(es)</b><hr />".$logotipo."</center><div id='datos_mupis_en_globo' style='width:400px; height:50px'>$DATOS</div></div>";
        $Contenido_maximizado = "";

	// Si no hay usuario entonces sacar todas la pantallas de ese mupi.
	// Si hay usuario entonces solo sacar las pantallas de ese usuario en ese mupi.
	if (!$usuario)
        {
            $q = "SELECT id_pantalla, tipo_pantalla, codigo_pedido, (SELECT descripcion FROM ".TBL_MUPI_ORDERS." AS b WHERE b.codigo_pedido=a.codigo_pedido) AS descripcion FROM emupi_mupis_caras AS a WHERE codigo_mupi='$id_mupi' and catorcena='$catorcena'".";";
	} else {
	    $q = "SELECT id_pantalla, tipo_pantalla FROM emupi_mupis_caras AS a WHERE codigo_mupi='$id_mupi' AND catorcena='$catorcena' AND codigo_pedido IN (SELECT codigo_pedido FROM ".TBL_MUPI_ORDERS." AS tmo WHERE tmo.codigo='$usuario')";
	}
	//echo $q."<br>";
	$result2 = $database->query($q);
	$num_rows2 = mysql_numrows($result2);
	$logotipo = "<br />";
	if ($session->isAdmin()){
	    $Valor_Peatonal = $Valor_Vehicular = $Pantalla_Vehicular = $Pantalla_Peatonal = NULL;
	    $Valor_Vehicular_Desc = $Valor_Peatonal_Desc = 'Ninguno';
	    $Boton_Vehicular = "<a href='./?"._ACC_."=gestionar+pantallas&crear=1&catorcena=$catorcena&tipo=0&id_mupi=$id_mupi' target='blank'>Crear esta cara...</a><br />";
	    $Boton_Peatonal = "<a href='./?"._ACC_."=gestionar+pantallas&crear=1&catorcena=$catorcena&tipo=1&id_mupi=$id_mupi' target='blank'>Crear esta cara...</a><br />";
	}
	// Si ese mupi tenia caras, entonces las recorremos.

	if($num_rows2 > 0)
	{
	    for($ii=0; $ii<$num_rows2; $ii++)
	    {

		if ( (mysql_result($result2,$ii,"tipo_pantalla") % 2) == 0 )
		{
		    $n_caras_v++;
		    $n_caras++;
		    if ($session->isAdmin())
		    {
			$Pantalla_Vehicular = mysql_result($result2,$ii,"id_pantalla");
			$Valor_Vehicular = mysql_result($result2,$ii,"codigo_pedido");
			$Valor_Vehicular_Desc = mysql_result($result2,$ii,"descripcion");
			$Boton_Vehicular = "<a href='./?"._ACC_."=gestionar+pantallas&actualizar=1&id=$Pantalla_Vehicular&catorcena=$catorcena' target='blank'>Editar esta cara...</a><br />";
		    }
		}
		else
		{
		    $n_caras_p++;
		    $n_caras++;
		    if ($session->isAdmin())
		    {
			$Pantalla_Peatonal = mysql_result($result2,$ii,"id_pantalla");
			$Valor_Peatonal = mysql_result($result2,$ii,"codigo_pedido");
			$Valor_Peatonal_Desc = mysql_result($result2,$ii,"descripcion");
			$Boton_Peatonal = "<a href='./?"._ACC_."=gestionar+pantallas&actualizar=1&id=$Pantalla_Peatonal&catorcena=$catorcena' target='blank'>Editar esta cara...</a><br />";
		    }
		}

	    }

        }

	if ($session->isAdmin())
            {
                $Contenido_maximizado =    "<b>Catorcena a editar:</b> " . AnularFechaNula($catorcena). " - " . AnularFechaNula( Fin_de_catorcena($catorcena) ).    "<br /><b>Mupi a Editar:</b> $codigo_mupi -> Id. $id_mupi" .    "<hr />".    "<table>".       "<tr>".       "<th>Cara vehicular</th><th>Cara peatonal</th>".       "</tr>".       "<tr>".       "<td width='50%' valign='top'>". // VEHICULAR
                "<b>ID. Pantalla:</b> ".EnNulidad($Pantalla_Vehicular,"Ninguna")."<br />".       "<b>Código de pedido Actual:</b> $Valor_Vehicular | $Valor_Vehicular_Desc<br />".       $Boton_Vehicular.       "</td>".       "<td width='50%' valign='top'>". // PEATONAL
                "<b>ID. Pantalla:</b> ". EnNulidad($Pantalla_Peatonal,"Ninguna")."<br />".       "<b>Código de pedido Actual:</b> $Valor_Peatonal | $Valor_Peatonal_Desc<br />".       $Boton_Peatonal.       "</td>".       "</tr>".    "</table>"    ;
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
    for($i=0; $i<$num_rows; $i++)
    {
        $lon  = mysql_result($result,$i,"lon");
        $lat  = mysql_result($result,$i,"lat");
        $logotipo = "<br />".CargarImagenDesdeBD2(mysql_result($result,$i,"imagen_referencia"), "200px");
        $map->addMarkerByCoords($lon, $lat, "Referencia" , "Este es un punto de referencia<br />".$logotipo, '', "REF|$catorcena|".mysql_result($result,$i,"id_referencia"),"");
        $map->addMarkerIcon(public_base_directory(). '/include/ver.php?id='.mysql_result($result,$i,"imagen_referencia"),'',0,0,50,50);
    }

    //------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
    $datos = '';
    $datos .= $map->getMapJS();
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

// Función de guardado de nuevas coordenadas que se ejecuta al mover un mupi.
function actualizarCoords ($id, $lat, $lng)
{
    global $database;
    $q = "UPDATE ".TBL_MUPI." SET lat='$lat', lon='$lng' WHERE id_mupi='$id';";
    $result = $database->query($q);
    $database->REGISTRAR ("pantallas_mover", "Se movió el Eco Mupis '$id' a ($lat,$lng)","SQL: $q");
}

// Función de guardado de nuevas coordenadas que se ejecuta al mover una referencia.
function actualizarReferencia ($id, $lat, $lng)
{
    global $database;
    $q = "UPDATE ".TBL_REFS." SET lat='$lat', lon='$lng' WHERE id_referencia='$id';";
    $result = $database->query($q);
    $database->REGISTRAR ("referencias_mover", "Se movió la referencia '$id' a ($lat,$lng)","SQL: $q");
}

?>
