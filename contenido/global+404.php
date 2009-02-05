<?
function CONTENIDO_global_404() {
echo
'<h1>'._NOMBRE_.' - ¡Error!</h1><br />
¡Lo sentimos pero Ud. ha intentado ingresar a un área de '._NOMBRE_.' que no existe!.<br />
Ud. intentó ingresar a <b>"'.$_SERVER['REQUEST_URI'] .'"</b>.<br />
<br />Sin embargo un reporte de error fue generado y enviado automáticamente a los administradores de '
._NOMBRE_. ' para corroborar el problema.
';
CREAR_LINK("","Continuar","Regresar a la página principal");
}
?>
