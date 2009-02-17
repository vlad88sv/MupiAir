<?php
function CONTENIDO_mupis_ubicaciones($usuario=''){
global $session, $database,$map;
$NivelesPermitidos = array(ADMIN_LEVEL, SALESMAN_LEVEL);
if ( !in_array($session->userlevel,$NivelesPermitidos) ) { $usuario = $session->codigo; }
//Importante!!! Esto tiene que suceder antes de cualquier cuestión AJAX porque Google esta usando document.write en algún momento!.
echo sprintf('<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=%s" type="text/javascript" charset="utf-8"></script>', GOOGLE_MAP_KEY);
// AJAX ;)
if ( !isset($_GET['verpormupis']) ) {
echo 
SCRIPT('
    $("#datos_calles").load("contenido/mupis+ubicaciones+dinamico.php?accion=calles&usuario='.$usuario.'&catorcena="+document.getElementsByName(\'combo_catorcenas\')[0].value);
	$("#combo_catorcenas").change(function (){$("#datos_calles").load("contenido/mupis+ubicaciones+dinamico.php?accion=calles&usuario='.$usuario.'&catorcena="+document.getElementsByName(\'combo_catorcenas\')[0].value);});
');
}

echo "<h1>Ubicaciones de MUPIS contratados</h1><hr />";

echo '<table>';
echo '<tr>';
echo '<td valign="top">';

if ( !isset($_GET['verpormupis']) ) {
echo '<b>Ver Catorcena:</b><br />' . $database->Combobox_CatorcenasConPresencia("combo_catorcenas",$usuario).'<br /><br />';
echo '<span id="datos_calles"><b>Seleccione una catorcena</b><br /><br /></span>';
//echo '<span id="lista_mupis"><b>Seleccione una calle</b><br /><br /></span>';
echo '<span id="lista_mupis"></span>'; //Deshabilitado - 17/02/09 - petición de Alejandro.

if ( in_array($session->userlevel,$NivelesPermitidos) ) {
	$BotonVerPorMupis = '<input type="button" OnClick="window.location=\'./?'._ACC_.'=ver+ubicaciones&verpormupis=1\'" value="Ver por Mupis">';
}
} else {
	echo '<b>Ver calle:</b><br />' . $database->Combobox_calle("combo_calles").'<br /><br />';
	if ( in_array($session->userlevel,$NivelesPermitidos) ) {
		$BotonVerPorMupis = '<input type="button" OnClick="window.location=\'./?'._ACC_.'=ver+ubicaciones\'" value="Ver por Pantallas">';
	}
}
	echo $BotonVerPorMupis;
echo '</td>';

echo '<td id="grafico_mapa" width="80%">';
echo '<center><b>Esperando información para generar mapa</b></center>';
echo '</td>';

echo '</tr>';
echo '</table>';
echo '<span id="datos_mupis"><center><b>Seleccione un '._NOMBRE_.'</b></center></span>';
}
?>
