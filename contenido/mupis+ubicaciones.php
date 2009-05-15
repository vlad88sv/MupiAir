<?php
/*
 * Este archivo se encarga de mostrar el mapa y generar los dos tipos de marcadores: mupis y referencias.
 * El tipo de mapa y las caracteristicas a mostrar dependen del tipo de usuario que la vea.
 * Este modulo puede ser acceso por visitantes, posiblemente a travez del FLASH de la pagina de inicio.
 */
function CONTENIDO_mupis_ubicaciones($usuario = '')
{
    global $session, $database, $map;
    $NivelesPermitidos = array(ADMIN_LEVEL, SALESMAN_LEVEL, DEMO_LEVEL, USER_LEVEL);
    if (!in_array($session->userlevel, $NivelesPermitidos)) {
	      $usuario = $session->codigo;
    }
    /* Solo el Administrador y los visitantes pueden ver el mapa en modo "Ver Por Mupis".
    * El modo "Ver Por Mupis" es un modo especial que carga TODOS los mupis registrados.
    * Al intentar mostrar la imagen muestra la de la catorcena actual o un logo por defecto
    * si en caso es
    * Rebajamos el nivel de acceso a solo admin, para evitar que vean el botón verpormupis.
    */
    $FLAG_verpormupis = isset($_GET['verpormupis']) || !$session->logged_in;
    $BotonVerPorMupis = "";

    //<- Importante!!! Esto tiene que suceder antes de cualquier cuestión AJAX porque Google esta usando document.write en algún momento!.
    echo sprintf('<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=%s" type="text/javascript" charset="utf-8"></script>', GOOGLE_MAP_KEY);
    // ->

    // Javascript necesario para mostrar el combobox de las calles y para cargar los mapas al seleccionar calle
    echo '<script>$.jGrowl.defaults.position = "bottom-left";$(\'div.close\').trigger("click.jGrowl");$.jGrowl.defaults.closer = false;</script>';
    if ( !$FLAG_verpormupis ) {
	echo
	'<script>
	function funcion_combo_catorcenas(){
	$("#botones_arte").empty();
	$("#datos_calles").load("contenido/mupis+ubicaciones+dinamico.php?accion=cmbcalles&usuario=' . $usuario . '&catorcena="+$(\'#combo_catorcenas\').val());
	$("li#MM_paso_1").css("text-decoration", "line-through");
	//$.jGrowl.defaults.closerTemplate = "<div>Cerrar todas</div>";
	$.jGrowl("Si selecciona la calle \"Todas\", podrá desplazarse sobre todo el mapa para encontrar sus ubicaciones, de lo contrario se le mostrará unicamente los Ecomupis que esten en la calle seleccionada", { sticky: true , theme:  \'aviso\' });
	}
	function funcion_combo_calles() {
	$("#botones_arte").empty();
	$("#Mensajes").empty();
	$(\'div.close\').trigger("click.jGrowl");
	window.location="#ubicaciones";
	$("#grafico_mapa").load("contenido/mupis+ubicaciones+dinamico.php?accion=vermapa&usuario=' . $usuario . '&catorcena="+$(\'#combo_catorcenas\').val()+"&calle="+$(\'#combo_calles\').val());
	}
	</script>
	';
    } else {
	echo
	'<script>
	function funcion_combo_ver_mupi_calles() {
	$("#Mensajes").empty();
	$("#grafico_mapa").load("contenido/mupis+ubicaciones+dinamico.php?accion=verpormupis&sin_presencia=si&calle="+$(\'#combo_calles\').val());
	}
	</script>
	';
    }

    if ($session->logged_in) echo '<h1 id="ubicaciones">Ubicaciones de MUPIS contratados</h1><hr />';

    echo '<table height="100%">';
    echo '<tr>';
    echo '<td valign="top" width="305px">';

    if (!$FLAG_verpormupis) {
	$Boton_combo_catorcenas = '<input type="button" OnClick="funcion_combo_catorcenas()" value="Mostrar calles">';
	echo '<b>Ver Catorcena:</b><br />' . $database->Combobox_CatorcenasConPresencia("combo_catorcenas", $usuario) . $Boton_combo_catorcenas . '<br /><br />';
	echo '<span id="datos_calles"><b>Seleccione una catorcena</b><br /><br /></span>';
	echo '<span id="lista_mupis"></span>';

	//Solo el administrador puede ver el Botón ver por mupis
	if ( in_array($session->userlevel, array(ADMIN_LEVEL)) ) {
	  $BotonVerPorMupis = "<br /><hr />" . '<input type="button" OnClick="window.location=\'./?' . _ACC_ . '=ver+ubicaciones&verpormupis=1\'" value="Ver por Mupis">';
	}
    } else {
	$Boton_combo_calles = '<input type="button" OnClick="funcion_combo_ver_mupi_calles()" value="Mostrar mapa">';
	echo '<b>Ver Calle:</b><br />' . $database->Combobox_calle_grupos("combo_calles") . $Boton_combo_calles . '<br /><br />';
	//Solo el administrador puede ver el Botón ver por pantallas
	if ( in_array($session->userlevel, array(ADMIN_LEVEL)) ) {
	  $BotonVerPorMupis = "<br /><hr />" . '<input type="button" OnClick="window.location=\'./?' . _ACC_ . '=ver+ubicaciones\'" value="Ver por Pantallas">';
	}
    }

    echo $BotonVerPorMupis;
    echo '</td>';

    // Celda para el mapa
    echo '<td>';
    if ($session->logged_in) {
    echo '
      <div id="Mensajes" style="font-weight:bold;padding: 10px 10px">
      <h2>Instrucciones de uso.</h2>
      Para utilizar su sistema de ubicación Eco Mupis debe seguir los siguientes pasos:<br />
      <ol>
      <li id="MM_paso_1">Escoja la catorcena de la cual desea ver sus Eco Mupis y presione el botón "Mostrar calles".</li>
      <li>Aparecerá una selección de calles en las cuales Ud. tiene Eco Mupis con su publicidad, escoja la calle de la cual desee ver el mapa y presione "Mostrar Mapa".</li>
      <li>Deberá aparecer un Mapa con los Eco Mupis (representados como pequeños cuadros rojos) que contienen las fotos de su publicidad.<br />Al realizar "clic" sobre dichos cuadros rojos podrá observar un recuadro amarillo en la parte inferior Izq. que le ayudará a seleccionar la fografía real de sus caras contratadas en el Ecomupis seleccionado.</li>
      <li>Repita los pasos 1 a 3 tanto como Ud. guste.</li>
      </ol>
      </div>';
    } else {
    echo '
      <div id="Mensajes" style="font-weight:bold;padding: 10px 10px">
      <h2>Instrucciones de uso.</h2>
      Para utilizar su sistema de ubicación Eco Mupis debe seguir los siguientes pasos:<br />
      <ol>
      <li>Seleccione el grupo de calles del cual quiera conocer las ubicaciones disponibles o el grupo especial "Todas" si desea observar todos los medios publicitarios disponibles</li>
      <li>Deberá aparecer un Mapa con los Eco Mupis (representados como pequeños cuadros rojos) que contienen las fotos de su publicidad.<br />Al realizar "clic" sobre dichos cuadros rojos podrá observar un recuadro amarillo en la parte inferior Izq. que le ayudará a seleccionar la fografía real de sus caras contratadas en el Ecomupis seleccionado.</li>
      <li>Repita tanto como Ud. guste.</li>
      </ol>
      </div>';
    }
      echo '<div id="map" style="width: 100%; height: 400px"></div>';
      echo '<div id="sidebar_map"></div>';
      echo '<div id="grafico_mapa"></div>';

    echo '</td>';

    echo '</tr>';
    echo '</table>';
    echo '<span id="datos_mupis"></span>';
}
?>
