<?php
function CONTENIDO_global_estadisticas(){
global $session, $database;
if ( $session->isAdmin() ) {
  echo "<h1>Estadísticas de clientes</h1><hr />";
}
echo "<h1>Estadísticas</h1>";

echo "Catorcena actual: <b>" . date("d/m/Y", Obtener_catorcena_cercana()) . ' a ' . date("d/m/Y", Fin_de_catorcena(Obtener_catorcena_cercana())) . "</b><br />";

$q = "SELECT COUNT(*) as cuenta FROM ". TBL_MUPI_FACES ." WHERE catorcena=".Obtener_catorcena_cercana()." AND codigo_pedido IN (SELECT codigo_pedido from ".TBL_MUPI_ORDERS." WHERE codigo = '".$session->codigo."');";
$result = $database->query($q);
echo "Número de caras publicitarias contratadas en catorcena actual: <b>" . mysql_result($result,0,"cuenta")."</b><br />";

$q = "SELECT SUM(catorcena_fin - catorcena_inicio) as cuenta FROM emupi_mupis_pedidos WHERE codigo='".$session->codigo."';";
$result = $database->query($q);
echo "Número de catorcenas contratadas: <b>" . Contar_catorcenas(mysql_result($result,0,"cuenta"))."</b><br />";

$q = "SELECT SUM((SELECT impactos FROM " . TBL_STREETS . " WHERE codigo_calle = (SELECT codigo_calle FROM ".TBL_MUPI." AS c WHERE c.codigo_mupi=a.codigo_mupi))) AS 'Impactos' FROM ". TBL_MUPI_FACES ." AS a WHERE catorcena=".Obtener_catorcena_cercana()." AND codigo_pedido IN (SELECT codigo_pedido FROM ".TBL_MUPI_ORDERS." WHERE codigo='".$session->codigo."')".";";
$result = $database->query($q);
echo "Número de impactos publicitarios diarios: <b>" . (int) (mysql_result($result,0,"Impactos"))."</b><br />";

/*********************************************************************************************/
// Inicio de parte dinámica.
/*********************************************************************************************/
JS_AJAX_ESTADISTICAS();

echo "<br />".$database->Combobox_CatorcenasConPresencia("catorcenas_presencia",$session->codigo,'document.getElementById(\'datos_catorcena\').firstChild.nodeValue = loadXMLDoc(\'contenido/global+estadisticas+dinamico.php?catorcena=\'+document.getElementsByName(\'catorcenas_presencia\')[0].value)');
echo '<hr><span id="datos_catorcena">Seleccione una catorcena por favor</span>';
return;
}

function JS_AJAX_ESTADISTICAS() {
echo'
<SCRIPT>
var xmlhttp;
function loadXMLDoc(url)
{
xmlhttp=null;
if (window.XMLHttpRequest)
{// code for Firefox, Opera, IE7, etc.
xmlhttp=new XMLHttpRequest();
}
else if (window.ActiveXObject)
{// code for IE6, IE5
xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
}
if (xmlhttp!=null)
{
xmlhttp.onreadystatechange=state_Change;
xmlhttp.open("GET",url,true);
xmlhttp.send(null);
}
else
{
alert("Your browser does not support XMLHTTP.");
}
}

function state_Change()
{
if (xmlhttp.readyState==4)
{// 4 = "loaded"
if (xmlhttp.status==200)
 {// 200 = "OK"
 document.getElementById(\'datos_catorcena\').innerHTML=xmlhttp.responseText;
 }
else
 {
 alert("Problem retrieving data:" + xmlhttp.statusText);
 }
}
}            
</SCRIPT>
';
}
?>
