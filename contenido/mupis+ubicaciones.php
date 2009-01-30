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
	$("#combo_catorcenas").click(function (){$("#datos_calles").load("contenido/mupis+ubicaciones+dinamico.php?accion=calles&catorcena="+document.getElementsByName(\'combo_catorcenas\')[0].value);});
');
echo '<table>';
echo '<tr>';

echo '<td id="grafico_mapa" width="80%">';
echo 'Esperando información para generar mapa';
echo '</td>';

echo '<td valign="top">';
echo 'Ver Catorcena:<br />' . $database->Combobox_CatorcenasConPresencia("combo_catorcenas",$usuario).'<br /><br />';
echo '<span id="datos_calles">Seleccione una catorcena por favor<br /><br /></span>';
echo '<span id="lista_mupis">Seleccione una calle por favor<br /><br /></span>';
echo '</td>';

echo '</tr>';
echo '</table>';
echo '<span id="datos_mupis">Seleccione un '._NOMBRE_.' por favor</span>';
}
?>
