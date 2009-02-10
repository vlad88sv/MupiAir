<?php
function CONTENIDO_mupis_ubicaciones($usuario=''){
global $session, $database,$map;
if ( !$session->isAdmin() ) { $usuario = $session->codigo; }
//Importante!!! Esto tiene que suceder antes de cualquier cuestión AJAX porque Google esta usando document.write en algún momento!.
echo sprintf('<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=%s" type="text/javascript" charset="utf-8"></script>', GOOGLE_MAP_KEY);
echo "<h1>Ubicaciones de MUPIS contratados</h1><hr />";
// AJAX ;)
echo 
SCRIPT('
    $("#datos_calles").load("contenido/mupis+ubicaciones+dinamico.php?accion=calles&usuario='.$usuario.'&catorcena="+document.getElementsByName(\'combo_catorcenas\')[0].value);
	$("#combo_catorcenas").change(function (){$("#datos_calles").load("contenido/mupis+ubicaciones+dinamico.php?accion=calles&usuario='.$usuario.'&catorcena="+document.getElementsByName(\'combo_catorcenas\')[0].value);});
');
echo '<table>';
echo '<tr>';

echo '<td valign="top" style="border:2px dotted #FFFFFF">';
echo '<b>Ver Catorcena:</b><br />' . $database->Combobox_CatorcenasConPresencia("combo_catorcenas",$usuario).'<br /><br />';
echo '<span id="datos_calles"><b>Seleccione una catorcena</b><br /><br /></span>';
echo '<span id="lista_mupis"><b>Seleccione una calle</b><br /><br /></span>';
echo '</td>';

echo '<td id="grafico_mapa" width="80%" style="border:2px dotted #FFFFFF">';
echo '<center><b>Esperando información para generar mapa</b></center>';
echo '</td>';

echo '</tr>';
echo '</table>';
echo '<span id="datos_mupis"><center><b>Seleccione un '._NOMBRE_.'</b></center></span>';
}
?>
