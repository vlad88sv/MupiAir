<?php
if( !file_exists("include/data.php") ) {
	if ( @!chmod("include/", 0777) ) exit ("woops!, 'include/data.php' no existe e include/ no pudo ser Chmodeado");
	if ( @!touch ("include/data.php") ) exit ("woops!, 'include/data.php' no existe y no pudo ser creado");
}
require_once("include/const.php");
error_reporting(E_STRICT | E_ALL);

function CREAR_TBL($TBL,$QUERY) {
global $link;
if ( isset($_POST['reiniciar']) ) { 
@mysql_query("DROP TABLE IF EXISTS $TBL;", $link) or die('!->No se pudo eliminar la tabla "'.$TBL.'".<br /><pre>' . mysql_error() . '</pre>');
}
$QUERY = "CREATE TABLE IF NOT EXISTS ". $TBL . " (" . $QUERY . ");";
$x = @mysql_query($QUERY, $link) or die('!->No se pudo crear la tabla "'. $TBL .'".<br /><pre>' . mysql_error() . '</pre>');
if ($x) {echo "- Creada: '$TBL'<br />";}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es" lang="es">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta http-equiv="Content-Style-type" content="text/css" />
	<meta http-equiv="Content-Script-type" content="text/javascript" />
	<meta http-equiv="Content-Language" content="es" />
	<link rel="StyleSheet" href="estilo.css" type="text/css" />
	<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
	<link rel="start" href="/" />
	<title>Instalador de MUPI</title>
</head>
<body>
<div id="centerwrapper">
<div id="content">

<?
$DB_motor = isset($DB_motor) ? $DB_motor : "localhost";
$DB_usuario = isset($DB_usuario) ? $DB_usuario : "";
$DB_base = isset($DB_base) ? $DB_base : "";
$MapKey = isset($MapKey) ? $MapKey : "";
if (!isset($_POST['instalar'])) {
echo '<h1>'._NOMBRE_.' - Instalador</h1><br />
<form action="'. $_SERVER['PHP_SELF'] .'" method="post">
<table border="0">
<tr><td colspan="2"><h2>Configuración MySQL</h2></td></tr>
<tr>
<td>Dirección del servidor MySQL:</td>
<td><input type="text" name="motor"  maxlength="100" size="20" value="localhost" /></td>
</tr>
<tr>
<td>Base de datos a utilizar:</td>
<td><input type="text" name="base"  maxlength="100" size="20" value="'.$DB_base.'" /></td>
</tr>
<tr>
<td>Usuario:</td>
<td><input type="text" name="usuario"  maxlength="100" size="20" value="'.$DB_usuario.'" /></td>
</tr>
<tr>
<td>Clave:</td>
<td><input type="password" name="clave"  maxlength="30" size="20" value="" /></td>
</tr>
<tr><td colspan="2"><h2>Google Maps</h2></td></tr>
<tr>
<td>Google Map API Key:</td>
<td><input type="text" name="MapKey"  maxlength="100" size="20" value="'.$MapKey.'" /></td>
</tr>
<tr><td colspan="2"><h2>Administración</h2></td></tr>
<tr>
<td>Nombre Administrador:</td>
<td><input type="text" name="admin"  maxlength="100" size="20" value="" /></td>
</tr>
<tr>
<td>Correo electrónico:</td>
<td><input type="text" name="email"  maxlength="100" size="20" value="" /></td>
</tr>
<tr>
<td>Clave:</td>
<td><input type="password" name="admin_clave"  maxlength="20" size="20" value="" /></td>
</tr>
<tr>
<td>Clave (repetir):</td>
<td><input type="password" name="admin_clave2"  maxlength="20" size="20" value="" /></td>
</tr>
</table>
<br />
<input type="checkbox" name="reiniciar"  value="" />Destruir tablas si existen
<br />
<input type="submit" name="instalar" value="Instalar" />
</form>
';
} else {
echo '<b>'._NOMBRE_.' - Instalador : Instalando</b><br />';
if ($_POST['admin_clave'] != $_POST['admin_clave2']) {
echo '<h3>+Las contraseñas no coinciden.</h3><br />
<a href="javascript:history.back();">Regresar al instalador</a>';
}
echo '<h3>+Creando conexión a la base de datos...</h3><br />';
$link = @mysql_connect($_POST['motor'], $_POST['usuario'], $_POST['clave']) or die('Por favor revise sus datos, puesto que se produjo el siguiente error:<br /><pre>' . mysql_error() . '</pre>');
mysql_select_db($_POST['base'], $link) or die('!->La base de datos seleccionada "'.$_POST['base'].'" no existe');
echo '- Base de datos conectada...<br />';
echo '<h3>+Creando Archivo con datos de conexión...</h3><br />';
$fh = @fopen("include/data.php", 'w') or die("No se pudo escribir 'data.php'.<br />");
if ($fh) {
$Datos = "<?php ";
fwrite($fh, $Datos);
$Datos = '$DB_motor = "'. $_POST['motor'] .'";' . '$DB_usuario = "'. $_POST['usuario'] . '";'. '$DB_clave = "'. $_POST['clave'] .'";' . '$DB_base = "'. $_POST['base'] .'";' . '$MapKey = "'. $_POST['MapKey'] .'";';
fwrite($fh, $Datos);
$Datos = " ?>\n";
fwrite($fh, $Datos);
fclose($fh);
}
echo '- Creado<br />';
echo '<h3>+Creando Tablas...</h3><br />';
if ( isset($_POST['reiniciar']) ) { echo '->Se solicitó destruir y crear tablas<br />'; }
/*
Tabla que contiene los datos de los clientes.
Esta tabla solo contendrá los datos relaventes a la identificación y contacto de cliente
*/
$q="codigo VARCHAR(100) primary key, clave VARCHAR(32) not null, nombre VARCHAR(32) not null, razon VARCHAR(100) not null, email VARCHAR(50), telefono1 VARCHAR(20) not null, telefono2 VARCHAR(20), telefono3 VARCHAR(20), logotipo VARCHAR(200), notas VARCHAR(500), userlevel tinyint(1) unsigned not null, userid VARCHAR(32), timestamp int(11) unsigned not null";
CREAR_TBL(TBL_USERS, $q);

/*
Tabla que contiene a los USUARIOS activos
NOTA: En un futuro se va a unir con TBL_ACTIVE_GUESTS
*/
$q="codigo VARCHAR(30) primary key, timestamp int(11) unsigned not null";
CREAR_TBL(TBL_ACTIVE_USERS, $q);

/*
Tabla que contiene a los VISITANTES activos
NOTA: En un futuro se va a unir con TBL_ACTIVE_USERS
*/
$q="ip VARCHAR(15) primary key, timestamp int(11) unsigned not null";
CREAR_TBL(TBL_ACTIVE_GUESTS, $q);

/*
Tabla que contiene la descripción de cada mupi habido y por haber
No contiene información sobre quién lo arrenda ni desde cuando, solo lo ubica en el mapa.
Ademas contiene la direccion (path) a la imagen/foto generica de este MUPI en particular.
NOTA: El campo evento tendrá el código de algún evento ocurrido (si hubiera) a este MUPI.
Si el evento le ocurré al MUPI en general (ej. le choca un automovil) entonces deberá 
asignarle el evento al MUPI.
Si el evento llegará a afectar a una sola de las caras, entonces deberá relacionar dicho
evento con la cara afectada y no con el MUPI. Ver detalles en tabla TBL_MUPI_FACES.
*/
$q="codigo_mupi VARCHAR(100) NOT NULL PRIMARY KEY, direccion VARCHAR(255), foto_generica VARCHAR(255), lon float default NULL, lat float default NULL, codigo_evento VARCHAR(50)";
CREAR_TBL(TBL_MUPI, $q);

/*
Esta tabla es una tabla relacionada con TBL_MUPI en el sentido de que a travez de ella se determinan las caras alquiladas
Ademas de su fecha de alquiler, quien es su "alquilador", que tipo de cara es (peatonal/vehicular) y la foto actual del MUPI.
NOTA: Dado que el daño a un MUPI (llamado "evento") puede producirse en una sola cara (ej. el pintando callejero de una cara)
entonces también los eventos pueden ser asociados a estas (caras).
*/
$q="codigo_cara_mupi VARCHAR(100) NOT NULL PRIMARY KEY, codigo_mupi VARCHAR(100) NOT NULL, codigo VARCHAR(100), alquilado_desde int(11), codigo_evento VARCHAR(50), foto VARCHAR(255)";
CREAR_TBL(TBL_MUPI_FACES, $q);

/*
La tabla de eventos contiene la fecha cuando sucedió el evento y la descripción del evento.
No tiene campo para decir que MUPI afectó porque al contrario es en las tablas TBL_MUPI y 
TBL_MUPI_FACES en las que se relaciona con una entrada de esta tabla.
*/

$q="codigo_evento VARCHAR(50) NOT NULL PRIMARY KEY, descripcion_evento VARCHAR(500)";
CREAR_TBL(TBL_MUPI_EVENTS, $q);

/*
Esta tabla es un cache de GEOCODES. Requerido por PHPGoogleMapApi para optimizar la búsqueda de los mismos GEOCODES.
Ademas puede ser util en un futuro para referenciar los MUPIS por su dirección en lugar de sus coordenadas.
*/
$q="address VARCHAR(255) NOT NULL PRIMARY KEY, lon float default NULL, lat float default NULL";
CREAR_TBL(TBL_GEOCODE_CACHE, $q);

/*
Esta tabla funciona como una versión básica de un registro de pares.
Servirá para llevar las estadísticas de todo el sitio:
-Numero de visitas
*/
$q="clave VARCHAR(255) NOT NULL PRIMARY KEY, valor VARCHAR(255)";
CREAR_TBL(TBL_REGISTRY, $q);

echo '<h3>+Creando usuario '.$_POST['admin'].'...</h3><br />';
$q = "INSERT INTO ".TBL_USERS." VALUES ('".$_POST['admin'] . "', '" . md5($_POST['admin_clave']) . "', 'Administrador Principal', 'Administrador', '" . $_POST['email'] . "', '0','','','','Creado durante de la instalación', 9, 0," . time() . ") ON DUPLICATE KEY UPDATE codigo=VALUES(codigo), clave=VALUES(clave), email=VALUES(email)";
@mysql_query($q, $link)  or die('!->No se pudo insertar el usuario<br /><pre>' . mysql_error() . '</pre>');;
echo '- Creado<br />';
mysql_close($link);
echo '<br /><b>Instalación completa</b><br />';
}
?>
</div>
</div>
</body>
</html>
